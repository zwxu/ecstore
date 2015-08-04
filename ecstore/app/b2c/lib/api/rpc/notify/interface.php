<?php
 

interface b2c_api_rpc_notify_interface
{
    /**
     * 中心是否需要发送订单同步请求
     * @param array sdf order
     */
    public function rpc_judge_send($sdf_order);
    
    /**
     * 给予需要发送订单的通知
     * @param string order id
     * @param mixed sdf payments
     * @return null
     */
    public function rpc_notify($order_id, $sdf=array());
}