<?php
$root_dir = realpath(dirname(__FILE__).'/../../../../');
require_once($root_dir."/config/config.php");
define('APP_DIR',ROOT_DIR."/app/");

require_once(APP_DIR.'/base/kernel.php');
if(!kernel::register_autoload()){
    require(APP_DIR.'/base/autoload.php');
}

require_once(APP_DIR.'/base/defined.php');

cachemgr::init(false);