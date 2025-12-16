<?php
require_once __DIR__ . '/env_loader.php';

// SENDGRID CONFIGURATION
define('SENDGRID_API_KEY', getenv('SENDGRID_API_KEY'));
define('SENDGRID_FROM_EMAIL', getenv('SENDGRID_FROM_EMAIL'));
define('SENDGRID_FROM_NAME', 'Car Sweepstakes');
?>