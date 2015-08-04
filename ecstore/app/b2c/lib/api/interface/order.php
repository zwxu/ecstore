<?php

 

/**
 * b2c order interactor with center - interface
 */ 
interface b2c_api_interface_order
{
    /**
     * 订单创建
     * @param array sdf
     * @param object rpc service object
     * @return boolean success or failure
     */
    public function create(&$sdf, &$thisObj);
    
    /**
     * 订单修改
     * @param array sdf
     * @param object rpc service object
     * @return boolean sucess of failure
     */
    public function update(&$sdf, &$thisObj);
    
    /**
     * 订单备注
     * @param array sdf
     * @param object rpc service object
     * @return boolean success or failure
     */
    public function remark(&$sdf, &$thisObj);    
    
    /**
     * 订单留言
     * @param array sdf
     * @param object rpc service object
     * @return boolean success or failure
     */
    public function leave_message(&$sdf, &$thisObj);
    
    /**
     * 订单状态修改
     * @param array sdf
     * @param object rpc service object
     * @return boolean success or failure
     */
    public function status_update(&$sdf, &$thisObj);
    
    /**
     * 订单支付状态修改
     * @param array sdf
     * @param object rpc service object
     * @return boolean success or failure
     */
    public function pay_status_update(&$sdf, &$thisObj);
    
    /**
     * 订单发货状态修改
     * @param array sdf
     * @param object rpc service object
     * @return boolean success or failure
     */
    public function ship_status_update(&$sdf, &$thisObj);
}