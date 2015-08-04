<?php
class business_ctl_admin_balance extends desktop_controller{
    public function __construct($app)
	{
		parent::__construct($app);
        $this->router = app::get('desktop')->router();
		header("cache-control: no-store, no-cache, must-revalidate");
	}
	
    public function index(){
        $this->finder('ectools_mdl_refunds',array(

            'title'=>app::get('ectools')->_('结算单'),'allow_detail_popup'=>true,
            'base_filter'=>array('refund_type'=>2),
            'actions'=>array(

                        ),
            'use_buildin_export'=>true,
            'use_view_tab'=>true,
            'force_view_tab'=>true,
            ));
    }

    public function _views(){

		$count_all = app::get('ectools')->model('refunds')->count(array('refund_type'=>2));
		$count_balance = app::get('ectools')->model('refunds')->count(array('status'=>'succ','refund_type'=>2));
		$count_no_balance = app::get('ectools')->model('refunds')->count(array('status'=>'ready','refund_type'=>2));

        return array(
                0=>array('label'=>app::get('ectools')->_('全部'),'optional'=>false,'filter'=>'','addon'=>$count_all,'href'=>$this->router->gen_url(array('app'=>'business','ctl'=>'admin_balance','act'=>'index','view'=>0))),
                1=>array('label'=>app::get('ectools')->_('待结算'),'optional'=>false,'filter'=>array('status'=>'ready'),'addon'=>$count_no_balance,'href'=>$this->router->gen_url(array('app'=>'business','ctl'=>'admin_balance','act'=>'index','view'=>1))),
                2=>array('label'=>app::get('ectools')->_('结算成功'),'optional'=>false,'filter'=>array('status'=>'succ'),'addon'=>$count_balance,'href'=>$this->router->gen_url(array('app'=>'business','ctl'=>'admin_balance','act'=>'index','view'=>2))),
            );
    }

    public function balance(){
        $this->begin('index.php?app=business&ctl=admin_balance&act=index');
        $refund = app::get('ectools')->model('refunds');
        $refund_data = $refund->dump($_GET['refund_id'],'*');
        $bill = app::get('ectools')->model('order_bills');
        $rel_order_id = $bill->dump(array('bill_id'=>$_GET['refund_id']),'rel_id');
        if($refund_data['refund_type'] == '2'){
            if($refund_data['pay_app_id'] == 'ysepay'){
                if($refund_data['cur_money'] == 0){
                    $result['0'] = "true";
                }else{
                    foreach( kernel::servicelist('ysepay_tools') as $services ) {
                        if ( is_object($services)) {
                            if ( method_exists($services, 'amount_transfer') ) {
                                $obj_ys = app::get('business')->model('storemanger');

                                $sz_payer = unserialize(app::get('ectools')->getConf('ysepay_payment_plugin_ysepay'));
                                $payer['payerName'] = urlencode($sz_payer['setting']['src_name']);
                                $payer['payerUserCode'] = $sz_payer['setting']['member_id'];
                                $src = $sz_payer['setting']['member_id'];

                                $ys = $obj_ys->getRow('*',array('store_id'=>$refund_data['member_id']));
                                $payee['payeeName'] = urlencode($ys['company_name']);
                                $payee['payeeUserCode'] = $ys['ysusercode'];
                                //转账信息
                                $amount = $refund_data['cur_money'];//转账金额
                                $out_order_id=$rel_order_id['rel_id'];//代付单号，唯一

                                $result = $services->amount_transfer($src,$payer,$payee,$amount,$out_order_id,$rel_order_id['rel_id']);
                            }
                        }
                    }
                }
                if($result['0'] == "true"){
                    $obj_refunds = kernel::single("ectools_refund");
                    $ref_rs = $obj_refunds->generate_after(array('refund_id'=>$_GET['refund_id'],'refund_type'=>'2'));
                    //添加操作员
                    $obj_user = kernel::single('desktop_user');
                    $refund->update(array('op_id'=>$obj_user->user_id),array('refund_id'=>$_GET['refund_id']));
                    if ($ref_rs)
                    {
                        $this->end(true, '结算成功！');

                    }else{
                        $this->end(false, '结算成功，更新结算单状态失败！');
                    }
                }else{
                    $refund->update(array('memo'=>$result['1']),array('refund_id'=>$_GET['refund_id']));
                    $this->end(false, '结算失败！'.$result['1']);
                }
            }else{
                $this->end(false, '结算失败！支付方式错误，请线下交易！');
            }
        }else{
            $this->end(false, '结算单类型错误！');
        }        
    }

