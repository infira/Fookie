<?php
require_once 'definitions.php';
require_once 'PathHandler.php';
require_once BASE_DIR . 'app/facade/Path.class.php';
Path::init();
require_once 'ConfigManager.php';
require_once 'Autoloader.php';
require_once 'request/Payload.php';
require_once 'Fookie.php';
?>