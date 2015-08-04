<?php

 

class b2c_stats_rpc_data
{
    public function __construct($app)
    {
        $this->app = $app;
        $this->listener_keys = $this->app->getConf('system.event_listener_key');
    }
    
    /**
     * 得到rpc监控的信息
     * @param null
     * @return null
     */
    public function get()
    {
        //待续....
        $obj_service_storager = kernel::service("stats_data_storager");
        
        $arr_sessions = array();
        $arr_sessions = $obj_service_storager->get($this->listener_keys);
        
        $str_rpc = '';
        if (isset($arr_sessions) && $arr_sessions)
        {
            foreach ($arr_sessions as $sessioninfo)
                foreach ($sessioninfo as $arr_session)
                    foreach ($arr_session as $key=>$value)
                    {
                        $str_rpc .= '&'.$key."=".urlencode($value);
                    }
        }
        
        return $str_rpc;
    }
}