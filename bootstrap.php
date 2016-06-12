<?php

$base = getenv('N98_MAGERUN_TEST_MAGENTO_ROOT');

require_once $base . '/app/Mage.php';
require_once 'vendor/n98/magerun/src/bootstrap.php';
require_once 'vendor/autoload.php';


Mage::app();


var_dump(get_include_path());
