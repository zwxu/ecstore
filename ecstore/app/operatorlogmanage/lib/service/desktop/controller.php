<?php

/**
* 该类主要是用来记录后台管理员的操作日志,当对象销毁前执行
*/
class operatorlogmanage_service_desktop_controller
{
	/**
	* 记录管理员日志 
	* @param object $controller 后台控制器对象
	*/
    public function destruct($controller) 
    {
        $this->_logs($controller);
    }

	/**
	* 插入管理员日志的操作
	* @access private 
	* @params object $controller 后台控制器对象
	*/
    private function _logs($controller) 
    {
        $data['app'] = $_GET['app'];
        $data['ctl'] = $_GET['ctl'];
        $data['act'] = $_GET['act'];
        $rows = app::get('operatorlogmanage')->model('register')->getList('operate', $data);

        if($rows[0]['operate']){
            $obj = new desktop_user();
            $data['dateline'] = time();
            $data['operate'] = $rows[0]['operate'];
            $data['username'] = $obj->get_login_name();
            $data['realname'] = $obj->get_name();
            $data['memo'] = $controller->_end_message;
            app::get('operatorlogmanage')->model('logs')->insert($data);
        }

    }//End Function

}//End Class