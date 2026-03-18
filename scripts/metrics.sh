#!/bin/bash
# /opt/booktracker/metrics.sh
#
# Group1's Book Tracker — CloudWatch Aggregate Metrics
# Author:  Abdullah Hafidz | Group 1 | 17 March 2026
#
# Queries MySQL for book counts and pushes aggregate metrics to CloudWatch.
# Run via cron every 5 minutes on EC2 (authenticated via IAM instance role).
#
# Crontab entry (run as root or ec2-user):
#   */5 * * * * /opt/booktracker/metrics.sh >> /var/log/booktracker-metrics.log 2>&1
#
# Deploy: sudo cp scripts/metrics.sh /opt/booktracker/metrics.sh
#         sudo chmod +x /opt/booktracker/metrics.sh

set -e

REGION="ap-southeast-1"
NAMESPACE="Group1/AppMetrics"

# Load DB credentials from environment (set in /etc/environment)
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_NAME="${DB_NAME:-group1_books}"
DB_USER="${DB_USER:-admin}"
DB_PASS="${DB_PASS:-}"

# Query aggregate stats from RDS
TOTAL=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
  -sNe "SELECT COUNT(*) FROM books;" 2>/dev/null || echo 0)

READ_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
  -sNe "SELECT COUNT(*) FROM books WHERE status='read';" 2>/dev/null || echo 0)

UNREAD_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
  -sNe "SELECT COUNT(*) FROM books WHERE status='unread';" 2>/dev/null || echo 0)

# Push metrics to CloudWatch
aws cloudwatch put-metric-data \
  --namespace "$NAMESPACE" \
  --metric-data \
    "MetricName=TotalBooks,Value=${TOTAL},Unit=Count" \
    "MetricName=BooksRead,Value=${READ_COUNT},Unit=Count" \
    "MetricName=BooksUnread,Value=${UNREAD_COUNT},Unit=Count" \
  --region "$REGION"

echo "$(date): pushed TotalBooks=${TOTAL} Read=${READ_COUNT} Unread=${UNREAD_COUNT}"
