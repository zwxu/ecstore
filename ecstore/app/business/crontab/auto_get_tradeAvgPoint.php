<?php
class setpoint {
    public function init(){
        $this->_do_exec();
    }

    private function _do_exec(){
        echo "自动统计行业评分...\n";
        kernel::single('business_mdl_comment_stores_point')->exec_auto();
        echo '行业评分统计完毕...';
    }
}
require_once(dirname(__FILE__) . '/../lib/config/config.php');
set_time_limit(0);
$point = new setpoint();
$point->init();

