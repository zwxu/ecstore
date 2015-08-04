<?php
class auto {
     public function init(){
        $this->_do_exec();
    }

    private function _do_exec(){
        echo "自动处罚未及时发货的商家...\n";
        kernel::single('scorebuy_auto_ship')->exec_auto();
        echo "未及时发货的商家处罚完毕...";
    }
}

require_once(dirname(__FILE__) . '/../lib/config/config.php');
set_time_limit(0);
$auto = new auto();
$auto->init();