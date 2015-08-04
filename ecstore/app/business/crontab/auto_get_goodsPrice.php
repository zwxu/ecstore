<?php
class setpoint {
    public function init(){
        $this->_do_exec();
    }

    private function _do_exec(){
        echo "自动获取商品降价数据...\n";
        kernel::single('b2c_mdl_member_goods')->changePrice();
        echo '商品降价数据获取完毕...';
    }
}
require_once(dirname(__FILE__) . '/../lib/config/config.php');
set_time_limit(0);
$point = new setpoint();
$point->init();

