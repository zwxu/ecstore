<?php
class exeviolation {
    public function init(){
        $this->_do_exec();
    }

    private function _do_exec(){
        echo "自动处理店铺违规...\n";
        kernel::single('business_mdl_storeviolation')->exec_violation();
        echo '店铺违规处理完毕...';
    }
}
require_once(dirname(__FILE__) . '/../lib/config/config.php');
set_time_limit(0);
$violation = new exeviolation();
$violation->init();

