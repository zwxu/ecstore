<?php

 

class b2c_order_refund extends b2c_api_rpc_request
{
    /**
     * 私有构造方法，不能直接实例化，只能通过调用getInstance静态方法被构造
     * @params null
     * @return null
     */
    public function __construct($app)
    {  
        parent::__construct($app);
    }
    
    public function order_refund_finish(&$sdf, $status='succ', $from='Back', &$msg='')
    {
        $this->op_id = $sdf['op_id'];
        $this->op_name = $sdf['op_name'];
        // 处理库存
        $obj_order = $this->app->model('orders');
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        $arrFreez = $obj_checkorder->checkOrderFreez('refund', $sdf['order_id']);
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $sdf_order = $obj_order->dump($sdf['order_id'], '*', $subsdf);
        
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;                
        }
        
        // 解除库存量的冻结.
        if ($arrFreez['unfreez'])
        {
            $is_unfreeze = true;                    
            
            $objGoods = &$this->app->model('goods');
            foreach($sdf_order['order_objects'] as $k => $v)
            {
                foreach ($v['order_items'] as $arrItem)
                {
                    if ($arrItem['item_type'] != 'gift')
                    {
                        $arr_params = array(
                            'goods_id' => $arrItem['products']['goods_id'],
                            'product_id' => $arrItem['products']['product_id'],
                            'quantity' => $arrItem['quantity'],
                        );
                        if ($arrItem['item_type'] == 'product')
                            $arrItem['item_type'] = 'goods';
                        $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrItem['item_type']];
                        $is_unfreeze = $str_service_goods_type_obj->unfreezeGoods($arr_params);
                    
                        //$is_unfreeze = $objGoods->unfreez($arrItem['products']['goods_id'], $arrItem['products']['product_id'], $arrItem['quantity']);
                    }
                    else
                    {
                         $arr_params = array(
                            'goods_id' => $arrItem['products']['goods_id'],
                            'product_id' => $arrItem['products']['product_id'],
                            'quantity' => $arrItem['quantity'],
                        );
                        if ($arrItem['item_type'] == 'product')
                            $arrItem['item_type'] = 'goods';
                        $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrItem['item_type']];
                        $is_freeze = $str_service_goods_type_obj->freezeGoods($arr_params);
                    }
                }
            }
        }
        
        // 更新order的信息
        $is_saveOdr = false;
        $objMath = kernel::single('ectools_math');
        
        $order_data['order_id'] = $sdf['order_id'];

        //添加积分支付 
        $point_money_value = app::get('b2c')->getConf('site.point_money_value');
        
        $order_data['payed'] = $objMath->number_minus(array($sdf_order['payed'], $sdf['cur_money']));
        
        if ($order_data['payed'] < 0)
        {
            if($sdf_order['score_u']){
                if($sdf_order['score_u']/$point_money_value < (-$order_data['payed'])){
                    $msg = app::get('b2c')->_('订单退款金额超过支付金额，不能退款！');
                    return false;
                }else{
                    $order_data['payed'] = $objMath->number_minus(array(($sdf_order['payed']+$sdf_order['score_u']/$point_money_value), $sdf['cur_money']));
                    $pay_dead = '1';
                }                
            }else{
                $msg = app::get('b2c')->_('订单退款金额超过支付金额，不能退款！');
                return false;
            }
        }

        if ($order_data['payed'] == '0')
            $order_data['pay_status'] = '5';
        elseif ($order_data['payed'] == $sdf_order['cur_amount'])
			$order_data['pay_status'] = '1';
		else
            $order_data['pay_status'] = '4';        
        
        if($pay_dead == '1'){
            $order_data['payed'] = 0;
        }

        $is_saveOdr = $obj_order->update($order_data,array('order_id'=>$order_data['order_id']));
		if (!$is_saveOdr){
			$msg = app::get('b2c')->_('订单退款状态保存失败！');
			return false;
		}
		if (!$obj_order->db->affect_row()){
			$msg = app::get('b2c')->_('订单重复退款！');
			return false;
		}
		
		if ($is_saveOdr && $sdf_order['member_id'])
        {
            // 预存款和积分处理
            $obj_members_point = $this->app->model('member_point');
			$reasons = $obj_members_point->getHistoryReason();
			$arr_return_score = $obj_members_point->db->select("SELECT * FROM ".$obj_members_point->table_name(1)." WHERE member_id=".$sdf_order['member_id']." AND related_id='".$order_data['order_id']."' AND type='".$reasons['order_refund_use']['type']."' AND reason='".$reasons['order_refund_use']['describe']."'");
			$is_returned_score = 0;
			foreach ((array)$arr_return_score as $arr_is_returned){
				$is_returned_score += abs($arr_is_returned['change_point']);
			}
			
             // 退还订单消费积分
            if ($sdf['return_score'] > ($sdf_order['score_g'] - $sdf_order['score_u'] - $is_returned_score)){
				$msg = app::get('b2c')->_('设置退还的积分过多！');
				return false;
			}				
			
			/** 取到积分有效值 **/
			$is_real_point = $obj_members_point->get_total_count($sdf_order['member_id']);
			if ($is_real_point < intval($sdf['return_score']))
			{
				$sdf['return_score'] = $is_real_point;
			}
            //$obj_members_point->change_point($sdf_order['member_id'], (0 - intval($sdf['return_score'])), $msg, 'order_refund_use', 1, $sdf['order_id'], $this->op_id, 'refund');
            
            $obj_other_joinfee = kernel::servicelist('b2c.other_joinfee.refund_finish');
			$is_frontend = ($from=='Back') ? false : true;
            if ($obj_other_joinfee)
            {
                foreach ($obj_other_joinfee as $obj)
                {
                    if ($obj->get_type() == $sdf['payment'])
                    {
                        $obj->generate_bills($sdf, $sdf_order, 'online', $this->op_id, $this->op_name, $errorMsg,$is_frontend);
                    }
                }
            }
        }
        
        // 更新退款日志结果
        if ($is_saveOdr)
        {
            $objorder_log = $this->app->model('order_log');
            if(isset($sdf['cur_money'])){
                $log_text[] = array(
                    'txt_key'=>'订单退款成功！退款金额'.$sdf['cur_money'].'元！',
                    'data'=>array(
                    ),
                );
            }else{
                $log_text[] = array(
                    'txt_key'=>'订单退款成功！',
                    'data'=>array(
                    ),
                );
            }
			$log_text = serialize($log_text);
			
            $sdf_order_log = array(
                'rel_id' => $sdf['order_id'],
                'op_id' => $this->op_id,
                'op_name' => $this->op_name,
                'alttime' => time(),
                'bill_type' => 'order',
                'behavior' => 'refunds',
                'result' => ($is_saveOdr) ? 'SUCCESS' : 'FAILURE',
                'log_text' => $log_text,
            );
            
            $log_id = $objorder_log->save($sdf_order_log);
        }
        
        $aUpdate['order_id'] = $sdf['order_id'];
        if ($sdf_order['member_id'])
        {
            $member = $this->app->model('members');
            $arr_member = $member->dump($sdf_order['member_id'], '*', array(':account@pam'=>'*'));
        }
        $aUpdate['email'] = (!$sdf_order['member_id']) ? $sdf_order['consignee']['email'] : $arr_member['contact']['email'];
        $aUpdate['pay_status'] = ($order_data['pay_status'] == '5') ? 'REFUND_ALL' : 'REFUND_PART'; 
                                
        $obj_order->fireEvent('refund', $aUpdate, $sdf_order['member_id']);
        
        return $is_saveOdr;
    }
    
    /**
     * 退款单发送矩阵请求
     * @param array 数组值
     * @return null
     */
    public function send_request(&$sdf)
    {
        $obj_members = $this->app->model('members');
        $arrPams = $obj_members->dump($sdf['member_id'], '*', array(':account@pam' => array('*')));
            
        $arr_data = array();
        $arr_data['tid'] = $sdf['order_id'];
        $arr_data['refund_id'] = $sdf['refund_id'];
        $arr_data['buyer_bank'] = $sdf['bank'];
        $arr_data['buyer_account'] = $sdf['account'];
        $arr_data['buyer_name'] = $arrPams['pam_account']['login_name'] ? $arrPams['pam_account']['login_name'] : $sdf['op_name'];
        $arr_data['refund_fee'] = $sdf['money'];
        $arr_data['currency'] = $sdf['currency'];
        $arr_data['currency_fee'] = $sdf['cur_money'];
        $arr_data['pay_type'] = $sdf['pay_type'];
        $arr_data['payment_type'] = $sdf['pay_name'];
		$arr_data['payment_tid'] = $sdf['pay_app_id'];
        $arr_data['seller_account'] = $sdf['pay_account'];
        $arr_data['t_begin'] = date('Y-m-d H:i:s', $sdf['t_begin']);
        $arr_data['t_sent'] = date('Y-m-d H:i:s', $sdf['t_payed']);
        $arr_data['t_received'] = date('Y-m-d H:i:s', $sdf['t_confirm']);
        $arr_data['status'] = $sdf['status'] == 'succ' ? 'SUCC' : 'PROGRESS';
        $arr_data['memo'] = ($sdf['memo'] ? $sdf['memo'] : ""). "#" . $sdf['refund_bn'] . "#";
        $arr_data['outer_no'] = $sdf['trade_no'];
        
        $arr_callback = array(
            'class' => 'b2c_api_callback_app', 
            'method' => 'callback',
            'params' => array(
                'method' => 'store.trade.refund.add',
                'tid' => $arr_data['tid'],
            ),
        );
        
        parent::request('store.trade.refund.add', $arr_data, $arr_callback, 'Order Refund', 1);
    }
}

?>
