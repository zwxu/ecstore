<?php

 
interface b2c_order_extends_interface
{
    /**
     * 订单支付完后对订单的扩展操作
     * @param mixed sdf payment array
     * @param mixed sdf of order change array
     * @return null
     */
    public function order_pay_extends($sdf, &$sdf_order=array());
}