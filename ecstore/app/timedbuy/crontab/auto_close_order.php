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
	$close_order = new close_order();
	$close_order->execute();
	class close_order{
		function __construct(){
			$this->object = kernel::single('timedbuy_activity_update');
		}

		function execute(){
            echo "自动取消限时抢购订单...\n";
            $this->object->close_order();
            echo "限时抢购订单处理完毕...";
		}
	}