<?php


class %*APP_NAME*%_ctl_%*CTL_DIR*%%*CTL_NAME*% extends site_controller{

    function %*FUNC_NAME*%(){
		$this->pagedata['app_name'] = "%*APP_NAME*%";
		$this->pagedata['testdata'] = "<h1>hello,控制器%*APP_NAME*%_ctl_%*CTL_DIR*%%*CTL_NAME*%!</h1>";
		$this->page('%*FUNC_NAME*%.html');
    }

}
