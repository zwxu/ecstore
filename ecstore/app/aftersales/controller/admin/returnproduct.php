<?php


class aftersales_ctl_admin_returnproduct extends desktop_controller{
    public $workground = 'ectools_ctl_admin_order';
    
    public function __construct($app)
    {
        parent::__construct($app);
        $this->router = app::get('desktop')->router();
        header("cache-control: no-store, no-cache, must-revalidate");
        $this->arr_status = array(
            '1' => app::get('aftersales')->_('退款协议等待卖家确认'),
            '2' => app::get('aftersales')->_('审核中'),
            '3' => app::get('aftersales')->_('接受申请'),
            '4' => app::get('aftersales')->_('完成'),
            '5' => app::get('aftersales')->_('拒绝'),
            '6' => app::get('aftersales')->_('已收货'),
            '7' => app::get('aftersales')->_('已质检'),
            '8' => app::get('aftersales')->_('补差价'),
            '9' => app::get('aftersales')->_('已拒绝退款'),
            '10' => app::get('aftersales')->_('已取消'),
            '11' => app::get('aftersales')->_('卖家不同意协议，等待买家修改'),
            '12' => app::get('aftersales')->_('买家已退货，等待卖家确认收货'),
            '13' => app::get('aftersales')->_('已修改'),
            '14' => app::get('aftersales')->_('卖家收到退货，拒绝退款'),
            '15' => app::get('aftersales')->_('卖家同意退款，等待卖家打款至平台'),
            '16' => app::get('aftersales')->_('卖家已退款，等待系统结算'),
        );
    }
    
    public function index()
    {
        if($_GET['action'] == 'export') $this->_end_message = '导出售后服务申请';
        $this->finder('aftersales_mdl_return_product',array(
            'title'=>app::get('aftersales')->_('售后服务管理'),
            'actions'=>array(
                        ),'use_buildin_set_tag'=>true,'use_buildin_recycle'=>true,'use_buildin_filter'=>true,'use_buildin_export'=>true,
            ));
    }

    public function _views(){

		$count_all = app::get('aftersales')->model('return_product')->count();
        $count_shouqian = app::get('aftersales')->model('return_product')->count(array('is_safeguard'=>1));
        $count_shouhou = app::get('aftersales')->model('return_product')->count(array('is_safeguard'=>2,'status|notin'=>array('4','15','16')));
		$count_daidakuan = app::get('aftersales')->model('return_product')->count(array('is_safeguard'=>2,'is_return_money'=>1,'status'=>'15'));
		$count_daichuli = app::get('aftersales')->model('return_product')->count(array('is_safeguard'=>2,'is_return_money'=>2,'status'=>'16'));
        $count_yichuli = app::get('aftersales')->model('return_product')->count(array('is_safeguard'=>2,'is_return_money'=>2,'status'=>'4'));

        return array(
                0=>array('label'=>app::get('aftersales')->_('全部'),'optional'=>false,'filter'=>array(),'addon'=>$count_all,'href'=>$this->router->gen_url(array('app'=>'aftersales','ctl'=>'admin_returnproduct','act'=>'index','view'=>0))),
                1=>array('label'=>app::get('ectools')->_('售前申请'),'optional'=>false,'filter'=>array('is_safeguard'=>1),'addon'=>$count_shouqian,'href'=>$this->router->gen_url(array('app'=>'aftersales','ctl'=>'admin_returnproduct','act'=>'index','view'=>1))),
                2=>array('label'=>app::get('ectools')->_('售后申请'),'optional'=>false,'filter'=>array('is_safeguard'=>2,'status|notin'=>array('4','15','16')),'addon'=>$count_shouhou,'href'=>$this->router->gen_url(array('app'=>'aftersales','ctl'=>'admin_returnproduct','act'=>'index','view'=>2))),
                3=>array('label'=>app::get('ectools')->_('售后待打款'),'optional'=>false,'filter'=>array('is_safeguard'=>2,'is_return_money'=>1,'status'=>'15'),'addon'=>$count_daidakuan,'href'=>$this->router->gen_url(array('app'=>'aftersales','ctl'=>'admin_returnproduct','act'=>'index','view'=>3))),
                4=>array('label'=>app::get('ectools')->_('售后待结算'),'optional'=>false,'filter'=>array('is_safeguard'=>2,'is_return_money'=>2,'status'=>'16'),'addon'=>$count_daichuli,'href'=>$this->router->gen_url(array('app'=>'aftersales','ctl'=>'admin_returnproduct','act'=>'index','view'=>4))),
                5=>array('label'=>app::get('ectools')->_('售后已完成'),'optional'=>false,'filter'=>array('is_safeguard'=>2,'is_return_money'=>2,'status'=>'4'),'addon'=>$count_yichuli,'href'=>$this->router->gen_url(array('app'=>'aftersales','ctl'=>'admin_returnproduct','act'=>'index','view'=>5))),
        );
    }
    
    public function save()
    {
        $rp = &$this->app->model('return_product');
        $obj_return_policy = kernel::single('aftersales_data_return_policy');

        $return_id = $_POST['return_id'];
        $status = $_POST['status'];
        $sdf = array(
            'return_id' => $return_id,
            'status' => $status,
        );
        $this->pagedata['return_status'] = $obj_return_policy->change_status($sdf);        
        if ($this->pagedata['return_status'])
            $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
        
        $obj_aftersales = kernel::servicelist("api.aftersales.request");
        foreach ($obj_aftersales as $obj_request)
        {
            $obj_request->send_update_request($sdf);
        }
        
        $this->display('admin/return_product/return_status.html');
    }
    
    public function file_download($return_id,$image_file)
    {
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $obj_return_policy->file_download($return_id,$image_file);
    }
    
    public function send_comment()
    {
        $rp = &$this->app->model('return_product');

        $return_id = $_POST['return_id'];
        $comment = $_POST['comment'];
        $arr_data = array(
            'return_id' => $return_id,
            'comment' => $comment,
        );
        
        $this->begin();
        if($rp->send_comment($arr_data))
        {        
            $this->end(true, app::get('aftersales')->_('发送成功！'));
        }
        else
        {
            //trigger_error(__('发送失败'),E_USER_ERROR);            
            $this->end(false, app::get('aftersales')->_('发送失败！'));
        }
    }
    
