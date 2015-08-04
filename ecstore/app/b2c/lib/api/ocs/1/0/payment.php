<?php



/**
 * b2c payment interactor with center
 */
class b2c_api_ocs_1_0_payment
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
     * 支付单创建
     * @param array sdf
     * @param string message
     * @return boolean success or failure
     */
    public function create(&$sdf, &$thisObj)
    {
        // 创建订单是和中心的交互
        $is_payed = false;
        $objModelPay = $this->app->model('payments');
        $obj_order = $this->app_b2c->model('orders');
        $objMath = kernel::single('ectools_math');

        if (!isset($sdf['order_bn']) || !$sdf['order_bn'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('支付单tid没有收到！'), array());
        }
        else
        {
   $tmp_order = $obj_order->getList('*',array('order_id'=>$sdf['order_bn']));
   if (!$tmp_order)
    $thisObj->send_user_error(app::get('b2c')->_('需要支付的订单号不存在！'), array('tid'=>$sdf['order_bn']));

   $obj_order_bills = $this->app->model('order_bills');
   $sql = 'SELECT * FROM '.$objModelPay->table_name(1) . ' AS payments'
     . ' LEFT JOIN '.$obj_order_bills->table_name(1) . ' AS bill ON bill.bill_id=payments.payment_id'
     . ' WHERE payments.t_begin='.$sdf['t_begin'].' AND bill.bill_type="payments" AND bill.rel_id=\''.$sdf['order_bn'].'\'';

   if (!$obj_order_bills->db->select($sql))
   {
    // 生成支付单据.
    $payment_id = $sdf['payment_id'] = $objModelPay->gen_id();
    $paymentArr = array(
     'payment_id' => $sdf['payment_id'],
     'payment_bn' => $sdf['payment_id'],
     'account' => $sdf['account'],
     'bank' => $sdf['bank'],
     'pay_account' => $sdf['pay_account'] ? $sdf['pay_account'] : app::get('b2c')->_('付款帐号'),
     'currency' => $sdf['currency'],
     'money' => $sdf['money'],
     'paycost' => $sdf['paycost'],
     'cur_money' => $sdf['cur_money'],
     'pay_type' => $sdf['pay_type'],
     'pay_app_id' => $sdf['payment_tid'],
     'pay_name' => $sdf['paymethod'],
     'pay_ver' => '1.0',
     'op_id' => '0',
     'ip' => $sdf['ip'],
     't_begin' => $sdf['t_begin'],
     't_payed' => $sdf['t_begin'],
     't_confirm' => $sdf['t_end'],
     'status' => 'ready',
     'trade_no' => $sdf['trade_no'],
     'memo' => $sdf['memo'],
     'return_url' => '',
     'orders' => array(
      array(
       'rel_id' => $sdf['order_bn'],
       'bill_type' => 'payments',
       'pay_object' => 'order',
       'bill_id' => $sdf['payment_id'],
       'money' => $sdf['money'],
      )
     )
    );

    $is_save = $objModelPay->save($paymentArr);
   }
   else
   {
    $sdf['payment_id'] = $payment_id = $tmp[0]['bill_id'];
   }

   // 更改支付单状态
   $filter = array(
    'payment_id' => $payment_id,
    'status|in'=> array('failed','cancel','error','invalid','timeout','ready'),
   );
   $is_save = $objModelPay->update(array('status'=>'succ'),$filter);
   $affect_row = $objModelPay->db->affect_row();
   if ($is_save)
   {
    // 开始事务
    $db = kernel::database();
    $transaction_status = $db->beginTransaction();
    // 防止重复充值
    if ($affect_row)
    {
     $tmp_payments = $objModelPay->dump($payment_id,'*',true);
     if (!$tmp_payments)
     {
      $thisObj->send_user_error(app::get('b2c')->_('收款单不存在！'), array('tid'=>$sdf['order_bn'],'payment_id'=>$payment_id));
     }
     $paymentArr = $tmp_payments;
     $paymentArr['order_id'] = $sdf['order_bn'];

     // 修改订单状态.
     $obj_pay_finish = kernel::single('b2c_order_pay');
     $is_payed = $obj_pay_finish->order_pay_finish($paymentArr,'succ','font',$msg);

     if ($is_payed)
     {
      $db->commit($transaction_status);
      // 支付扩展事宜 - 如果上面与中心没有发生交互，那么此处会发出和中心交互事宜.
      $obj_pay_finish->order_pay_finish_extends($paymentArr);
      return array('tid'=>$sdf['order_bn'], 'payment_id'=>$sdf['payment_id']);
     }
     else
     {
      $db->rollback();
      $filter = array(
       'payment_id' => $payment_id,
      );
      $objModelPay->update(array('status'=>'failed'),$filter);
      $thisObj->send_user_error($msg, array('tid'=>$sdf['order_bn'],'payment_id'=>$sdf['payment_id']));
     }
    }
    else
    {
     $db->rollback();
     $filter = array(
      'payment_id' => $payment_id,
     );
     $objModelPay->update(array('status'=>'failed'),$filter);
     $thisObj->send_user_error(app::get('b2c')->_('支付多次重复请求！'), array('tid'=>$sdf['order_bn'],'payment_id'=>$sdf['payment_id']));
    }
   }
            else
            {
                $db->rollback();
    $filter = array(
     'payment_id' => $payment_id,
    );
    $objModelPay->update(array('status'=>'failed'),$filter);
    $thisObj->send_user_error(app::get('b2c')->_('支付单生成失败！'), array('tid'=>$sdf['order_bn'],'payment_id'=>$sdf['payment_id']));
            }
        }
    }

    /**
     * 支付单修改
     * @param array sdf
     * @param string message
     * @return boolean sucess of failure
     */
    public function update(&$sdf, &$thisObj)
    {
        // 修改支付单是和中心的交互
        $objPayments = $this->app->model('payments');
        $obj_order = $this->app_b2c->model('orders');

        $arr_payments = $objPayments->dump(array('payment_bn' => $sdf['payment_bn']), '*', '*');

        $arr_refunds['account'] = $sdf['account'] ? $sdf['account'] : '';
        $arr_refunds['bank'] = $sdf['bank'] ? $sdf['bank'] : '';
        $arr_refunds['pay_account'] = $sdf['pay_account'] ? $sdf['pay_account'] : '';
        $arr_refunds['op_id'] = $sdf['op_id'] ? $sdf['op_id'] : 0;
        $arr_refunds['ip'] = $sdf['ip'] ? $sdf['ip'] : '';
        $arr_refunds['t_payed'] = $sdf['t_begin'] ? $sdf['t_begin'] : '';
        $arr_refunds['t_confirm'] = $sdf['t_end'] ? $sdf['t_end'] : '';
        $arr_refunds['status'] = $sdf['status'] ? $sdf['status'] : '';
        $arr_refunds['trade_no'] = $sdf['trade_no'] ? $sdf['trade_no'] : '';
        $arr_refunds['memo'] = $sdf['memo'] ? $sdf['memo'] : '';

        $is_save = $objPayments->save($data);

        if ($arr_refunds['status'] == 'succ')
            if (isset($arr_payments['orders']) && $arr_payments['orders'])
                foreach ($arr_payments['orders'] as $key=>$arr_order)
                {
                    $arr_odrs = $obj_order->dump($key);
                    $order_bn = $key;
                    $arr_odr_data = array(
                        'order_id' => $key,
                        'status' => '1',
                    );

                    $obj_order->save($arr_odr_data);
                }

        if ($is_save)
            return array('tid'=>$order_bn, 'payment_id'=>$sdf['payment_bn']);
        else
        {
            $thisObj->send_user_error(app::get('b2c')->_('支付单修改失败！'), array('tid'=>$sdf['order_bn'],'payment_id'=>$sdf['payment_bn']));
        }
    }
}