    function balance_refund_finish(){
        $this->begin('index.php?app=ectools&ctl=admin_refund&act=index');
        $refund = app::get('ectools')->model('refunds');
        $refund_data = $refund->dump($_GET['refund_id'],'*');
        $obj_bills = app::get('ectools')->model('order_bills');
        $order_id = $obj_bills->getRow('rel_id',array('bill_id'=>$_GET['refund_id']));
        if($refund_data['refund_type'] == '1'){         
            $obj_refunds = kernel::single("ectools_refund");
            $ref_rs = $obj_refunds->generate_after(array('refund_id'=>$_GET['refund_id'],'refund_type'=>'1'));
            //添加操作员
            $obj_user = kernel::single('desktop_user');
            $refund->update(array('op_id'=>$obj_user->user_id),array('refund_id'=>$_GET['refund_id']));
            if ($ref_rs)
            {
                $this->end(true, '退款成功！');
            }else{
                $this->end(false, '退款成功，更新退款单失败！');
            }
        }else{
            $this->end(false, '结算单类型错误！');
        }
    }

    public function balance_refund(){
        $this->begin('index.php?app=ectools&ctl=admin_refund&act=index');
        $refund = app::get('ectools')->model('refunds');
        $refund_data = $refund->dump($_GET['refund_id'],'*');
        $obj_bills = app::get('ectools')->model('order_bills');
        $order_id = $obj_bills->getRow('rel_id',array('bill_id'=>$_GET['refund_id']));
        if($refund_data['refund_type'] == '1'){

            $payment_id = $refund->get_payment($order_id['rel_id']);
            $obj_payment = app::get('ectools')->model('payments');
            $cur_money = $obj_payment->dump($payment_id['bill_id'],'cur_money,merge_payment_id');

            //判断是否是合并付款
            if($cur_money['merge_payment_id'] != ''){
                $payment_id['bill_id'] = $cur_money['merge_payment_id'];
                $cur_money['cur_money'] = 0;
                $total = $obj_payment->getList('*',array('merge_payment_id'=>$payment_id['bill_id'],'status'=>'succ'));
                foreach($total as $key=>$val){
                    $cur_money['cur_money'] = $cur_money['cur_money'] + $val['cur_money'];
                }
            }

            if($refund_data['pay_app_id'] != 'deposit'){
                $obj_refunds = kernel::single("ectools_refund");
                if($refund_data['cur_money'] == 0){
                    $ref_rs = $obj_refunds->generate_after($sdf);
                }else{
                    $refund_data['payment_info'] = $cur_money;
                    $result = $obj_refunds->dorefund($refund_data,$this);
                    $obj_refunds->callback($refund_data,$result);
                }
            
                if($result == "success"){
                    //添加操作员
                    $obj_user = kernel::single('desktop_user');
                    $refund->update(array('op_id'=>$obj_user->user_id),array('refund_id'=>$_GET['refund_id']));
                    if ($ref_rs)
                    {
                        $this->end(true, '退款成功！');

                    }else{
                        $this->end(false, '退款成功，更新退款单失败！');
                    }
                }else{
                    $this->end(false, '退款失败！'.$result);
                }
            }else{
                $this->end(false, '结算失败！支付方式错误，请线下交易！');
            }
        }else{
            $this->end(false, '结算单类型错误！');
        }        
    }
}