    public function settings()
    {
        if (!$_POST)
        {
            $this->pagedata['return_product']['is_open'] = $this->app->getConf('site.is_open_return_product');
            $this->pagedata['return_product']['comment'] = $this->app->getConf('site.return_product_comment');
            $this->page('admin/setting/return_product.html');
        }
        else
        {
            $this->begin('index.php?app=aftersales&ctl=admin_returnproduct&act=settings');
            
            if ($_POST['return_is_open'] == 'true')
                $this->app->setConf('site.is_open_return_product', 1);
            else
                $this->app->setConf('site.is_open_return_product', 0);
            
            $this->app->setConf('site.return_product_comment', $_POST['conmment']);
            
            $this->end(true, app::get('aftersales')->_("设置成功！"));
        }
    }

    public function intereven(){
        $this->finder('aftersales_mdl_return_product',array(
            'title'=>app::get('aftersales')->_('待处理退款纠纷'),
            'base_filter'=>array('is_intervene'=>3),
            'actions'=>array(
                        ),'use_buildin_set_tag'=>true,'use_buildin_recycle'=>true,'use_buildin_filter'=>true,'use_buildin_export'=>true,
            ));
    }

    function return_blance(){
        
        $this->begin('index.php?app=aftersales&ctl=admin_returnproduct&act=intereven');

        $rp = app::get('aftersales')->model('return_product');
        $obj_orders = app::get('b2c')->model('orders');
        $return_info = $rp->dump($_POST['return_id']);

        $result = $rp->update(array('status'=>'10','is_intervene'=>'4'),array('return_id'=>$_POST['return_id']));
        $obj_orders->update(array('refund_status'=>'10'),array('order_id'=>$return_info['order_id']));

        if($result){
            //添加退款日志
            if ($this->user->user_id)
            {
                $obj_members = app::get('b2c')->model('members');
                $arrPams = $obj_members->dump($this->user->user_id, '*', array(':account@pam' => array('*')));
            }

            $log_text = "平台打款给卖家";
            $result = "SUCCESS";
            $returnLog = app::get('aftersales')->model("return_log");
            $sdf_return_log = array(
                'order_id' => $return_info['order_id'],
                'return_id' => $_POST['return_id'],
                'op_id' => $this->user->user_id,
                'op_name' => (!$this->user->user_id) ? app::get('b2c')->_('管理员') : $this->user->user_data['name'],
                'alttime' => time(),
                'behavior' => 'intereven_blance',
                'result' => $result,
                'role' => 'admin',
                'log_text' => $log_text,
            );

            $log_id = $returnLog->save($sdf_return_log);

            $objOrderLog = app::get('b2c')->model("order_log");
            $sdf_order_log = array(
                'rel_id' => $return_info['order_id'],
                'op_id' => $this->user->user_id,
                'op_name' => (!$this->user->user_id) ? app::get('b2c')->_('管理员') : $this->user->user_data['name'],
                'alttime' => time(),
                'bill_type' => 'order',
                'behavior' => 'refunds',
                'result' => 'SUCCESS',
                'log_text' => $log_text,
            );
            $log_id = $objOrderLog->save($sdf_order_log);
        }
        
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_finish($return_info['order_id'],'',$message))
        {
            $this->end(false,$message);
        }

        $point_money_value = app::get('b2c')->getConf('site.point_money_value');
        
        $sdf['order_id'] = $return_info['order_id'];
        $sdf['opid'] = $this->user->user_id;
        $sdf['opname'] = $this->user->user_data['account']['login_name'];
        $sdf['confirm_time'] = time();
        
        $b2c_order_finish = kernel::single("b2c_order_finish");

