<?php
	set_time_limit(0);
	$root_dir = realpath(dirname(__FILE__).'/../../../');
	
	require_once($root_dir."/config/config.php");
	define('APP_DIR',ROOT_DIR."/app/");
	@include_once(APP_DIR.'/base/defined.php');

	require_once(APP_DIR.'/base/kernel.php');
	if(!kernel::register_autoload()){
		require(APP_DIR.'/base/autoload.php');
	}
	cachemgr::init(false);
	$updateActiveStatus = new updateActiveStatus();
	$updateActiveStatus->execute();
	class updateActiveStatus{
		function __construct(){
			$this->object = kernel::single('timedbuy_activity_update');
		}

		function execute(){
            echo "自动更新活动的进行状态...\n";
            $this->object->updateActiveStatus();
            echo "活动的进行状态更新完毕...";
		}
	}