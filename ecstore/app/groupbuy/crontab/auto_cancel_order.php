<?php
class auto {
     public function init(){
        $this->_do_exec();
    }

    private function _do_exec(){
        echo "自动取消团购订单...\n";
        kernel::single('groupbuy_auto_pay')->exec_auto();
        echo "团购订单处理完毕...";
    }
}

require_once(dirname(__FILE__) . '/../lib/config/config.php');
set_time_limit(0);
$auto = new auto();
$auto->init();