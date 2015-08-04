<?php

 

/**
 * b2c delivery interactor with center
 */
class b2c_api_rpc_notify_common implements b2c_api_rpc_notify_interface
{
    /**
     * app object
     */
    public $app;
    
    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function rpc_judge_send($sdf_order)
    {
        if (!isset($sdf_order['order_refer']) || $sdf_order['order_refer'] == 'local')
            return true;
        else
            return false;
    }
    
    public function rpc_notify($order_id, $sdf=array())
    {
        if (!$order_id)
            return;
            
        // 普通一般订单不做处理，只是实现这个接口。
        $obj_order = $this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $objOrder = $obj_order->dump($order_id, '*', $subsdf);
        $obj_order_create = kernel::single("b2c_order_create");
        
        // 首先发送订单。
        $obj_rpc_request_service = kernel::service('b2c.rpc.send.request');
                
        if ($obj_rpc_request_service && method_exists($obj_rpc_request_service, 'rpc_caller_request'))
        {
            if ($obj_rpc_request_service instanceof b2c_api_rpc_request_interface)
                $obj_rpc_request_service->rpc_caller_request($objOrder,'create');
        }
        else
        {
            $obj_order_create->rpc_caller_request($objOrder);
        }
        
        // 判断是否需要其他的请求 - 暂时只有支付后的业务
        $app_ectools = app::get('ectools');
		$objModelPay = $app_ectools->model('payments');
		$obj_order_bills = $app_ectools->model('order_bills');
		$sql = 'SELECT * FROM '.$objModelPay->table_name(1) . ' AS payments'
				. ' LEFT JOIN '.$obj_order_bills->table_name(1) . ' AS bill ON bill.bill_id=payments.payment_id'
				. ' WHERE bill.bill_type="payments" AND bill.rel_id=\''.$obj_order_bills->db->quote($order_id).'\' AND (status=\'succ\' OR status=\'progress\')';
		if ($row = $obj_order_bills->db->select($sql))
		{
			$arr_data = array();
			$arr_data = $row[0];
			$arr_data['order_id'] = $arr_data['rel_id'];
			unset($arr_data['rel_id']);
			
			$obj_order_pay = kernel::single('b2c_order_pay');
			$obj_order_pay->request($arr_data);
		}
    }
}