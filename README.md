# AWS Cloud Infrastructure: Book Tracker

A hands-on cloud computing project built for **CMP6210 Cloud Computing (2025-6)** at Birmingham City University.

**Author:** Abdullah Hafidz

---

## About This Project

This repository documents a hands-on study of AWS cloud infrastructure design and deployment. A simple PHP web application (a personal book tracker) serves as the deployment target, but the app itself is not the focus. The focus is the cloud architecture surrounding it: how services connect, how traffic flows, how the system stays available, and how it is monitored.

The project is structured in two phases, each adding a layer of production-grade infrastructure on top of the last.

---

## What This Covers

| Area | Concepts Explored |
|---|---|
| Networking | VPC design, public and private subnets, routing tables, Internet Gateway |
| Compute | EC2 provisioning, AMI baking, multi-server deployment across availability zones |
| Database | RDS MySQL with Multi-AZ standby for automatic failover |
| Load Balancing | Application Load Balancer, health checks, security group chaining |
| Storage | S3 with all public access blocked, lifecycle and access control |
| CDN | CloudFront with Origin Access Control (OAC) for private S3 asset delivery |
| Identity and Access | IAM roles, least-privilege policies, SSM Parameter Store for secrets |
| Observability | CloudWatch metrics, alarms, dashboard, and CloudWatch Agent (RAM and disk) |
| Audit | CloudTrail API logging to S3 |
| Infrastructure as Code | CloudFormation with a two-stack approach and cross-stack exports |

---

## Architecture

### Phase 1: Core Infrastructure

The VPC has 2 public and 2 private subnets spread across 2 availability zones. A single EC2 instance serves the app. RDS MySQL runs in the private subnets with a Multi-AZ standby for automatic failover.

```
Internet
    |
    v
[ EC2 WebServer ]  <- public subnet AZ1
    |
    v  port 3306
[ RDS MySQL ]      <- private subnets, Multi-AZ (primary AZ1, standby AZ2)
```

### Phase 2: Production-Grade Enhancements

An Application Load Balancer sits in front of two EC2 instances. The second instance is launched directly from an AMI baked from the first, so no manual setup is required. Cover images are stored in S3 and served through CloudFront. CloudWatch monitors all layers. CloudTrail logs every API call.

```
Internet
    |
    v  port 80/443
  [ ALB ]
    |            |
    v            v
[ WebServer1 ]  [ WebServer2 ]   <- public subnets, port 80 from ALB only
    |
    v  port 3306
[ RDS MySQL ]                    <- private subnets, unreachable from internet

[ S3 ]                           <- private, no public access
    |
    v
[ CloudFront ]                   <- OAC signs all requests to S3
```

**Security group chain.** Direct internet access to EC2 on port 80 is blocked because the web security group only accepts traffic sourced from the ALB security group. RDS is doubly protected: it sits in a private subnet with no internet route, and its security group only accepts traffic from the web security group.

---

## AWS Services Used

| Service | Role in This Project |
|---|---|
| VPC | Isolated network with public and private subnet tiers |
| EC2 | Application servers running Apache and PHP |
| RDS MySQL | Managed relational database with Multi-AZ high availability |
| ALB | Layer 7 load balancer distributing traffic across two AZs |
| S3 | Private object storage for cover images |
| CloudFront | CDN delivering S3 assets via signed OAC requests |
| IAM | Instance roles granting least-privilege access to AWS services |
| SSM Parameter Store | Encrypted storage for the database password, never written to disk |
| CloudWatch | Metrics, alarms, and dashboard for CPU, RAM, disk, RDS, and ALB |
| CloudWatch Agent | Collects RAM and disk metrics that EC2 does not expose by default |
| CloudTrail | Audit log of every AWS API call, stored in S3 |
| CloudFormation | Infrastructure as Code across two stacks, D1 and D2 |

---

## Application

The web app is a minimal PHP MVC application. It exists to give the infrastructure something to run.

```
app/
├── controllers/          # BookController.php
├── models/               # Book.php, Setting.php
└── views/                # books/ and layout/ templates
assets/
├── css/style.css
└── js/app.js
config/
└── db.php                # DB connection reads from environment variables
public/
├── index.php             # Front controller
└── health.php            # ALB health check endpoint
scripts/
├── cloudwatch-agent-config.json
└── metrics.sh            # Pushes custom app metrics to CloudWatch
sql/
└── schema.sql
uploads/                  # Local dev only. S3 and CloudFront are used in production.
```

**Stack:** PHP 8.x, MySQL 8.x, Apache with mod_rewrite, plain CSS and vanilla JS. No framework, no build step, no Composer.
