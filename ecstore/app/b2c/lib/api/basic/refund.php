<?php



/**
 * ectools refund interactor with center
 */
class b2c_api_basic_refund
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
        $this->app = app::get('ectools');
        $this->app_b2c = $app;

         //店铺校验 
         $data = $_POST ? $_POST: $_GET;
          if($data['method'] &&  trim($data['source_type']) !='system'){
            foreach(kernel::servicelist('business.api_verify_store') as $object)
            {
                 if(is_object($object))
                 {
                     if(method_exists($object,'verifyStore'))
                     {
                        $result = $object->verifyStore(trim($data['store_cert']));
                        if( $result==false){
                            echo app::get('b2c')->_('店铺校验失败！');
                            exit;
                        }
                     }
                 }
            }
          }
    }

    /**
     * 退款单创建
     * @param array sdf
     * @return boolean success or failure
     */
    public function create(&$sdf, $thisObj)
    {
        // 退款单创建是和中心的交互
        $is_payed = false;
        $obj_refund = $this->app->model('refunds');
        $obj_order = $this->app_b2c->model('orders');
        $objMath = kernel::single('ectools_math');

		if (!isset($sdf['order_bn']) || !$sdf['order_bn'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('退款单tid没有收到！'), array());
        }

		$obj_order_bills = $this->app->model('order_bills');
		$sql = 'SELECT * FROM '.$obj_refund->table_name(1) . ' AS refunds'
				. ' LEFT JOIN '.$obj_order_bills->table_name(1) . ' AS bill ON bill.bill_id=refunds.refund_id'
				. ' WHERE refunds.t_begin='.$sdf['t_begin'].' AND bill.bill_type="refunds" AND bill.rel_id=\''.$sdf['order_bn'].'\'';
		if (!$obj_order_bills->db->select($sql))
		{
			$refund_id = $sdf['refund_id'] = $obj_refund->gen_id();

			$refundArr = array(
				'refund_id' => $sdf['refund_id'],
				'refund_bn' => $sdf['refund_bn'],
				'order_id' => $sdf['order_bn'],
				'account' => $sdf['account'],
				'bank' => $sdf['bank'],
				'pay_account' => $sdf['pay_account'] ? $sdf['pay_account'] : app::get('b2c')->_('付款帐号'),
				'currency' => $sdf['currency'],
				'money' => $sdf['money'],
				'paycost' => $sdf['paycost'],
				'cur_money' => $sdf['cur_money'],
				'pay_type' => $sdf['pay_type'],
				'pay_app_id' => $sdf['payment_tid'],
				'pay_name' => $sdf['pay_name'],
				'pay_ver' => '1.0',
				'op_id' => '0',
				'ip' => $sdf['ip'],
				't_begin' => $sdf['t_begin'],
				't_payed' => $sdf['t_begin'],
				't_confirm' => $sdf['t_confirm'],
				'status' => 'ready',
				'trade_no' => $sdf['trade_no'],
				'memo' => $sdf['memo'],
				'return_url' => '',
				'orders' => array(
					array(
						'rel_id' => $sdf['order_bn'],
						'bill_type' => 'refunds',
						'pay_object' => 'order',
						'bill_id' => $sdf['refund_id'],
						'money' => $sdf['money'],
					)
				)
			);

			$is_save = $obj_refund->save($refundArr);
		}
		else
		{
			$sdf['refund_id'] = $refund_id = $tmp[0]['bill_id'];
		}

		// 更改支付单状态
		$filter = array(
			'refund_id' => $refund_id,
			'status|in'=> array('failed','cancel','error','invalid','timeout','ready'),
		);
		$is_save = $obj_refund->update(array('status'=>'succ'),$filter);
		$affect_row = $obj_refund->db->affect_row();
		if ($is_save)
		{
			$db = kernel::database();
			$transaction_status = $db->beginTransaction();
			// 防止重复充值
			if ($affect_row)
			{
				$tmp_refunds = $obj_refund->getList('*',array('refund_id'=>$refund_id));
				if (!$tmp_refunds)
				{
					$thisObj->send_user_error(app::get('b2c')->_('退款单不存在！'), array('tid'=>$sdf['order_bn'],'refund_id'=>$refund_id));
				}
				$refundArr = $tmp_refunds[0];
				$refundArr['order_id'] = $sdf['order_bn'];
				// 修改订单状态.
				$refundArr['op_name'] = 'ome'.app::get('b2c')->_('管理员');
				$refundArr['payment'] = $refundArr['pay_app_id'];
				$obj_refund_finish = kernel::single('b2c_order_refund');
				$is_payed = $obj_refund_finish->order_refund_finish($refundArr, 'succ', 'font', $msg);
			}
			else
			{
				$db->rollback();
				$filter = array(
					'refund_id' => $refund_id,
				);
				$obj_refund->update(array('status'=>'failed'),$filter);
				$thisObj->send_user_error(app::get('b2c')->_('退款多次重复请求！'), array('tid'=>$sdf['order_bn'],'refund_id'=>$sdf['refund_id']));
			}
		}

		if (!$is_save || !$is_payed)
		{
			$db->rollback();
			$msg = $msg ? $msg : app::get('b2c')->_('退款单生成失败！');
			$filter = array(
				'refund_id' => $refund_id,
			);
			$obj_refund->update(array('status'=>'failed'),$filter);
			$thisObj->send_user_error($msg, array('tid'=>$sdf['order_bn'],'refund_id'=>$sdf['refund_id']));
		}
		$db->commit($transaction_status);
		$obj_refund_finish->send_request($refundArr);

		return array('tid'=>$sdf['order_bn'], 'refund_id'=>$sdf['refund_id']);
    }

    /**
     * 退款单修改
     * @param array sdf
     * @return boolean sucess of failure
     */
    public function update(&$sdf, $thisObj)
    {
        // 退款单修改是和中心的交互
        $objRefunds = $this->app->model('refunds');
        $arr_refunds = $this->dump(array('refund_bn' => $sdf['refund_bn']));

        if (isset($arr_refunds) && $arr_refunds)
        {
            $arr_refunds['account'] = $sdf['account'] ? $sdf['account'] : '';
            $arr_refunds['bank'] = $sdf['bank'] ? $sdf['bank'] : '';
            $arr_refunds['pay_account'] = $sdf['pay_account'] ? $sdf['pay_account'] : '';
            $arr_refunds['op_id'] = $sdf['op_id'] ? $sdf['op_id'] : '';
            $arr_refunds['ip'] = $sdf['ip'] ? $sdf['ip'] : '';
            $arr_refunds['t_payed'] = $sdf['t_sent'] ? $sdf['t_sent'] : '';
            $arr_refunds['t_confirm'] = $sdf['t_received'] ? $sdf['t_received'] : '';
            $arr_refunds['status'] = $sdf['status'] ? $sdf['status'] : '';
            $arr_refunds['trade_no'] = $sdf['trade_no'] ? $sdf['trade_no'] : '';
            $arr_refunds['memo'] = $sdf['memo'] ? $sdf['memo'] : '';

            $is_save = $objRefunds->save($arr_refunds);

            if ($arr_refunds['status'] == 'succ')
                if (isset($arr_payments['orders']) && $arr_payments['orders'])
                    foreach ($arr_payments['orders'] as $key=>$arr_order)
                    {
                        $arr_odrs = $obj_order->dump($key);
                        $order_bn = $key;
                        $arr_odr_data = array(
                            'order_id' => $key,
                            'status' => '5',
                        );

                        $obj_order->save($arr_odr_data);
                    }

            if ($is_save)
                return array('tid'=>$order_bn,'refund_id'=>$sdf['refund_bn']);
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('支付单修改失败！'), array('tid'=>$sdf['order_bn'],'refund_id'=>$sdf['refund_bn']));
            }
        }
        else
        {
            $thisObj->send_user_error(app::get('b2c')->_('退款单不存在！'), array('tid'=>$sdf['order_bn'],'refund_id'=>$sdf['refund_bn']));
        }
    }
}