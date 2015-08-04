<?php
 

interface b2c_api_rpc_request_interface
{
    /**
     * 中心发送接口
     * @param array sdf
     */
    public function rpc_caller_request(&$sdf,$method='pay');
    
    /**
     * 中心重试交互接口
     * @param string method
     * @param array parameters
     * @param array callback array
     * @param string request title
     * @param int shop id
     * @param int time out type
     * @param string rpc id
     * @return null
     */
    public function rpc_recaller_request($method, $params, $callback=array(), $title, $shop_id=NULL, $time_out=1, $rpc_id=null);
}