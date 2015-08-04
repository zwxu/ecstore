<?php
 

class b2c_ctl_site_paycenter extends b2c_frontpage{

    var $noCache = true;

    public function __construct(&$app){
        parent::__construct($app);
        $this->_response->set_header('Cache-Control', 'no-store');
        if(!$this->action) $this->action = 'index';
        $this->action_view = $this->action.".html";
    }

    /**
     * 生成支付单据处理
     * @params string - pay_object ('order','recharge','joinfee')
     * @return null
     */
    public function dopayment($pay_object='order')
    {

        if ($pay_object)
        {
            $arrMember = $this->get_current_member();
            $objOrders = $this->app->model('orders');
            $objPay = kernel::single('ectools_pay');
            $objMath = kernel::single('ectools_math');
            // 得到商店名称
            $shopName = $this->app->getConf('system.shopname');
            // Post payment information.
            $sdf = $_POST['payment'];

       

            $sdf['money'] = floatval($sdf['money']);

            //ajx 防止恶意修改支付金额，导致支付状态异常
            if($pay_object == 'order'){
                $orders = $objOrders->dump($sdf['order_id']);
                
                $sdf['cur_amount'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                $orders['total_amount'] = $objMath->number_div(array($orders['cur_amount'], $orders['cur_rate']));
                $sdf['money'] = floatval($orders['total_amount'] - $orders['payed']);
                $sdf['currency']=$orders['currency'];
                $sdf['cur_money'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                $sdf['cur_rate'] = $orders['cur_rate'];
            }

            $payment_id = $sdf['payment_id'] = $objPay->get_payment_id();

            if ($arrMember)
                $sdf['member_id'] = $arrMember['member_id'];

            if (!$sdf['pay_app_id'])
                $this->splash('failed', 'close', app::get('b2c')->_('支付方式不能为空！'));

            $sdf['pay_object'] = $pay_object;
            $sdf['shopName'] = $shopName;


            switch ($sdf['pay_object'])
            {
                case 'order':
                $arrOrders = $objOrders->dump($sdf['order_id'], '*');

                //线下支付
                if ($sdf['pay_app_id'] == 'offline')
                {
                    if (isset($sdf['member_id']) && $sdf['member_id'])
                        $this->begin(array('app'=>'b2c','ctl'=>'site_member','act'=>'orderdetail', 'arg0'=>$sdf['order_id']));
                    else
                        $this->begin(array('app'=>'b2c','ctl'=>'site_order','act'=>'index', 'arg0'=>$sdf['order_id']));
                }

                //判断是银盛支付时 是否选择了银行
				if($sdf['pay_app_id'] == 'ysepay'&&!$sdf['banktype']){
					$this->begin(array('app'=>'b2c','ctl'=>'site_member','act'=>'orderPayments', 'arg0'=>$sdf['order_id']));
					 $this->end(false,'请选择一家银行');
				}
				

                if ($arrOrders['payinfo']['pay_app_id'] != $sdf['pay_app_id'])
                {
                    $class_name = "";
                    $obj_app_plugins = kernel::servicelist("ectools_payment.ectools_mdl_payment_cfgs");
                    foreach ($obj_app_plugins as $obj_app)
                    {
                        $app_class_name = get_class($obj_app);
                        $arr_class_name = explode('_', $app_class_name);
                        if (isset($arr_class_name[count($arr_class_name)-1]) && $arr_class_name[count($arr_class_name)-1])
                        {
                            if ($arr_class_name[count($arr_class_name)-1] == $sdf['pay_app_id'])
                            {
                                $pay_app_ins = $obj_app;
                                $class_name = $app_class_name;
                            }
                        }
                        else
                        {
                            if ($app_class_name == $sdf['pay_app_id'])
                            {
                                $pay_app_ins = $obj_app;
                                $class_name = $app_class_name;
                            }
                        }
                    }
                    $strPaymnet = app::get('ectools')->getConf($class_name);
                    $arrPayment = unserialize($strPaymnet);

                    $cost_payment = $objMath->number_multiple(array($objMath->number_minus(array($arrOrders['total_amount'], $arrOrders['payinfo']['cost_payment'])), $arrPayment['setting']['pay_fee']));
                    $total_amount = $objMath->number_plus(array($objMath->number_minus(array($arrOrders['total_amount'], $arrOrders['payinfo']['cost_payment'])), $cost_payment));
                    $cur_money = $objMath->number_multiple(array($total_amount, $arrOrders['cur_rate']));

                    // 更新订单支付信息
                    $arr_updates = array(
                        'order_id' => $sdf['order_id'],
                        'payinfo' => array(
                                        'pay_app_id' => $sdf['pay_app_id'],
                                        'cost_payment' => $objMath->number_multiple(array($cost_payment, $arrOrders['cur_rate'])),
                                    ),
                        'total_amount' => $total_amount,
                        'cur_amount' => $cur_money,
                    );

                    $changepayment_services = kernel::servicelist('b2c_order.changepayment');
                    foreach($changepayment_services as $changepayment_service)
                    {
                        $changepayment_service->generate($arr_updates);
                    }

                    $objOrders->save($arr_updates);

                    $arrOrders = $objOrders->dump($sdf['order_id'], '*');
                    /** 需要想中心发送支付方式修改的动作 **/
                    $obj_b2c_pay = kernel::single('b2c_order_pay');
                    $obj_b2c_pay->order_payment_change($arrOrders);
                }

                // 检查是否能够支付
                $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
                $sdf_post = $sdf;
                $sdf_post['money'] = $sdf['cur_money'];
                if (!$obj_checkorder->check_order_pay($sdf['order_id'],$sdf_post,$message))
                {
                        if ($sdf['pay_app_id'] != 'offline'){
                    $this->begin(array('app'=>'b2c','ctl'=>'site_member','act'=>'orderPayments', 'arg0'=>$sdf['order_id']));
                        }
                        $this->end(false, $message);
                }

                if ($sdf['pay_app_id'] == 'offline')
                {
                     $this->end(true,  app::get('b2c')->_('订单已成功提交了'));
                }

                if (!$sdf['pay_app_id'])
                    $sdf['pay_app_id'] = $arrOrders['payinfo']['pay_app_id'];

                $sdf['currency'] = $arrOrders['currency'];
                $sdf['total_amount'] = $arrOrders['total_amount'];
                $sdf['payed'] = $arrOrders['payed'] ? $arrOrders['payed'] : '0.000';
                $sdf['money'] = $objMath->number_div(array($sdf['cur_money'], $arrOrders['cur_rate']));

                $sdf['payinfo']['cost_payment'] = $arrOrders['payinfo']['cost_payment'];

                    // 相关联的id.
                    $sdf['rel_id'] = $sdf['order_id'];
                    break;
                case 'recharge':
                    // 得到充值信息
                    $sdf['rel_id'] = $sdf['member_id'];
                    break;
                case 'joinfee':
                    // 得到加盟费信息
                    break;
                
                case 'earnest':
                    //支付保证金
                    //判断是银盛支付时 是否选择了银行
                    if($sdf['pay_app_id'] == 'ysepay'&&!$sdf['banktype']){
                        $this->begin(array('app'=>'b2c','ctl'=>'site_member','act'=>'orderPayments', 'arg0'=>$sdf['order_id']));
                         $this->end(false,'请选择一家银行');
                    }
                   
                    $sdf['rel_id'] = $sdf['member_id']; 
                    break;
                default:
                    // 其他的卡充值
                    $sdf['rel_id'] = $sdf['rel_id'];
                    break;

            }

            if ($sdf['pay_app_id'] == 'deposit')
                $sdf['return_url'] = "";
            else
                if (!$sdf['return_url'])
                    $sdf['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'result', 'arg0'=>$payment_id));

            $sdf['status'] = 'ready';
            // 需要加入service给其他实体和虚拟卡
            $obj_prepaid = kernel::service('b2c.prepaidcards.add');
             
            $is_save_prepaid = false;
            if ($obj_prepaid)
            {
                $is_save_prepaid = $obj_prepaid->gen_charge_log($sdf);
            }

            
            $is_payed = $objPay->generate($sdf, $this, $msg);

          

            if ($is_save_prepaid && $is_payed)
            {
                $obj_prepaid->update_charge_log($sdf);
            }

          

            if ($sdf['pay_app_id'] == 'deposit')
            {
                // 预存款支付
                if (isset($arrMember['member_id']) && $arrMember['member_id'])
                    $this->begin(array('app'=>'b2c','ctl'=>'site_member','act'=>'orders'));
                else
                    $this->begin(array('app'=>'b2c','ctl'=>'site_order','act'=>'index', 'arg0'=>$sdf['order_id']));

                if ($is_payed)

                    $this->end(true,  app::get('b2c')->_('预存款支付成功！'), array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'result', 'arg0'=>$sdf['payment_id']));
                else
                    $this->end(false, $msg);
            }
        }
    }

    public function result($payment_id)
    {
        $app_ectools = app::get('ectools');
        $oPayment = $app_ectools->model('payments');
        $objOrderItems = $this->app->model('order_items');
        $objGoods = $this->app->model('goods');
        $objOrders = $this->app->model('orders');
        $goods_ids = array();
        $order_ids = array();
        if($payment_id){
            $pay_data = $oPayment->getList('*',array('merge_payment_id'=>$payment_id));
        }
        if($pay_data){
            foreach($pay_data as $key=>$val){
                $subsdf = array('orders'=>array('*'));
                $sdf_payment = $oPayment->dump($val['payment_id'], '*', $subsdf);

                if ($sdf_payment['orders'])
                {
                    // 得到订单日志
                    $objOrderlog = $this->app->model('order_log');
                    foreach ($sdf_payment['orders'] as $order_id=>$arrOrderbills)
                    {
                        $orderlog = $objOrderlog->get_latest_orderlist($arrOrderbills['rel_id'], $arrOrderbills['pay_object'], $arrOrderbills['bill_type']);
                        $arrOrderlogs[$orderlog['log_id']] = $orderlog;
                    }

                    $this->pagedata['payment'] = &$sdf_payment;
                    $this->pagedata['payment']['order_id'] = $order_id;
                    $this->pagedata['orderlog'] = $arrOrderlogs;
                }

                $this->pagedata['pay_succ'] = $app_ectools->getConf('site.paycenter.pay_succ');
                $this->pagedata['pay_failure'] = $app_ectools->getConf('site.paycenter.pay_failure');
                $this->pagedata['send_immediately'] = app::get('b2c')->getConf('site.order.send_type');
                $this->pagedata['base_path'] = kernel::base_url().'/';
                $this->pagedata['payment_id'] = $payment_id;
            }
            $this->page('site/paycenter/result_merge.html');
            
        }else{
            $subsdf = array('orders'=>array('*'));
            $sdf_payment = $oPayment->dump($payment_id, '*', $subsdf); 
            if ($sdf_payment['orders'])
            {
                // 得到订单日志
                $objOrderlog = $this->app->model('order_log');
                foreach ($sdf_payment['orders'] as $order_id=>$arrOrderbills)
                {
                    $orderlog = $objOrderlog->get_latest_orderlist($arrOrderbills['rel_id'], $arrOrderbills['pay_object'], $arrOrderbills['bill_type']);
                    $arrOrderlogs[$orderlog['log_id']] = $orderlog;
                }

                $this->pagedata['payment'] = &$sdf_payment;
                $this->pagedata['payment']['order_id'] = $order_id;
                $this->pagedata['orderlog'] = $arrOrderlogs;

            }

            $this->pagedata['pay_succ'] = $app_ectools->getConf('site.paycenter.pay_succ');
            $this->pagedata['pay_failure'] = $app_ectools->getConf('site.paycenter.pay_failure');
            $this->pagedata['send_immediately'] = app::get('b2c')->getConf('site.order.send_type');
            $this->pagedata['base_path'] = kernel::base_url().'/';
            $this->pagedata['payment_id'] = $payment_id;
            $this->page('site/paycenter/result_'.$sdf_payment['pay_type'].'.html');         
        }

    }

    /**
     * 合并付款
     * @params string - pay_object ('order','recharge','joinfee')
     * @return null
     */
    public function all_dopayment($pay_object='order')
    {
        if ($pay_object)
        {
            $arrMember = $this->get_current_member();
            $objOrders = $this->app->model('orders');
            $objPay = kernel::single('ectools_pay');
            $objMath = kernel::single('ectools_math');
            // 得到商店名称
            $shopName = $this->app->getConf('system.shopname');
            // Post payment information.
            
            $merge_payment_id = $objPay->get_payment_id();
            $money = 0;
            //判断是银盛支付时 是否选择了银行
            if($_POST['payments']['pay_app_id'] == 'ysepay'&&!$_POST['payments']['banktype']){
                foreach($_POST['payment'] as $k=>$v){
                    $orders_id[] = $v['order_id'];
                }
                $orderStr = base64_encode(implode('|',$orders_id));
                $this->begin(array('app'=>'b2c','ctl'=>'site_member','act'=>'all_orderPayments', 'args'=>array(false,'',$orderStr)));
                $this->end(false,'请选择一家银行');
            }
           
            foreach($_POST['payment'] as $all_key => $all_val){  
                $sdf = $all_val;
                $sdf['bankaccounttype'] = $_POST['payments']['bankaccounttype'];
                $sdf['banktype'] = $_POST['payments']['banktype'];
                $sdf['merge_payment_id'] = $merge_payment_id;

                $sdf['money'] = floatval($sdf['money']);

                //ajx 防止恶意修改支付金额，导致支付状态异常
                if($pay_object == 'order'){
                    $orders = $objOrders->dump($sdf['order_id']);
                    
                    $sdf['cur_amount'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                    $orders['total_amount'] = $objMath->number_div(array($orders['cur_amount'], $orders['cur_rate']));
                    $sdf['money'] = floatval($orders['total_amount'] - $orders['payed']);
                    $sdf['currency']=$orders['currency'];
                    $sdf['cur_money'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                    $sdf['cur_rate'] = $orders['cur_rate'];
                }

                $payment_id = $sdf['payment_id'] = $objPay->get_payment_id();

                if ($arrMember)
                    $sdf['member_id'] = $arrMember['member_id'];

                if (!$sdf['pay_app_id'])
                    $this->splash('failed', 'close', app::get('b2c')->_('支付方式不能为空！'));

                $sdf['pay_object'] = $pay_object;
                $sdf['shopName'] = $shopName;


                switch ($sdf['pay_object'])
                {
                    case 'order':
                    $arrOrders = $objOrders->dump($sdf['order_id'], '*');

                    //线下支付
                    if ($sdf['pay_app_id'] == 'offline')
                    {
                        if (isset($sdf['member_id']) && $sdf['member_id'])
                            $this->begin(array('app'=>'b2c','ctl'=>'site_member','act'=>'orderdetail', 'arg0'=>$sdf['order_id']));
                        else
                            $this->begin(array('app'=>'b2c','ctl'=>'site_order','act'=>'index', 'arg0'=>$sdf['order_id']));
                    }

                    if ($arrOrders['payinfo']['pay_app_id'] != $sdf['pay_app_id'])
                    {
                        $class_name = "";
                        $obj_app_plugins = kernel::servicelist("ectools_payment.ectools_mdl_payment_cfgs");
                        foreach ($obj_app_plugins as $obj_app)
                        {
                            $app_class_name = get_class($obj_app);
                            $arr_class_name = explode('_', $app_class_name);
                            if (isset($arr_class_name[count($arr_class_name)-1]) && $arr_class_name[count($arr_class_name)-1])
                            {
                                if ($arr_class_name[count($arr_class_name)-1] == $sdf['pay_app_id'])
                                {
                                    $pay_app_ins = $obj_app;
                                    $class_name = $app_class_name;
                                }
                            }
                            else
                            {
                                if ($app_class_name == $sdf['pay_app_id'])
                                {
                                    $pay_app_ins = $obj_app;
                                    $class_name = $app_class_name;
                                }
                            }
                        }
                        $strPaymnet = app::get('ectools')->getConf($class_name);
                        $arrPayment = unserialize($strPaymnet);

                        $cost_payment = $objMath->number_multiple(array($objMath->number_minus(array($arrOrders['total_amount'], $arrOrders['payinfo']['cost_payment'])), $arrPayment['setting']['pay_fee']));
                        $total_amount = $objMath->number_plus(array($objMath->number_minus(array($arrOrders['total_amount'], $arrOrders['payinfo']['cost_payment'])), $cost_payment));
                        $cur_money = $objMath->number_multiple(array($total_amount, $arrOrders['cur_rate']));

                        // 更新订单支付信息
                        $arr_updates = array(
                            'order_id' => $sdf['order_id'],
                            'payinfo' => array(
                                            'pay_app_id' => $sdf['pay_app_id'],
                                            'cost_payment' => $objMath->number_multiple(array($cost_payment, $arrOrders['cur_rate'])),
                                        ),
                            'total_amount' => $total_amount,
                            'cur_amount' => $cur_money,
                        );

                        $changepayment_services = kernel::servicelist('b2c_order.changepayment');
                        foreach($changepayment_services as $changepayment_service)
                        {
                            $changepayment_service->generate($arr_updates);
                        }

                        $objOrders->save($arr_updates);

                        $arrOrders = $objOrders->dump($sdf['order_id'], '*');
                        /** 需要想中心发送支付方式修改的动作 **/
                        $obj_b2c_pay = kernel::single('b2c_order_pay');
                        $obj_b2c_pay->order_payment_change($arrOrders);
                    }

                    // 检查是否能够支付
                    $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
                    $sdf_post = $sdf;
                    $sdf_post['money'] = $sdf['cur_money'];
                    //判断是否已经支付
                    if($sdf['cur_money'] > 0){
                        if (!$obj_checkorder->check_order_pay($sdf['order_id'],$sdf_post,$message))
                        {
                                if ($sdf['pay_app_id'] != 'offline'){
                            $this->begin(array('app'=>'b2c','ctl'=>'site_member','act'=>'orderPayments', 'arg0'=>$sdf['order_id']));
                                }
                                $this->end(false, $message);
                        }
                    }

                    if ($sdf['pay_app_id'] == 'offline')
                    {
                         $this->end(true,  app::get('b2c')->_('订单已成功提交了'));
                    }

                    if (!$sdf['pay_app_id'])
                        $sdf['pay_app_id'] = $arrOrders['payinfo']['pay_app_id'];

                    $sdf['currency'] = $arrOrders['currency'];
                    $sdf['total_amount'] = $arrOrders['total_amount'];
                    $sdf['payed'] = $arrOrders['payed'] ? $arrOrders['payed'] : '0.000';
                    $sdf['money'] = $objMath->number_div(array($sdf['cur_money'], $arrOrders['cur_rate']));

                    $sdf['payinfo']['cost_payment'] = $arrOrders['payinfo']['cost_payment'];

                        // 相关联的id.
                        $sdf['rel_id'] = $sdf['order_id'];
                        break;
                    case 'recharge':
                        // 得到充值信息
                        $sdf['rel_id'] = $sdf['member_id'];
                        break;
                    case 'joinfee':
                        // 得到加盟费信息
                        break;
                    
                    case 'earnest':
                        //支付保证金
                        $sdf['rel_id'] = $sdf['member_id'];
                        break;
                    default:
                        // 其他的卡充值
                        $sdf['rel_id'] = $sdf['rel_id'];
                        break;

                }

               

                if ($sdf['pay_app_id'] == 'deposit')
                    $sdf['return_url'] = "";
                else
                    if (!$sdf['return_url'])
                        $sdf['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'result', 'arg0'=>$merge_payment_id));

                $sdf['status'] = 'ready';
                // 需要加入service给其他实体和虚拟卡
                $obj_prepaid = kernel::service('b2c.prepaidcards.add');
                 
                $is_save_prepaid = false;
                if ($obj_prepaid)
                {
                    $is_save_prepaid = $obj_prepaid->gen_charge_log($sdf);
                }
                if($sdf['cur_money'] > 0){
                    $is_payed = $objPay->all_generate($sdf, $this, $msg);
                }

                $money = $money + $sdf['cur_money'];

            }
            $sdf['cur_money'] = $money;
            $sdf['money'] = $money;
            $sdf['cur_amount'] = $money;
            $sdf['total_amount'] = $money;
            $sdf['cur_money'] = $money;
            $sdf['merge_payment_id'] = $merge_payment_id;
            $rel_order = array('rel_id'=>$merge_payment_id,'bill_type'=>'payments','pay_object'=>'order','bill_id'=>$sdf['merge_payment_id'],'money'=>$money);
            $sdf['orders']['0'] = $rel_order;
            $is_payed = $objPay->all_dopay($sdf, $this, $msg);
            
            if ($is_save_prepaid && $is_payed)
            {
                $obj_prepaid->update_charge_log($sdf);
            }

          

            if ($sdf['pay_app_id'] == 'deposit')
            {
                // 预存款支付
                if (isset($arrMember['member_id']) && $arrMember['member_id'])
                    $this->begin(array('app'=>'b2c','ctl'=>'site_member','act'=>'orders'));
                else
                    $this->begin(array('app'=>'b2c','ctl'=>'site_order','act'=>'index', 'arg0'=>$sdf['order_id']));

                if ($is_payed)

                    $this->end(true,  app::get('b2c')->_('预存款支付成功！'), array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'result', 'arg0'=>$sdf['merge_payment_id']));
                else
                    $this->end(false, $msg);
            }
        }
    }
}
