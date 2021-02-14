<?php
require_once 'functions.php';
require_once 'definitions.php';
require_once APP_DIR . 'facade/Path.php';
Path::init();
require_once APP_DIR . 'facade/AppConfig.php';
AppConfig::init();
require_once 'Autoloader.php';
require_once 'request/Payload.php';
require_once 'Fookie.php';
?>