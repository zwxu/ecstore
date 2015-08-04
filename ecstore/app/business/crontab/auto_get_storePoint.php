<?php
class setpoint {
    public function init(){
        $this->_do_exec();
    }

    private function _do_exec(){
        echo "自动统计店铺动态评分...\n";
        kernel::single('business_mdl_comment_stores_point')->exec_point();
        echo '店铺动态评分统计完毕...';
    }
}
require_once(dirname(__FILE__) . '/../lib/config/config.php');
set_time_limit(0);
$point = new setpoint();
$point->init();

