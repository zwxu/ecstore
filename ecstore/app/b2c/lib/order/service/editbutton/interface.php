<?php

 
interface b2c_order_service_editbutton_interface
{
	/**
	 * 修改订单editbuttons的结构
	 * @param array links 引用值
	 * @param array sdf 
	 * @return boolean true
	 */
	public function get_action_links(&$links, $arr_orders);
}