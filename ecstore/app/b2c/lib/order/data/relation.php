<?php

 

class b2c_order_data_relation extends b2c_api_rpc_request
{
    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        parent::__construct($app);
    }
    
    /**
     * 整理和验证请求的数据
     * @param string method
     * @param array parameters
     * @param array callback array
     * @param string request title
     * @param int shop id
     * @param int time out type
     * @param string rpc id
     * @return null
     */
    public function form_request($method, $params, $callback=array(), $title, $shop_id=NULL, $time_out=1, $rpc_id=null)
    {
        $this->request($method,$params,$callback,$title,$shop_id,$time_out,$rpc_id);
    }
}