        $system_money_decimals = app::get('b2c')->getConf('system.money.decimals');
        $system_money_operation_carryset = app::get('b2c')->getConf('system.money.operation.carryset');
        $controller = kernel::single('b2c_ctl_site_order');
        if ($b2c_order_finish->generate($sdf, $controller, $message))
        {
            //生成结算单
            $obj_order = app::get('b2c')->model('orders');
            $money = $obj_order->getRow('payed,pmt_order,cost_freight,is_protect,cost_protect,cost_payment,member_id,ship_status,score_u,score_g,discount_value',array('order_id'=>$return_info['order_id']));
            $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));

            $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
            $sdf_order = $obj_order->dump($sdf['order_id'],'*',$subsdf);

            $refunds = app::get('ectools')->model('refunds');
            unset($sdf['inContent']);
            
            $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');
            $sdf['payment'] = ($sdf['payment']) ? $sdf['payment'] : $sdf_order['payinfo']['pay_app_id'];

            $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);

            $time = time();
            $sdf['pay_app_id'] = $sdf['payment'];
            $sdf['member_id'] = $sdf_order['store_id'] ? $sdf_order['store_id'] : 0;
            $sdf['currency'] = $sdf_order['currency'];
            $sdf['paycost'] = 0;
            //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
            $sdf['t_begin'] = $time;
            $sdf['t_payed'] = $time;
            $sdf['t_confirm'] = $time;
            $sdf['pay_object'] = 'order';
            
            $return_product_obj = app::get('aftersales')->model('return_product');
            $returns = $return_product_obj->getList('amount',array('order_id'=>$sdf['order_id'],'refund_type|in'=>array('3','4'),'status'=>'3'));
            if($returns[0]['amount']){
                if($money['is_protect']){
                    $cost_freight = $money['cost_freight']+$money['cost_payment']+$money['cost_protect']-$returns[0]['amount'];
                }else{
                    $cost_freight = $money['cost_freight']+$money['cost_payment']-$returns[0]['amount'];
                }
                if($money['discount_value'] > 0){
                    $total_money = ($money['payed'])+$money['pmt_order']-$cost_freight+$money['discount_value'];
                }else{
                    $total_money = ($money['payed'])+$money['pmt_order']-$cost_freight;
                }
                $obj_items = app::get('b2c')->model('order_items');
                $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));
                //退款金额小于运费
                if($cost_freight > 0){
                    $profit = 0;
                    foreach($items as $k=>$v){
                        $obj_cat = app::get('b2c')->model('goods_cat');
                        $obj_goods = app::get('b2c')->model('goods');
                        $cat_id = $obj_goods->dump($v['goods_id'],'cat_id');
                        if(app::get('b2c')->getConf('member.isprofit') == 'true'){
                            $profit_point = $obj_cat->dump($cat_id['category']['cat_id'],'profit_point');
                            if(is_null($profit_point['profit_point'])){
                                $parent_id = $obj_cat->dump($cat_id['category']['cat_id'],'parent_id');
                                $profit_point = $obj_cat->dump($parent_id['parent_id'],'profit_point');
                            }
                        }else{
                            $profit_point['profit_point'] = 0;
                        }
                        $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                    }
                    $freight_pro = app::get('b2c')->getConf('member.profit');
                    $profit = $profit + $cost_freight*($freight_pro/100);
                }else{
                    $freight_pro = app::get('b2c')->getConf('member.profit');
                    if($money['discount_value'] > 0){
                        $total_money = ($money['payed']+($money['discount_value']))*($freight_pro/100);
                    }else{
                        $total_money = ($money['payed'])*($freight_pro/100);
                    }
                }
                //计算系统价格 
                $math = kernel::single("ectools_math");
                $profit = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);

                if($money['discount_value'] > 0){
                    $sdf['money'] = ($money['payed']+($money['discount_value']))-$profit;
                }else{
                    $sdf['money'] = ($money['payed'])-$profit;
                }
                //end

                if($money['score_g'] > 0){
                    $sdf['money'] = $sdf['money']-($money['score_g'])/$point_money_value;
                    $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                }

                $sdf['return_score'];

                $refunds = app::get('ectools')->model('refunds');
                //$objOrder->op_id = $this->user->user_id;
                //$objOrder->op_name = $this->user->user_data['account']['name'];
                //$sdf['op_id'] = $this->user->user_id;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                
                $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');

                $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                    
                $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                $sdf['cur_money'] = $sdf['money'];
                //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                $sdf['op_id'] = $this->user->user_id;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                $sdf['status'] = 'ready';
                $sdf['app_name'] = $arrPaymentInfo['app_name'];
                $sdf['app_version'] = $arrPaymentInfo['app_version'];
                $sdf['refund_type'] = '2';
                $obj_ys = app::get('business')->model('storemanger');
                $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                $sdf['account'] = $ys['company_name'];
                $sdf['profit'] = $profit;
                if (!$obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
                {
                     $this->end(false,$message);
                }
                $obj_refunds = kernel::single("ectools_refund");
                $rs_seller = $obj_refunds->generate($sdf, $controller, $msg);

                // 增加经验值
                $obj_member = app::get('b2c')->model('members');
                $obj_member->change_exp($money['member_id'], floor($total_money));
            }elseif($money['ship_status'] == '3'){
                //部分退款的确认收货
                $obj_items = app::get('b2c')->model('order_items');
                $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));
                
                $payed = 0;
                foreach($items as $k=>$v){
                    $payed = $payed+$v['price']*$v['sendnum'];
                }
                $payed = $payed - $money['pmt_order'];
                //剩余可打金额
                $return_product_obj = app::get('aftersales')->model('return_product');
                $amount = $return_product_obj->getRow('amount',array('order_id'=>$sdf['order_id'],'status'=>'6'));
                if($money['discount_value'] > 0){
                    $money_useful = ($money['payed'])+($money['discount_value']);
                }else{
                    $money_useful = ($money['payed']);
                }
                //剩余杂费
                $cost_freight = $money_useful - $payed;

                $total_money = $payed+$money['pmt_order'];

                $profit = 0;
                foreach($items as $k=>$v){
                    $obj_cat = app::get('b2c')->model('goods_cat');
                    $obj_goods = app::get('b2c')->model('goods');
                    $cat_id = $obj_goods->dump($v['goods_id'],'cat_id');
                    if(app::get('b2c')->getConf('member.isprofit') == 'true'){
                        $profit_point = $obj_cat->dump($cat_id['category']['cat_id'],'profit_point');
                        if(is_null($profit_point['profit_point'])){
                            $parent_id = $obj_cat->dump($cat_id['category']['cat_id'],'parent_id');
                            $profit_point = $obj_cat->dump($parent_id['parent_id'],'profit_point');
                        }
                    }else{
                        $profit_point['profit_point'] = 0;
                    }
                    $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                }
                $freight_pro = app::get('b2c')->getConf('member.profit');
                $profit = $profit + $cost_freight*($freight_pro/100);

                //计算系统价格 
                $math = kernel::single("ectools_math");
                $profit = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);

                if($money['score_g'] > 0){
                    $sdf['money'] = $money_useful-$profit-($money['score_g'])/$point_money_value;
                    $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                }else{
                    $sdf['money'] = $money_useful-$profit;
                }
                //end

                $sdf['return_score'];

                $refunds = app::get('ectools')->model('refunds');
                //$objOrder->op_id = $this->user->user_id;
                //$objOrder->op_name = $this->user->user_data['account']['name'];
                //$sdf['op_id'] = $this->user->user_id;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                
                $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');

                $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                    
                $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                $sdf['cur_money'] = $sdf['money'];
                //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                $sdf['op_id'] = $this->user->user_id;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                $sdf['status'] = 'ready';
                $sdf['app_name'] = $arrPaymentInfo['app_name'];
                $sdf['app_version'] = $arrPaymentInfo['app_version'];
                $sdf['refund_type'] = '2';

                $obj_ys = app::get('business')->model('storemanger');
                $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                $sdf['account'] = $ys['company_name'];
                $sdf['profit'] = $profit;
                if (!$obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
                {
                     $this->end(false,$message);
                }
                $obj_refunds = kernel::single("ectools_refund");
                $rs_seller = $obj_refunds->generate($sdf, $controller, $msg);

                // 增加经验值
                $obj_member = app::get('b2c')->model('members');
                $obj_member->change_exp($money['member_id'], floor($total_money));
            }else{
                //进行提成计算（正常流程）
                if($money['is_protect']){
                    $cost_freight = $money['cost_freight']+$money['cost_payment']+$money['cost_protect'];
                }else{
                    $cost_freight = $money['cost_freight']+$money['cost_payment'];
                }
                if($money['discount_value'] > 0){
                    $total_money = $money['payed']+$money['pmt_order']-$cost_freight+($money['discount_value']);
                }else{
                    $total_money = $money['payed']+$money['pmt_order']-$cost_freight;
                }
                $obj_items = app::get('b2c')->model('order_items');
                $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));

                $profit = 0;
                foreach($items as $k=>$v){
                    $obj_cat = app::get('b2c')->model('goods_cat');
                    $obj_goods = app::get('b2c')->model('goods');
                    $cat_id = $obj_goods->dump($v['goods_id'],'cat_id');
                    if(app::get('b2c')->getConf('member.isprofit') == 'true'){
                        $profit_point = $obj_cat->dump($cat_id['category']['cat_id'],'profit_point');
                        if(is_null($profit_point['profit_point'])){
                            $parent_id = $obj_cat->dump($cat_id['category']['cat_id'],'parent_id');
                            $profit_point = $obj_cat->dump($parent_id['parent_id'],'profit_point');
                        }
                    }else{
                        $profit_point['profit_point'] = 0;
                    }
                    $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                }
                $freight_pro = app::get('b2c')->getConf('member.profit');
                $profit = $profit + $cost_freight*($freight_pro/100);

                //计算系统价格 
                $math = kernel::single("ectools_math");
                $profit = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);

                if($money['discount_value'] > 0 && $money['score_g'] > 0){
                    $sdf['money'] = $money['payed']+($money['discount_value'])-$profit-($money['score_g'])/$point_money_value;
                    $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                }elseif($money['discount_value'] > 0 && $money['score_g'] == 0){
                    $sdf['money'] = $money['payed']+($money['discount_value'])-$profit;
                }elseif($money['discount_value'] == 0 && $money['score_g'] > 0){
                    $sdf['money'] = $money['payed']-$profit-($money['score_g'])/$point_money_value;
                    $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                }else{
                    $sdf['money'] = $money['payed']-$profit; 
                }
                //end

                $sdf['return_score'];

                $refunds = app::get('ectools')->model('refunds');
                //$objOrder->op_id = $this->user->user_id;
                //$objOrder->op_name = $this->user->user_data['account']['name'];
                //$sdf['op_id'] = $this->user->user_id;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                
                $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');

                $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                    
                $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                $sdf['cur_money'] = $sdf['money'];
                //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                $sdf['op_id'] = $this->user->user_id;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                $sdf['status'] = 'ready';
                $sdf['app_name'] = $arrPaymentInfo['app_name'];
                $sdf['app_version'] = $arrPaymentInfo['app_version'];
                $sdf['refund_type'] = '2';
                $obj_ys = app::get('business')->model('storemanger');
                $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                $sdf['account'] = $ys['company_name'];
                $sdf['profit'] = $profit;
                if (!$obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
                {
                     $this->end(false,$message);
                }
                $obj_refunds = kernel::single("ectools_refund");
                $rs_seller = $obj_refunds->generate($sdf, $controller, $msg);

                // 增加经验值
                $obj_member = app::get('b2c')->model('members');
                $obj_member->change_exp($money['member_id'], floor($total_money));
            }
            
            //将款项打给卖家
            if($rs_seller){
                $refund = app::get('ectools')->model('refunds');
                $refund_data = $refund->dump($refund_id,'*');
                $bill = app::get('ectools')->model('order_bills');
                $rel_order_id = $bill->dump(array('bill_id'=>$refund_id),'rel_id');
                if($refund_data['refund_type'] == '2' && $refund_data['status'] == 'ready'){
                    if($refund_data['pay_app_id'] == 'ysepay'){
                        if($refund_data['cur_money'] == 0){
                            $result['0'] = "true";
                        }else{
                            foreach( kernel::servicelist('ysepay_tools') as $services ) {
                                if ( is_object($services)) {
                                    if ( method_exists($services, 'amount_transfer') ) {

                                        $sz_payer = unserialize(app::get('ectools')->getConf('ysepay_payment_plugin_ysepay'));
                                        $payer['payerName'] = urlencode($sz_payer['setting']['src_name']);
                                        $payer['payerUserCode'] = $sz_payer['setting']['member_id'];
                                        $src = $sz_payer['setting']['member_id'];

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
                            $ref_rs = $obj_refunds->generate_after(array('refund_id'=>$refund_id,'refund_type'=>'2'));
               
                            if ($ref_rs)
                            {
                                $this->end(true, '操作成功！');

                            }else{
                                $this->end(true, '操作成功！');
                            }
                        }else{
                            $refund->update(array('memo'=>$result['1']),array('refund_id'=>$refund_id));
                            $this->end(true, '操作成功！'.$result['1']);
                        }
                    }else{
                        $obj_refunds = kernel::single("ectools_refund");
                        $ref_rs = $obj_refunds->generate_after(array('refund_id'=>$refund_id,'refund_type'=>'2'));
           
                        if ($ref_rs)
                        {
                            $this->end(true, '操作成功！');

                        }else{
                            $this->end(false, '操作成功！更新结算单状态失败！');
                        }
                    }
                }else{
                    $this->end(false, '结算单类型错误！');
                }
            }
            
        }
        else
        {
            $this->end(false, app::get('aftersales')->_('操作失败！'));
        }
    }

    function return_refund(){
        $this->begin('index.php?app=aftersales&ctl=admin_returnproduct&act=intereven');
        $controller = kernel::single('aftersales_ctl_site_member',array('app'=>app::get('aftersales'),'arg1'=>false));
        $rp = app::get('aftersales')->model('return_product');
        $return_info = $rp->dump($_POST['return_id']);
        $order_obj = app::get('b2c')->model('orders');

        //处理申请单
        $order_id = $rp->getRow('*',array('return_id'=>$_POST['return_id']));
        if(isset($_POST['amount']) || $_POST['amount'] > 0){
            if($_POST['amount'] > $order_id['amount']){
                $this->end(false,'输入金额大于买家申请，请修改！');
            }
        }else{
            $this->end(false,'请输入正确的金额！');
        }
        if($order_id['shop_cost'] || $order_id['amount_seller']){
            $total = $order_id['shop_cost']+$order_id['amount_seller']+$order_id['amount'];
            $status['amount'] = $_POST['amount'];
            $status['shop_cost'] = $order_id['shop_cost'];
            $amount_seller = $total - $status['shop_cost'] - $status['amount'];
            $status['amount_seller'] = $amount_seller;
        }else{
            $status['amount'] = $_POST['amount'];
        }

        $status['status'] = '4';
        $status['is_intervene'] = '4';
        $result = $rp->update($status,array('return_id'=>$_POST['return_id']));
        $order_obj->update(array('refund_status'=>'10'),array('order_id'=>$return_info['order_id']));

        if($result){
            //添加退款日志
            if ($this->user->user_id)
            {
                $obj_members = app::get('b2c')->model('members');
                $arrPams = $obj_members->dump($this->user->user_id, '*', array(':account@pam' => array('*')));
            }

            $log_text = "平台退款给买家";
            $result = "SUCCESS";
            $returnLog = app::get('aftersales')->model("return_log");
            $sdf_return_log = array(
                'order_id' => $return_info['order_id'],
                'return_id' => $_POST['return_id'],
                'op_id' => $this->user->user_id,
                'op_name' => (!$this->user->user_id) ? app::get('b2c')->_('管理员') : $this->user->user_data['name'],
                'alttime' => time(),
                'behavior' => 'intereven_refund',
                'result' => $result,
                'role' => 'admin',
                'log_text' => $log_text,
            );

            $log_id = $returnLog->save($sdf_return_log);

            $objOrderLog = app::get('b2c')->model("order_log");

            $sdf_order_log = array(
                'rel_id' => $return_info['order_id'],
                'op_id' => $this->user->user_id,
                'op_name' => (!$this->user->user_id) ? app::get('b2c')->_('管理员') : $this->user->user_data['name'],
                'alttime' => time(),
                'bill_type' => 'order',
                'behavior' => 'refunds',
                'result' => 'SUCCESS',
                'log_text' => $log_text,
            );
            $log_id = $objOrderLog->save($sdf_order_log);
        }

        //添加赔付金
        if($return_info['comment'] == '虚假发货'){
            
            $store_obj = app::get('business')->model('storemanger');
            $info = $order_obj->dump($return_info['order_id'],'store_id,total_amount,cost_freight');
                
            $company_earnest = $store_obj->dump($info['store_id'],'earnest');
            
            $earnest_value = ($info['total_amount'] - $info['shipping']['cost_shipping'])*0.3;
            if($earnest_value > 500){
                $earnest_value = 500;
            }

            $data['earnest'] = $company_earnest['earnest']-$earnest_value;

            $obj_log = app::get('business')->model('earnest_log');
            $log_data['store_id'] = $info['store_id'];
            $log_data['earnest_value'] = (0-$earnest_value);
            $log_data['last_modify'] = time();
            $log_data['addtime'] = time();
            $log_data['expiretime'] = time();
            $log_data['source'] = '1';
            $log_data['operator'] = $this->user->user_id;
            $log_data['remark'] = '虚假发货赔付金';
            $log_data['orders'] = $return_info['order_id'];
            if ($obj_log->insert($log_data)){
                $store_obj->update($data, array('store_id'=>$info['store_id']));
            }else{
                $msg = app::get('b2c')->_("修改失败");
            }

        }

        $returns = $rp->getRow('*',array('return_id'=>$_POST['return_id']));
        $obj_return_policy = kernel::single('aftersales_data_return_policy');

        $re_sdf = array(
            'return_id' => $_POST['return_id'],
            'status' => '3',
        );

        //生成退款单
   
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));

        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $sdf_order = $order_obj->dump($returns['order_id'],'*',$subsdf);

        $sdf['money'] = $returns['amount'];
        //$sdf['return_score'] = $sdf_order['score_g']-$sdf_order['score_u'];

        $refunds = app::get('ectools')->model('refunds');
        $sdf['op_id'] = $this->user->user_id;
        unset($sdf['inContent']);
        
        $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');
        $sdf['payment'] = ($sdf['payment']) ? $sdf['payment'] : $sdf_order['payinfo']['pay_app_id'];

        $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
           
        $time = time();
        $sdf['refund_id'] = $refund_id = $refunds->gen_id();
        $sdf['pay_app_id'] = $sdf['payment'];
        $sdf['member_id'] = $sdf_order['member_id'] ? $sdf_order['member_id'] : 0;

        $obj_members = app::get('pam')->model('account');
        $buy_name = $obj_members->getRow('login_name',array('account_id'=>$sdf['member_id']));
        $sdf['account'] = $buy_name['login_name'];

        $sdf['currency'] = $sdf_order['currency'];
        $sdf['paycost'] = 0;
        $sdf['cur_money'] = $sdf['money'];
        //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
        $sdf['t_begin'] = $time;
        $sdf['t_payed'] = $time;
        $sdf['t_confirm'] = $time;
        $sdf['pay_object'] = 'order';
        $sdf['op_id'] = $this->user->user_id;
        $sdf['status'] = 'ready';
        $sdf['app_name'] = $arrPaymentInfo['app_name'];
        $sdf['app_version'] = $arrPaymentInfo['app_version'];
        $sdf['refund_type'] = '1';
        $sdf['order_id'] = $returns['order_id'];
        if (!$obj_checkorder->check_order_refund($returns['order_id'],$sdf,$message))
        {
             $this->end(false, $message);
        }
        $obj_refunds = kernel::single("ectools_refund");
        $rs_buyer = $obj_refunds->generate($sdf, $controller, $msg);
        //开始确认收货时间
        $confirm_time = $order_obj->getRow('confirm_time,status,score_u,member_id',array('order_id'=>$returns['order_id']));
        $time = $confirm_time['confirm_time'] + time();
        
        $refund_data = $refunds->dump($refund_id,'*');

        $score_u = $confirm_time['score_u']-$returns['return_score'];

        //修改订单状态
        $refund_status = array('refund_status'=>'4','confirm_time'=>$time,'score_u'=>$score_u);
        $rs = $order_obj->update($refund_status,array('order_id'=>$returns['order_id']));

        //退还积分
        $obj_members_point = kernel::service('b2c_member_point_add');
        $obj_members_point->change_point($confirm_time['member_id'],intval($returns['return_score']), $msg, 'order_refund_use', 1, $returns['order_id'],0, 'refund');
        
        $obj_bills = app::get('ectools')->model('order_bills');
        $order_id = $obj_bills->getRow('rel_id',array('bill_id'=>$refund_id));
        $payment_id = $refunds->get_payment($order_id['rel_id']);
        $obj_payment = app::get('ectools')->model('payments');
        $cur_money = $obj_payment->dump($payment_id['bill_id'],'*');

        //判断是否是合并付款
        if($cur_money['merge_payment_id'] != ''){
            $payment_id['bill_id'] = $cur_money['merge_payment_id'];
            $cur_money['cur_money'] = 0;
            $total = $obj_payment->getList('*',array('merge_payment_id'=>$payment_id['bill_id'],'status'=>'succ'));
            foreach($total as $key=>$val){
                $cur_money['cur_money'] = $cur_money['cur_money'] + $val['cur_money'];
            }
        }

        if($confirm_time['status'] == 'active'){
            if($refund_data['pay_app_id'] != 'deposit'){
                if($refund_data['cur_money'] == 0){
                    $obj_refunds = kernel::single("ectools_refund");
                    $ref_rs = $obj_refunds->generate_after($sdf);
                }else{
                    $refund_data['payment_info'] = $cur_money;
                    $result = $obj_refunds->dorefund($refund_data,$this);
                    $obj_refunds->callback($refund_data,$result);
                }

                //生成运费结算单
                if($returns['ship_cost'] > 0 || $returns['amount_seller']>0){
                    $freight_pro = app::get('b2c')->getConf('member.profit');
                    $sdf['money'] = ($returns['ship_cost']+$returns['amount_seller'])*(1-$freight_pro/100);
                    $sdf['profit'] = ($returns['ship_cost']+$returns['amount_seller'])*($freight_pro/100);
                    unset($sdf['return_score']);

                    $refunds = app::get('ectools')->model('refunds');
                    //$objOrder->op_id = $this->user->user_id;
                    //$objOrder->op_name = $this->user->user_data['account']['name'];
                    //$sdf['op_id'] = $this->user->user_id;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    
                    $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');

                    $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                        
                    $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                    $sdf['member_id'] = $sdf_order['store_id'] ? $sdf_order['store_id'] : 0;
                    $sdf['cur_money'] = $sdf['money'];
                    //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                    $sdf['op_id'] = $this->user->user_id;
                    $sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    $sdf['status'] = 'ready';
                    $sdf['app_name'] = $arrPaymentInfo['app_name'];
                    $sdf['app_version'] = $arrPaymentInfo['app_version'];
                    $sdf['refund_type'] = '2';
                    $sdf['order_id'] = $returns['order_id'];
                    $obj_ys = app::get('business')->model('storemanger');
                    $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                    $sdf['account'] = $ys['company_name'];
                    $rs_seller = $obj_refunds->generate($sdf, $controller, $msg);
                    //需要结算结算单

                    $obj_order->update(array('status'=>'finish'),array('order_id'=>$sdf['order_id']));
                    
                    $refund_data = $refunds->getRow('*',array('return_id'=>$sdf['refund_id']));
                    $bill = app::get('ectools')->model('order_bills');
                    $rel_order_id = $bill->dump(array('bill_id'=>$sdf['refund_id']),'rel_id');
                    if($refund_data['refund_type'] == '2' && $refund_data['status'] == 'ready'){
                        if($refund_data['pay_app_id'] == 'ysepay'){
                            if($refund_data['cur_money'] == 0){
                                $result['0'] = "true";
                            }else{
                                foreach( kernel::servicelist('ysepay_tools') as $services ) {
                                    if ( is_object($services)) {
                                        if ( method_exists($services, 'amount_transfer') ) {

                                            $sz_payer = unserialize(app::get('ectools')->getConf('ysepay_payment_plugin_ysepay'));
                                            $payer['payerName'] = urlencode($sz_payer['setting']['src_name']);
                                            $payer['payerUserCode'] = $sz_payer['setting']['member_id'];
                                            $src = $sz_payer['setting']['member_id'];

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
                         }
                     }
                }
     
                if($result['0'] == "true"){
                    
                    $obj_refunds = kernel::single("ectools_refund");
                    $ref_rs = $obj_refunds->generate_after($sdf);
                    
                    if ($ref_rs)
                    {

                        $obj_refund_lists = kernel::servicelist("order.refund_finish");
                        foreach ($obj_refund_lists as $order_refund_service_object)
                        {     
                            $sdf['order_id'] = $return_info['order_id'];
                            $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                        }
                        // 发送同步日志.
                        $order_refund_service_object->send_request($sdf);

                        //ajx crm
                        $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                        $req_arr['order_id']=$sdf['order_id'];
                        $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

                        $this->pagedata['return_status'] = $obj_return_policy->change_status($re_sdf);        
                        if ($this->pagedata['return_status'])
                            $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
                        
                        $obj_aftersales = kernel::servicelist("api.aftersales.request");
                        foreach ($obj_aftersales as $obj_request)
                        {
                            $obj_request->send_update_request($sdf);
                        }

                        //判断如果已经全部退款  则给积分（没有退还商品的情况）
                        $order_data = $order_obj->getRow('pay_status',array('order_id'=>$sdf['order_id']));
                        if($order_data['pay_status'] == '5'){
                            $order_obj->update(array('status'=>'finish'),array('order_id'=>$sdf['order_id']));
                        }

                        $this->end(true,'操作成功');

                    }else{

                        $this->pagedata['return_status'] = $obj_return_policy->change_status($re_sdf);        
                        if ($this->pagedata['return_status'])
                            $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];

                        $obj_refund_lists = kernel::servicelist("order.refund_finish");
                        foreach ($obj_refund_lists as $order_refund_service_object)
                        {                
                            $sdf['order_id'] = $return_info['order_id'];
                            $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                        }
                        
                        $obj_aftersales = kernel::servicelist("api.aftersales.request");
                        foreach ($obj_aftersales as $obj_request)
                        {
                            $obj_request->send_update_request($sdf);
                        }

                         //判断如果已经全部退款  则给积分（没有退还商品的情况）
                        $order_data = $order_obj->getRow('pay_status',array('order_id'=>$sdf['order_id']));
                        if($order_data['pay_status'] == '5'){
                            $order_obj->update(array('status'=>'finish'),array('order_id'=>$sdf['order_id']));
                        }

                        $this->end(true,'退款成功，更新退款单失败！');
                    }
                }else{
                    $this->pagedata['return_status'] = $obj_return_policy->change_status($re_sdf);        
                    if ($this->pagedata['return_status'])
                        $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];

                    $obj_refund_lists = kernel::servicelist("order.refund_finish");
                    foreach ($obj_refund_lists as $order_refund_service_object)
                    {                
                        $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                    }
                    
                    $obj_aftersales = kernel::servicelist("api.aftersales.request");
                    foreach ($obj_aftersales as $obj_request)
                    {
                        $obj_request->send_update_request($sdf);
                    }

                    //判断如果已经全部退款  则给积分（没有退还商品的情况）
                    $order_data = $order_obj->getRow('pay_status',array('order_id'=>$sdf['order_id']));
                    if($order_data['pay_status'] == '5'){
                        $order_obj->update(array('status'=>'finish'),array('order_id'=>$sdf['order_id']));
                    }
                    $refunds->update(array('memo'=>$result['1']),array('refund_id'=>$refund_id));
                    $this->end(true,'退款成功,结算失败,请等待管理员结算'.$result['1']);
                }
            }else{
                $obj_refunds = kernel::single("ectools_refund");
                $ref_rs = $obj_refunds->generate_after(array('refund_id'=>$refund_id,'refund_type'=>$sdf['refund_type']));
                
                $obj_refund_lists = kernel::servicelist("order.refund_finish");
                foreach ($obj_refund_lists as $order_refund_service_object)
                {                
                    $sdf['order_id'] = $return_info['order_id'];
                    $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                }
                // 发送同步日志.
                $order_refund_service_object->send_request($sdf);

                //ajx crm
                $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                $req_arr['order_id']=$sdf['order_id'];
                $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

                $this->pagedata['return_status'] = $obj_return_policy->change_status($re_sdf);        
                if ($this->pagedata['return_status'])
                    $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
                
                $obj_aftersales = kernel::servicelist("api.aftersales.request");
                foreach ($obj_aftersales as $obj_request)
                {
                    $obj_request->send_update_request($sdf);
                }

                //判断如果已经全部退款  则给积分（没有退还商品的情况）
                $order_data = $order_obj->getRow('pay_status',array('order_id'=>$sdf['order_id']));
                if($order_data['pay_status'] == '5'){
                    $order_obj->update(array('status'=>'finish'),array('order_id'=>$sdf['order_id']));
                }

                $this->end(true,app::get('aftersales')->_('退款成功,请等待管理员结算'));
            }
        }else{
            //申请售后流程
            $obj_refunds = kernel::single("ectools_refund");
            $ref_rs = $obj_refunds->generate_after(array('refund_id'=>$refund_id,'refund_type'=>$sdf['refund_type']));
            
            $obj_refund_lists = kernel::servicelist("order.refund_finish");
            foreach ($obj_refund_lists as $order_refund_service_object)
            {                
                $sdf['order_id'] = $return_info['order_id'];
                $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
            }
            // 发送同步日志.
            $order_refund_service_object->send_request($sdf);

            //ajx crm
            $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
            $req_arr['order_id']=$sdf['order_id'];
            $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

            $this->pagedata['return_status'] = $obj_return_policy->change_status($re_sdf);        
            if ($this->pagedata['return_status'])
                $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
            
            $obj_aftersales = kernel::servicelist("api.aftersales.request");
            foreach ($obj_aftersales as $obj_request)
            {
                $obj_request->send_update_request($sdf);
            }

            $this->end(true,app::get('aftersales')->_('退款成功,请等待管理员结算'));
        }
    }

    function return_agree(){
        
        $this->begin('index.php?app=aftersales&ctl=admin_returnproduct&act=intereven');
        $controller = kernel::single('aftersales_ctl_site_member',array('app'=>app::get('aftersales'),'arg1'=>false));
        $rp = app::get('aftersales')->model('return_product');
        $order_obj = app::get('b2c')->model('orders');
        $returns = $rp->getRow('*',array('return_id'=>$_POST['return_id']));
        if(isset($_POST['amount']) || $_POST['amount'] > 0){
            if($_POST['amount'] > $returns['amount']){
                $this->end(false,'输入金额大于买家申请，请修改！');
            }
        }else{
            $this->end(false,'请输入正确的金额！');
        }
        //处理申请单
        if($returns['shop_cost'] || $returns['amount_seller']){
            $total = $returns['shop_cost']+$returns['amount_seller']+$returns['amount'];
            $status['amount'] = $_POST['amount'];
            $status['shop_cost'] = $returns['shop_cost'];
            $amount_seller = $total - $status['shop_cost'] - $status['amount'];
            $status['amount_seller'] = $amount_seller;
        }else{
            $status['amount'] = $_POST['amount'];
        }

        $status['is_intervene'] = '4';

        $result = $rp->update($status,array('return_id'=>$_POST['return_id']));

        $order_obj->update(array('refund_status'=>'10'),array('order_id'=>$returns['order_id']));

        $obj_return_policy = kernel::single('aftersales_data_return_policy');

        $dly_add = kernel::single("business_mdl_dlyaddress");
        $dly_id = $dly_add->dump(array('store_id'=>$returns['store_id'],'refund'=>'true'));

        $re_sdf = array(
            'return_id' => $_POST['return_id'],
            'status' => '3',
            'refund_address' => $dly_id['da_id'],
            'close_time'=> 86400*(app::get('b2c')->getConf('member.to_buyer_refund'))+time(),
        );

        //修改订单状态
        $rs = $order_obj->getRow('score_u',array('order_id'=>$returns['order_id']));
        $score_u = $rs['score_u'] - $returns['return_score'];

        $this->pagedata['return_status'] = $obj_return_policy->change_status($re_sdf);        
        if ($this->pagedata['return_status'])
            $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
        
        $obj_aftersales = kernel::servicelist("api.aftersales.request");
        foreach ($obj_aftersales as $obj_request)
        {
            $obj_request->send_update_request($sdf);
        }

        //积分重新计算
        $obj_items = app::get('b2c')->model('order_items');
        $items = $obj_items->getList('score,sendnum',array('order_id'=>$returns['order_id']));
        $score = 0;
        foreach($items as $key=>$val){
            $score = $score+$val['score']*$val['sendnum'];
        }
        $data = array('score_g'=>$score,'refund_status'=>'3','score_u'=>$score_u);
        $order_obj->update($data,array('order_id'=>$returns['order_id']));

        //添加退款日志
        if ($this->user->user_id)
        {
            $obj_members = app::get('b2c')->model('members');
            $arrPams = $obj_members->dump($this->user->user_id, '*', array(':account@pam' => array('*')));
        }

        $log_text = "平台同意申请";
        $result = "SUCCESS";

        $returnLog = app::get('aftersales')->model("return_log");
        $sdf_return_log = array(
            'order_id' => $returns['order_id'],
            'return_id' => $returns['return_id'],
            'op_id' => $this->user->user_id,
            'op_name' => (!$this->user->user_id) ? app::get('b2c')->_('管理员') : $this->user->user_data['name'],
            'alttime' => time(),
            'behavior' => 'intereven_agree',
            'result' => $result,
            'role' => 'admin',
            'log_text' => $log_text,
        );

        $log_id = $returnLog->save($sdf_return_log);

        $objOrderLog = app::get('b2c')->model("order_log");

        $sdf_order_log = array(
            'rel_id' => $returns['order_id'],
            'op_id' => $this->user->user_id,
            'op_name' => (!$this->user->user_id) ? app::get('b2c')->_('管理员') : $this->user->user_data['name'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'refunds',
            'result' => 'SUCCESS',
            'log_text' => $log_text,
        );
        $log_id = $objOrderLog->save($sdf_order_log);

        $this->end(true,app::get('aftersales')->_('操作成功'));
    }

    function return_refuse(){
        $this->begin('index.php?app=aftersales&ctl=admin_returnproduct&act=intereven');
        $obj_product = app::get('aftersales')->model('return_product');
        $objOrder = app::get('b2c')->model('orders');
        $returns = $obj_product->getRow('*',array('return_id'=>$_POST['return_id']));

        $result = $obj_product->update(array('is_intervene'=>'4'),array('return_id'=>$_POST['return_id']));
        $objOrder->update(array('refund_status'=>'10'),array('order_id'=>$returns['order_id']));

        $aData['status'] = 11;
        $rs = $obj_product->update($aData,array('return_id'=>$_POST['return_id']));

        //添加退款日志
        if ($this->user->user_id)
        {
            $obj_members = app::get('b2c')->model('members');
            $arrPams = $obj_members->dump($this->user->user_id, '*', array(':account@pam' => array('*')));
        }

        $log_text = "平台拒绝申请";
        $result = "SUCCESS";

        $returnLog = app::get('aftersales')->model("return_log");
        $sdf_return_log = array(
            'order_id' => $returns['order_id'],
            'return_id' => $returns['return_id'],
            'op_id' => $this->user->user_id,
            'op_name' => (!$this->user->user_id) ? app::get('b2c')->_('管理员') : $this->user->user_data['name'],
            'alttime' => time(),
            'behavior' => 'intereven_refuse',
            'result' => $result,
            'role' => 'admin',
            'log_text' => $log_text,
        );

        $log_id = $returnLog->save($sdf_return_log);

        $objOrderLog = app::get('b2c')->model("order_log");
        $sdf_order_log = array(
            'rel_id' => $returns['order_id'],
            'op_id' => $this->user->user_id,
            'op_name' => (!$this->user->user_id) ? app::get('b2c')->_('管理员') : $this->user->user_data['name'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'refunds',
            'result' => 'SUCCESS',
            'log_text' => $log_text,
        );
        $log_id = $objOrderLog->save($sdf_order_log);

        if($rs){
            
            //修改订单状态
            $refund_status = array('refund_status'=>'6');
            $rs = $objOrder->update($refund_status,array('order_id'=>$returns['order_id']));

            $this->end(true, app::get('aftersales')->_('拒绝成功'));
        }else{
            $this->end(false, app::get('aftersales')->_('拒绝失败'));
        }
    }

    function balance_refund_finish(){
        $this->begin('index.php?app=aftersales&ctl=admin_returnproduct&act=index');
        $rp = app::get('aftersales')->model('return_product');
        $order_id = $rp->dump(array('return_id'=>$_GET['return_id']),'order_id');
        $returns = $rp->getRow('*',array('return_id'=>$_GET['return_id']));

        //生成退款单
           
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));

        $obj_order = app::get('b2c')->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $sdf_order = $obj_order->dump($order_id['order_id'],'*',$subsdf);

        $sdf['money'] = $returns['amount'];
        $sdf['return_score'] = $sdf_order['score_g']-$sdf_order['score_u'];

        $refunds = app::get('ectools')->model('refunds');
        //$objOrder->op_id = $this->user->user_id;
        //$objOrder->op_name = $this->user->user_data['account']['name'];


        //需要处理
        $sdf['op_id'] = $this->user->user_id;;
        $sdf['op_name'] = $this->user->user_data['account']['login_name'];
        //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
        unset($sdf['inContent']);



        
        $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');
        $sdf['payment'] = $sdf_order['payinfo']['pay_app_id'];

        $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
            
        $time = time();
        $sdf['refund_id'] = $refund_id = $refunds->gen_id();
        $sdf['pay_app_id'] = $sdf['payment'];
        $sdf['member_id'] = $sdf_order['member_id'] ? $sdf_order['member_id'] : 0;

        $obj_members = app::get('pam')->model('account');
        $buy_name = $obj_members->getRow('login_name',array('account_id'=>$sdf['member_id']));
        $sdf['account'] = $buy_name['login_name'];

        $sdf['currency'] = $sdf_order['currency'];
        $sdf['paycost'] = 0;
        $sdf['cur_money'] = $sdf['money'];
        //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
        $sdf['t_begin'] = $time;
        $sdf['t_payed'] = $time;
        $sdf['t_confirm'] = $time;
        $sdf['pay_object'] = 'order';
        $sdf['status'] = 'ready';
        $sdf['app_name'] = $arrPaymentInfo['app_name'];
        $sdf['app_version'] = $arrPaymentInfo['app_version'];
        $sdf['refund_type'] = '1';
        $sdf['is_safeguard'] = $returns['is_safeguard'];
        $sdf['order_id'] = $order_id['order_id'];
        if (!$obj_checkorder->check_order_refund($order_id['order_id'],$sdf,$message))
        {
             $this->end(false, app::get('aftersales')->_('退款单异常'));
        }
        $obj_refunds = kernel::single("ectools_refund");
        $controller   = kernel::single('b2c_ctl_site_order'); 
        $rs_buyer = $obj_refunds->generate($sdf, $controller, $msg);
        $obj_bills = app::get('ectools')->model('order_bills');
        $order_id = $obj_bills->getRow('rel_id',array('bill_id'=>$refund_id));
        $payment_id = $refunds->get_payment($order_id['rel_id']);
        $obj_payment = app::get('ectools')->model('payments');
        $cur_money = $obj_payment->dump($payment_id['bill_id'],'*');

        //判断是否是合并付款
        if($cur_money['merge_payment_id'] != ''){
            $payment_id['bill_id'] = $cur_money['merge_payment_id'];
            $cur_money['cur_money'] = 0;
            $total = $obj_payment->getList('*',array('merge_payment_id'=>$payment_id['bill_id'],'status'=>'succ'));
            foreach($total as $key=>$val){
                $cur_money['cur_money'] = $cur_money['cur_money'] + $val['cur_money'];
            }
        }

        //退款
        $refund_data = $refunds->getRow('*',array('refund_id'=>$sdf['refund_id']));
        //echo "<pre>";print_r($refund_data);exit;
        if($refund_data['pay_app_id'] != 'deposit'){
            if($refund_data['cur_money'] == 0){
                $obj_refunds = kernel::single("ectools_refund");
                $ref_rs = $obj_refunds->generate_after($sdf);
            }else{
                $refund_data['payment_info'] = $cur_money;
                $result = $obj_refunds->dorefund($refund_data,$this);
                $obj_refunds->callback($refund_data,$result);
            }
        }

        $obj_refund_lists = kernel::servicelist("order.refund_finish");
        foreach ($obj_refund_lists as $order_refund_service_object)
        {                
            $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
            
            if(isset($sdf['cur_money'])){
                 $log_text = '订单退款成功！退款金额'.$sdf['cur_money'].'元！';
            }else{
                 $log_text = '订单退款成功！';
            }

            $result = "SUCCESS";
            $returnLog = app::get('aftersales')->model("return_log");
            $sdf_return_log = array(
                'order_id' => $sdf['order_id'],
                'return_id' => $_GET['return_id'],
                'op_id' => $this->user->user_id,
                'op_name' => (!$this->user->user_id) ? app::get('b2c')->_('管理员') : $this->user->user_data['name'],
                'alttime' => time(),
                'behavior' => 'agreereturn',
                'result' => $result,
                'role' => 'admin',
                'log_text' => $log_text,
            );

            $log_id = $returnLog->save($sdf_return_log);

        }
        //修改订单状态
        $refund_status = array('refund_status'=>'4');
        $rs = $obj_order->update($refund_status,array('order_id'=>$sdf['order_id']));
        $status = array('status'=>'4','close_time'=>time());
        $rs = $rp->update($status,array('return_id'=>$_GET['return_id']));

        $this->end(true, app::get('aftersales')->_('退款成功'));
    }
}