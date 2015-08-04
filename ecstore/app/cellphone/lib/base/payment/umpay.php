<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class cellphone_base_payment_umpay extends cellphone_cellphone
{

    public function __construct( $app )
    {
        parent::__construct() ;
        $this->app = $app ;

    }
    /*
    //获取支付tradeno
    public function dopay()
    {
        $params = $this->params ;

        //检查应用级必填参数
        $must_params = array( 'session' => '会员ID', 'order_id' => '订单编号' ) ;
        $this->check_params( $must_params ) ;

        $sdf['order_id'] = trim( $params['order_id'] ) ;

        $member = $this->get_current_member() ;
        if ( ! $member ) {
            $this->send( false, null, app::get( 'b2c' )->_( '该会员不存在' ) ) ;
        }
        $sdf['member_id'] = $member['member_id'] ;

        $objOrders = &app::get( 'b2c' )->model( 'orders' ) ;
        $objPay = kernel::single( 'ectools_pay' ) ;
        $objMath = kernel::single( 'ectools_math' ) ;
        // 得到商店名称
        $shopName = app::get( 'b2c' )->getConf( 'system.shopname' ) ;

        // Post payment information.
        //$sdf = $_POST['payment'];
        $pay_object = 'order' ;
        $sdf['pay_app_id'] = 'umpay' ;

        //$sdf['money'] = floatval($sdf['money']);

        //ajx 防止恶意修改支付金额，导致支付状态异常
        if ( $pay_object == 'order' ) {
            $orders = $objOrders->dump( $sdf['order_id'] ) ;
            $sdf['cur_amount'] = $objMath->number_minus( array( $orders['cur_amount'], $orders['payed'] ) ) ;
            $orders['total_amount'] = $objMath->number_div( array( $orders['cur_amount'], $orders['cur_rate'] ) ) ;
            $sdf['money'] = floatval( $orders['total_amount'] - $orders['payed'] ) ;
            $sdf['currency'] = $orders['currency'] ;
            $sdf['cur_money'] = $objMath->number_minus( array( $orders['cur_amount'], $orders['payed'] ) ) ;
            $sdf['cur_rate'] = $orders['cur_rate'] ;
        }

        $payment_id = $sdf['payment_id'] = $objPay->get_payment_id() ;

        $sdf['pay_object'] = $pay_object ;
        $sdf['shopName'] = $shopName ;

        $arrOrders = $objOrders->dump( $sdf['order_id'], '*' ) ;

        // 检查是否能够支付
        $obj_checkorder = kernel::service( 'b2c_order_apps', array( 'content_path' =>
                'b2c_order_checkorder' ) ) ;
        $sdf_post = $sdf ;
        $sdf_post['money'] = $sdf['cur_money'] ;
        if ( ! $obj_checkorder->check_order_pay( $sdf['order_id'], $sdf_post, $message ) ) {
            $this->send( false, null, $message ) ;
        }

        if ( ! $sdf['pay_app_id'] )
            $sdf['pay_app_id'] = $arrOrders['payinfo']['pay_app_id'] ;

        $sdf['currency'] = $arrOrders['currency'] ;
        $sdf['total_amount'] = $arrOrders['total_amount'] ;
        $sdf['payed'] = $arrOrders['payed'] ? $arrOrders['payed'] : '0.000' ;
        $sdf['money'] = $objMath->number_div( array( $sdf['cur_money'], $arrOrders['cur_rate'] ) ) ;

        $sdf['payinfo']['cost_payment'] = $arrOrders['payinfo']['cost_payment'] ;

        // 相关联的id.
        $sdf['rel_id'] = $sdf['order_id'] ;

        $sdf['status'] = 'ready' ;
        // 需要加入service给其他实体和虚拟卡
        $obj_prepaid = kernel::service( 'b2c.prepaidcards.add' ) ;

        $is_save_prepaid = false ;
        if ( $obj_prepaid ) {
            $is_save_prepaid = $obj_prepaid->gen_charge_log( $sdf ) ;
        }

        $is_payed = $objPay->generate( $sdf, $this, $msg ) ;

        if ( $is_save_prepaid && $is_payed ) {
            $obj_prepaid->update_charge_log( $sdf ) ;
        }

        if ( $is_payed && $is_payed['ret_code'] == 0 ) {
            unset( $is_payed['ret_code'], $is_payed['mer_date'], $is_payed['mer_id'], $is_payed['ret_msg'],
                $is_payed['sign_type'], $is_payed['token'], $is_payed['version'], $is_payed['sign'] ) ;

            //print_r( '<pre>' ) ;
            //print_r( $is_payed ) ;
            //print_r( '</pre>' ) ;

            $this->send( true, $is_payed, app::get( 'b2c' )->_( '请求支付成功' ) ) ;

        } else {
            $errmsg = $is_payed['ret_msg'] ? app::get( 'b2c' )->_( '请求支付失败:' ) . $is_payed['ret_msg'] :
                app::get( 'b2c' )->_( '请求支付失败' ) ;
            $this->send( false, null, $errmsg ) ;

        }

    }
    8?
    
    /**
     * 合并付款
     * @params string - pay_object ('order','recharge','joinfee')
     * @return null
     */
    public function dopay($pay_object='order')
    {
    
        $params = $this->params ;

        //检查应用级必填参数
        $must_params = array( 'session' => '会员ID', 'order_id' => '订单编号' ) ;
        $this->check_params( $must_params ) ;
        $params['order_id']=trim($params['order_id']);
        
        $pos = strpos($params['order_id'],'[');
        if ($pos === false) {
            $aryorder_id =array($params['order_id']);
        }else{
            $aryorder_id =json_decode($params['order_id']);
        }
        
        $member = $this->get_current_member() ;
        if ( ! $member ) {
            $this->send( false, null, app::get( 'b2c' )->_( '该会员不存在' ) ) ;
        }
        $sdf['member_id'] = $member['member_id'] ;
        
        $pay_object = 'order' ;
        $sdf['pay_app_id'] = 'umpay' ;
       
        $objOrders = &app::get( 'b2c' )->model( 'orders' ) ;
        $objPay = kernel::single( 'ectools_pay' ) ;
        $objMath = kernel::single( 'ectools_math' ) ;
        // 得到商店名称
        $shopName = $this->app->getConf('system.shopname');
        // Post payment information.
            
        $merge_payment_id = $objPay->get_payment_id();
        $money = 0;
    
             
            foreach($aryorder_id as $item){  
                //$sdf = $all_val;
                $sdf['order_id']=$item;
               
                //$sdf['bankaccounttype'] = $_POST['payments']['bankaccounttype'];
                //$sdf['banktype'] = $_POST['payments']['banktype'];
                $sdf['merge_payment_id'] = $merge_payment_id;
                //$sdf['money'] = floatval($sdf['money']);

                //ajx 防止恶意修改支付金额，导致支付状态异常
                $orders = $objOrders->dump($sdf['order_id']);
                $sdf['cur_amount'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                $orders['total_amount'] = $objMath->number_div(array($orders['cur_amount'], $orders['cur_rate']));
                $sdf['money'] = floatval($orders['total_amount'] - $orders['payed']);
                $sdf['currency']=$orders['currency'];
                $sdf['cur_money'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                $sdf['cur_rate'] = $orders['cur_rate'];

                $payment_id = $sdf['payment_id'] = $objPay->get_payment_id();

                if (!$sdf['pay_app_id'])
                 $this->send( false, null, app::get( 'b2c' )->_( '支付方式不能为空！' ) ) ;

                $sdf['pay_object'] = $pay_object;
                $sdf['shopName'] = $shopName;
               
                $arrOrders = $objOrders->dump($sdf['order_id'], '*');

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

                if (!$sdf['pay_app_id'])
                    $sdf['pay_app_id'] = $arrOrders['payinfo']['pay_app_id'];

                $sdf['currency'] = $arrOrders['currency'];
                $sdf['total_amount'] = $arrOrders['total_amount'];
                $sdf['payed'] = $arrOrders['payed'] ? $arrOrders['payed'] : '0.000';
                $sdf['money'] = $objMath->number_div(array($sdf['cur_money'], $arrOrders['cur_rate']));

                $sdf['payinfo']['cost_payment'] = $arrOrders['payinfo']['cost_payment'];

                // 相关联的id.
                $sdf['rel_id'] = $sdf['order_id'];
                
                


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
          

            if ( $is_payed && $is_payed['ret_code'] == 0 ) {
                unset( $is_payed['ret_code'], $is_payed['mer_date'], $is_payed['mer_id'], $is_payed['ret_msg'],
                    $is_payed['sign_type'], $is_payed['token'], $is_payed['version'], $is_payed['sign'] ) ;

                $this->send( true, $is_payed, app::get( 'b2c' )->_( '请求支付成功' ) ) ;

            } else {
                $errmsg = $is_payed['ret_msg'] ? app::get( 'b2c' )->_( '请求支付失败:' ) . $is_payed['ret_msg'] :
                    app::get( 'b2c' )->_( '请求支付失败' ) ;
                $this->send( false, null, $errmsg ) ;

            }
        
    }


   
}
