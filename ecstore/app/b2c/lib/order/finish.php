<?php

 

class b2c_order_finish extends b2c_api_rpc_request
{
    /**
     * 构造方法，不能直接实例化，只能通过调用getInstance静态方法被构造
     * @params null
     * @return null
     */
    public function __construct($app)
    {          
        parent::__construct($app);
    }
    
    /**
     * 最终的克隆方法，禁止克隆本类实例，克隆是抛出异常。
     * @params null
     * @return null
     */
    final public function __clone()
    {
        trigger_error(app::get('b2c')->_("此类对象不能被克隆！"), E_USER_ERROR);
    }
    
    /**
     * 订单完成
     * @params array - 订单数据
     * @params object - 控制器
     * @params string - 支付单生成的记录
     * @return boolean - 成功与否
     */
    public function generate($sdf, &$controller=null, &$msg='')
    {
        $is_save = true;        
        //todo:ever 什么状态下需要解冻
        $objOrders = $controller->app->model('orders');
        
        $arr_data['status'] = 'finish';
        $arr_data['order_id'] = $sdf['order_id'];
        $arr_data['confirm_time'] = $sdf['confirm_time'];
        $objOrders->save($arr_data);
        $this->request($arr_data);
		$sdf_order = $objOrders->dump($sdf['order_id'], '*');
        
        // 订单积分结算埋点
        $policy_stage = $this->app->getConf("site.get_policy.stage");
        if ($sdf_order['status'] == 'finish' && $policy_stage == '3')
        {
            $stage = '1';
        }
        else
        {  
            $stage = '0';
        }
        
        // 获得积分
        $obj_add_point = kernel::service('b2c_member_point_add');
        if ($stage)
            $obj_add_point->change_point($sdf_order['member_id'], intval($sdf_order['score_g']), $msg, 'order_pay_get', 2, $stage, $sdf['order_id'], $controller->user->user_id, 'finish');
            
        // 扣除积分，使用积分
        $policy_stage = $this->app->getConf("site.consume_point.stage");
        if ($sdf_order['status'] == 'finish' && $policy_stage == '3')
        {
            $stage = '1';
        }
        else
        {  
            $stage = '0';
        }
        
        $obj_reducte_point = kernel::service('b2c_member_point_reducte');
        if ($stage)
            $obj_reducte_point->change_point($sdf_order['member_id'], 0 - intval($sdf_order['score_u']), $msg, 'order_pay_use', 1, $stage, $sdf['order_id'], $controller->user->user_id, 'finish');
        
        // 更新退款日志结果        
        $objorder_log = $this->app->model('order_log');
		
		$log_text[] = array(
				'txt_key'=>'订单完成',
				'data'=>array(
				),
			);
		$log_text = serialize($log_text);
		
        $sdf_order_log = array(
            'rel_id' => $sdf['order_id'],
            'op_id' => $sdf['op_id'],
            'op_name' => $sdf['opname'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'finish',
            'result' => 'SUCCESS',
            'log_text' => $log_text,
        );
        $log_id = $objorder_log->save($sdf_order_log);
        foreach(kernel::servicelist("b2c_order_finish_after") as $k=>$object)
		{
			if(is_object($object))
			{
				if(method_exists($object,'generate'))
					$object->generate($sdf_order);
			}
		}
        return $is_save;
    }
    
    /**
     * 订单创建
     * @param array sdf
     * @return boolean success or failure
     */
    protected function request(&$sdf)
    {
        $arr_data['tid'] = $sdf['order_id'];
        $arr_data['status'] = 'TRADE_FINISHED';
        
        $arr_callback = array(
            'class' => 'b2c_api_callback_app', 
            'method' => 'callback',
            'params' => array(
                'method' => 'store.trade.status.update',
                'tid' => $arr_data['tid'],
            ),
        );
        
        // 回朔待续...
        //$rst = $this->app->matrix()->call('store.trade.status.update', $arr_data);
        parent::request('store.trade.status.update', $arr_data, $arr_callback, 'Order Finish', 1);
        
        return true;
    }
}
