<?php

 

class b2c_stats_listener_pam_login
{
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    /**
     * b2c½־
     * @param array ½Ϣ
     * @return null
     */
    public function listener_login(&$arr_params)
    {
        $obj_member_account = $this->app->model('member_account');
        
        $arr_update = array(
            'member_id' => $arr_params['member_id'],
            'uname' => $arr_params['uname'],
            'is_frontend' => true,
        );
        $obj_member_account->fireEvent('login', $arr_update, $arr_params['member_id']);
    }
}