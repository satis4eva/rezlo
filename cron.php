<?php
// Run every 5 minutes from Hostinger's cron jobs:  php /home/USER/public_html/cron.php
// Retries DBS portal events that couldn't be delivered straight away.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require __DIR__ . '/app/bootstrap.php';
echo json_encode(['portal' => portal_flush(100), 'at' => now()]) . PHP_EOL;
