<?php
class sync {

    public function init(){
        $this->_do_sync();
    }

    private function _do_sync(){
        echo "自动处理订单...\n";
        echo kernel::single('b2c_orderautojob')->order_auto_operation();
        echo "订单处理完毕...";
    }
}
require_once(dirname(__FILE__) . '/../lib/config/config.php');
$sync = new sync();
$sync->init();