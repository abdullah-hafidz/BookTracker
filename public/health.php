<?php
http_response_code(200);
header('Content-Type: application/json');
header('Cache-Control: no-store');
echo '{"status":"ok"}';
