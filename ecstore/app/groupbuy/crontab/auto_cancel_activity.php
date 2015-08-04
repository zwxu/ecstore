<?php
class auto {
     public function init(){
        $this->_do_exec();
    }

    private function _do_exec(){
        echo "自动关闭团购活动...\n";
        kernel::single('groupbuy_auto_activity')->exec_auto();
        echo "团购活动处理完毕...";
    }
}

require_once(dirname(__FILE__) . '/../lib/config/config.php');
set_time_limit(0);
$auto = new auto();
$auto->init();