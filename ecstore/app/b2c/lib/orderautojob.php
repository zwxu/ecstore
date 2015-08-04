<?php
class b2c_orderautojob{
    function order_auto_operation($member_id = '',$store_id = ''){
        $mdl_order   = kernel::single('b2c_mdl_orders');
        $mdl_return_product   = kernel::single('aftersales_mdl_return_product');

        //确认收货自动脚本
        if($member_id == '' && $store_id == ''){
            $n_finishs = $mdl_order->getList('order_id',array('confirm_time|lthan'=>time(),'status'=>'active','ship_status|in'=>array('1','3'),'pay_status|in'=>array('1','4'),'refund_status|in'=>array('0','2','4')));
            foreach($n_finishs as $k=>$order_id){
                $return_id = $mdl_return_product->getList('return_id',array('order_id'=>$order_id['order_id'],'status'=>'1','refund_type'=>'2'));
                if(isset($return_id['return_id'])){
                    unset($n_finishs[$k]);
                }
            }
        }elseif($member_id != '' && $store_id == ''){
            $n_finishs = $mdl_order->getList('order_id',array('confirm_time|lthan'=>time(),'status'=>'active','member_id'=>$member_id,'ship_status|in'=>array('1','3'),'pay_status|in'=>array('1','4'),'refund_status|in'=>array('0','2','4')));
            foreach($n_finishs as $k=>$order_id){
                $return_id = $mdl_return_product->getList('return_id',array('order_id'=>$order_id['order_id'],'status'=>'1','refund_type'=>'2'));
                if(isset($return_id['return_id'])){
                    unset($n_finishs[$k]);
                }
            }
        }elseif($member_id == '' && $store_id != ''){
            $n_finishs = $mdl_order->getList('order_id',array('confirm_time|lthan'=>time(),'status'=>'active','store_id'=>$store_id,'ship_status|in'=>array('1','3'),'pay_status|in'=>array('1','4'),'refund_status|in'=>array('0','2','4')));
            foreach($n_finishs as $k=>$order_id){
                $return_id = $mdl_return_product->getList('return_id',array('order_id'=>$order_id['order_id'],'status'=>'1','refund_type'=>'2'));
                if(isset($return_id['return_id'])){
                    unset($n_finishs[$k]);
                }
            }
        }
        if($n_finishs){
            $this->do_finish($n_finishs);
        }

        //付款超时，自动关闭交易
        $close_time = time()-(app::get('b2c')->getConf('member.to_close'))*86400;
        if($member_id == '' && $store_id == ''){
            $n_close = $mdl_order->getList('order_id',array('createtime|lthan'=>$close_time,'status'=>'active','pay_status'=>0,'order_type'=>'normal'));
        }elseif($member_id != '' && $store_id == ''){
            $n_close = $mdl_order->getList('order_id',array('createtime|lthan'=>$close_time,'status'=>'active','member_id'=>$member_id,'pay_status'=>0,'order_type'=>'normal'));
        }elseif($member_id == '' && $store_id != ''){
            $n_close = $mdl_order->getList('order_id',array('createtime|lthan'=>$close_time,'status'=>'active','store_id'=>$store_id,'pay_status'=>0,'order_type'=>'normal'));
        }
        if($n_close){
            $this->do_close($n_close);
        }

        //未发货时，买家退款，卖家超时响应，自动同意退款
        $agree_time = time()-(app::get('b2c')->getConf('member.to_agree'))*86400;
        if($member_id == '' && $store_id == ''){
            $n_agree = $mdl_return_product->getList('order_id,return_id',array('add_time|lthan'=>$agree_time,'status'=>1,'refund_type'=>1));
        }elseif($member_id == '' && $store_id != ''){
            $n_agree = $mdl_return_product->getList('order_id,return_id',array('add_time|lthan'=>$agree_time,'status'=>1,'store_id'=>$store_id,'refund_type'=>1));
        }elseif($member_id != '' && $store_id == ''){
            $n_agree = $mdl_return_product->getList('order_id,return_id',array('add_time|lthan'=>$agree_time,'status'=>1,'member_id'=>$member_id,'refund_type'=>1));
        }
        if($n_agree){
            $this->do_agree($n_agree);
        }

        //不需要退货时，买家退款，卖家超时响应，自动同意退款
        $agree_time = time()-(app::get('b2c')->getConf('member.to_agree'))*86400;
        if($member_id == '' && $store_id == ''){
            $n_agrees = $mdl_return_product->getList('order_id,return_id',array('add_time|lthan'=>$agree_time,'status'=>1,'refund_type|in'=>array('3','4')));
        }elseif($member_id == '' && $store_id != ''){
            $n_agrees = $mdl_return_product->getList('order_id,return_id',array('add_time|lthan'=>$agree_time,'status'=>1,'store_id'=>$store_id,'refund_type|in'=>array('3','4')));
        }elseif($member_id != '' && $store_id == ''){
            $n_agrees = $mdl_return_product->getList('order_id,return_id',array('add_time|lthan'=>$agree_time,'status'=>1,'member_id'=>$member_id,'refund_type|in'=>array('3','4')));
        }
        //echo "<pre>";print_r($n_agrees);exit;
        if($n_agrees){
            $this->do_agrees($n_agrees);
        }

        //需要退货时，买家退款，卖家超时响应，自动同意退款
        $agree_time = time()-(app::get('b2c')->getConf('member.to_agree'))*86400;
        if($member_id == '' && $store_id == ''){
            $n_refund_agree = $mdl_return_product->getList('order_id,return_id',array('close_time|lthan'=>$agree_time,'status'=>12,'refund_type'=>2));
            if($n_refund_agree){
                foreach($n_refund_agree as $key=>$val){
                    $refund_status = $mdl_order->getRow('refund_status',array('order_id'=>$val['order_id']));
                    if($refund_status['refund_status']!='5'){
                        unset($n_refund_agree[$key]);
                    }
                }
            }
        }elseif($member_id == '' && $store_id != ''){
            $n_refund_agree = $mdl_return_product->getList('order_id,return_id',array('close_time|lthan'=>$agree_time,'status'=>12,'store_id'=>$store_id,'refund_type'=>2));
            
            if($n_refund_agree){
                foreach($n_refund_agree as $key=>$val){
                    $refund_status = $mdl_order->getRow('refund_status',array('order_id'=>$val['order_id']));
                    if($refund_status['refund_status']!='5'){
                        unset($n_refund_agree[$key]);
                    }
                }
            }
        }elseif($member_id != '' && $store_id == ''){
            $n_refund_agree = $mdl_return_product->getList('order_id,return_id',array('close_time|lthan'=>$agree_time,'status'=>12,'member_id'=>$member_id,'refund_type'=>2));
            
            if($n_refund_agree){
                foreach($n_refund_agree as $key=>$val){
                    $refund_status = $mdl_order->getRow('refund_status',array('order_id'=>$val['order_id']));
                    if($refund_status['refund_status']!='5'){
                        unset($n_refund_agree[$key]);
                    }
                }
            }
        }

        if($n_refund_agree){
            $this->do_refund_agrees($n_refund_agree);
        }

        //需要退货时，买家退货，卖家超时响应，自动同意申请
        $agree_time = time()-(app::get('b2c')->getConf('member.to_agree'))*86400;
        if($member_id == '' && $store_id == ''){
            $n_refund_pass = $mdl_return_product->getList('order_id,return_id',array('add_time|lthan'=>$agree_time,'status'=>1,'refund_type'=>2));
        }elseif($member_id == '' && $store_id != ''){
            $n_refund_pass = $mdl_return_product->getList('order_id,return_id',array('add_time|lthan'=>$agree_time,'status'=>1,'store_id'=>$store_id,'refund_type'=>2));
        }elseif($member_id != '' && $store_id == ''){
            $n_refund_pass = $mdl_return_product->getList('order_id,return_id',array('add_time|lthan'=>$agree_time,'status'=>1,'member_id'=>$member_id,'refund_type'=>2));
        }
        if($n_refund_pass){
            $this->do_refund_pass($n_refund_pass);
        }

        //需要退货时，买家退货超时，自动取消退款申请
        $canael_time = time()-(app::get('b2c')->getConf('member.to_buyer_refund'))*86400;
        if($member_id == '' && $store_id == ''){
            $n_cancels = $mdl_return_product->getList('order_id,return_id',array('close_time|lthan'=>$canael_time,'status'=>3,'refund_type'=>2));
            foreach($n_cancels as $k=>$v){
                $n_cancel = $mdl_order->dump(array('order_id'=>$v['order_id'],'refund_type'=>'3'));
                if(empty($n_cancel)){
                    unset($n_cancels[$k]);
                }
            }
        }elseif($member_id != '' && $store_id == ''){
            $n_cancels = $mdl_return_product->getList('order_id,return_id',array('close_time|lthan'=>$canael_time,'status'=>3,'refund_type'=>2,'member_id'=>$member_id));
            foreach($n_cancels as $k=>$v){
                $n_cancel = $mdl_order->dump(array('order_id'=>$v['order_id'],'refund_type'=>'3'));
                if(empty($n_cancel)){
                    unset($n_cancels[$k]);
                }
            }
        }elseif($member_id == '' && $store_id != ''){
            $n_cancels = $mdl_return_product->getList('order_id,return_id',array('close_time|lthan'=>$canael_time,'status'=>3,'refund_type'=>2,'store_id'=>$store_id));
            foreach($n_cancels as $k=>$v){
                $n_cancel = $mdl_order->dump(array('order_id'=>$v['order_id'],'refund_type'=>'3'));
                if(empty($n_cancel)){
                    unset($n_cancels[$k]);
                }
            }
        }
        if($n_cancels){
            $this->do_refund_cancel($n_cancels);
        }

        //需要修改时，买家超时修改
        $agree_time = time()-(app::get('b2c')->getConf('member.to_buyer_edit'))*86400;
        if($member_id == '' && $store_id == ''){
            $n_edit = $mdl_return_product->getList('order_id,return_id',array('close_time|lthan'=>$agree_time,'status'=>11));
            if($n_edit){
                foreach($n_edit as $key=>$val){
                    $refund_status = $mdl_order->getRow('refund_status',array('order_id'=>$val['order_id']));
                    if($refund_status['refund_status']!='6'){
                        unset($n_edit[$key]);
                    }
                }
            }
        }elseif($member_id == '' && $store_id != ''){
            $n_edit = $mdl_return_product->getList('order_id,return_id',array('close_time|lthan'=>$agree_time,'status'=>11,'store_id'=>$store_id));
            
            if($n_edit){
                foreach($n_edit as $key=>$val){
                    $refund_status = $mdl_order->getRow('refund_status',array('order_id'=>$val['order_id']));
                    if($refund_status['refund_status']!='6'){
                        unset($n_edit[$key]);
                    }
                }
            }
        }elseif($member_id != '' && $store_id == ''){
            $n_edit = $mdl_return_product->getList('order_id,return_id',array('close_time|lthan'=>$agree_time,'status'=>11,'member_id'=>$member_id));
            
            if($n_edit){
                foreach($n_edit as $key=>$val){
                    $refund_status = $mdl_order->getRow('refund_status',array('order_id'=>$val['order_id']));
                    if($refund_status['refund_status']!='6'){
                        unset($n_edit[$key]);
                    }
                }
            }
        }

        if($n_edit){
            $this->do_refund_atuo_cancel($n_edit);
        }

    }

     public function do_finish($n_finishs)
     {
        $controller   = kernel::single('b2c_ctl_site_order');
        $obj_order_bills = kernel::single("ectools_mdl_order_bills");
        $point_money_value = app::get('b2c')->getConf('site.point_money_value');

        $system_money_decimals = app::get('b2c')->getConf('system.money.decimals');
        $system_money_operation_carryset = app::get('b2c')->getConf('system.money.operation.carryset');

        foreach($n_finishs as $k=>$order_id){
            $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
            if ($obj_checkorder->check_order_finish($order_id['order_id'],'',$message))
            {
                $sdf['order_id'] = $order_id['order_id'];
                //$arrMember = $this->get_current_member();
                $sdf['op_id'] = '0';
                $sdf['opname'] = 'auto';
                $sdf['confirm_time'] = time();
                
                //生成结算单
                $obj_order = kernel::single("b2c_mdl_orders");
                $money = $obj_order->getRow('payed,pmt_order,cost_freight,is_protect,cost_protect,cost_payment,member_id,ship_status,score_u,score_g,discount_value',array('order_id'=>$order_id['order_id']));
                $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
                if ($obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
                {
                     //$this->end(false, $message);
                }

                $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
                $sdf_order = $obj_order->dump($sdf['order_id'],'*',$subsdf);

                $refunds = kernel::single("ectools_mdl_refunds");
                //$objOrder->op_id = $this->user->user_id;
                //$objOrder->op_name = $this->user->user_data['account']['name'];
                //$sdf['op_id'] = $this->user->user_id;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                unset($sdf['inContent']);
                
                $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");
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
                
                $return_product_obj = kernel::single("aftersales_mdl_return_product");
                $returns = $return_product_obj->getList('amount',array('order_id'=>$sdf['order_id'],'refund_type|in'=>array('3','4'),'status'=>'3'));
                if($returns[0]['amount']){
                    if($money['is_protect']){
                        $cost_freight = $money['cost_freight']+$money['cost_payment']+$money['cost_protect']-$returns[0]['amount'];
                    }else{
                        $cost_freight = $money['cost_freight']+$money['cost_payment']-$returns[0]['amount'];
                    }
                    if($money['discount_value'] > 0){
                        $total_money = ($money['payed'])+$money['pmt_order']-$cost_freight+($money['discount_value']);
                    }else{
                        $total_money = ($money['payed'])+$money['pmt_order']-$cost_freight;
                    }
                    $obj_items = kernel::single("b2c_mdl_order_items");
                    $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));
                    //退款金额小于运费
                    if($cost_freight >= 0){
                        $profit = 0;
                        foreach($items as $k=>$v){
                            $obj_cat = kernel::single("b2c_mdl_goods_cat");
                            $obj_goods = kernel::single("b2c_mdl_goods");
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
                            if($total_money > 0){
                                $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                            }else{
                                $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum'];
                            }
                        }
                        $freight_pro = app::get('b2c')->getConf('member.profit');
                        $profit = $profit + $cost_freight*($freight_pro/100);
                    }else{
                        $freight_pro = app::get('b2c')->getConf('member.profit');

                        $total_money = ($money['payed']+($money['discount_value']))*($freight_pro/100);
                    }

                    //计算系统价格 
                    $math = kernel::single("ectools_math");
                    $profit = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);
                        
                    $sdf['money'] = ($money['payed']-($money['score_g'])/$point_money_value)-$profit;
                    $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                    //end
                    unset($sdf['return_score']);

                    $refunds = kernel::single("ectools_mdl_refunds");
                    //$objOrder->op_id = $this->user->user_id;
                    //$objOrder->op_name = $this->user->user_data['account']['name'];
                    //$sdf['op_id'] = $this->user->user_id;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    
                    $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");

                    $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                        
                    $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                    $sdf['cur_money'] = $sdf['money'];
                    //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                    $sdf['op_id'] = 0;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    $sdf['status'] = 'ready';
                    $sdf['app_name'] = $arrPaymentInfo['app_name'];
                    $sdf['app_version'] = $arrPaymentInfo['app_version'];
                    $sdf['refund_type'] = '2';
                    $obj_ys = kernel::single("business_mdl_storemanger");
                    $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                    $sdf['account'] = $ys['company_name'];
                    $sdf['profit'] = $profit;
                    $obj_refunds = kernel::single("ectools_refund");
                    if ($obj_checkorder->check_order_finish($order_id['order_id'],'',$message)){
                        $res = $obj_order_bills->dump(array('rel_id'=>$sdf['order_id'],'bill_type'=>'blances'),'bill_id');
                        if(!$res){
                            $rs_seller = $obj_refunds->generate($sdf, $controller, $msg);
                            // 增加经验值
                            $obj_member = kernel::single("b2c_mdl_members");
                            $obj_member->change_exp($money['member_id'], floor($total_money));
                        }
                    }
                }elseif($money['ship_status'] == '3'){
                    //部分退款的确认收货
                    $obj_items = kernel::single("b2c_mdl_order_items");
                    $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));
                    
                    $payed = 0;
                    foreach($items as $k=>$v){
                        $payed = $payed+$v['price']*$v['sendnum'];
                    }
                    $payed = $payed - $money['pmt_order'];
                    //剩余可打金额
                    $return_product_obj = kernel::single("aftersales_mdl_return_product");
                    $amount = $return_product_obj->getRow('amount',array('order_id'=>$sdf['order_id'],'status'=>'6'));

                    $money_useful = ($money['payed'])+($money['discount_value']);
                    //剩余杂费
                    $cost_freight = $money_useful - $payed;

                    $total_money = $payed+$money['pmt_order'];

                    $profit = 0;
                    foreach($items as $k=>$v){
                        $obj_cat = kernel::single("b2c_mdl_goods_cat");
                        $obj_goods = kernel::single("b2c_mdl_goods");
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
                        if($total_money > 0){
                            $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                        }else{
                            $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum'];
                        }
                    }
                    $freight_pro = app::get('b2c')->getConf('member.profit');
                    $profit = $profit + $cost_freight*($freight_pro/100);

                    //计算系统价格 
                    $math = kernel::single("ectools_math");
                    $profit = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);

                    $sdf['money'] = $money_useful-$profit-($money['score_g'])/$point_money_value;
                    $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                    //end

                    unset($sdf['return_score']);

                    $refunds = kernel::single("ectools_mdl_refunds");
                    //$objOrder->op_id = $this->user->user_id;
                    //$objOrder->op_name = $this->user->user_data['account']['name'];
                    //$sdf['op_id'] = $this->user->user_id;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    
                    $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");

                    $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                        
                    $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                    $sdf['cur_money'] = $sdf['money'];
                    //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                    $sdf['op_id'] = 0;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    $sdf['status'] = 'ready';
                    $sdf['app_name'] = $arrPaymentInfo['app_name'];
                    $sdf['app_version'] = $arrPaymentInfo['app_version'];
                    $sdf['refund_type'] = '2';

                    $obj_ys = kernel::single("business_mdl_storemanger");
                    $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                    $sdf['account'] = $ys['company_name'];
                    $sdf['profit'] = $profit;
                    $obj_refunds = kernel::single("ectools_refund");
                    if ($obj_checkorder->check_order_finish($order_id['order_id'],'',$message)){
                        $res = $obj_order_bills->dump(array('rel_id'=>$sdf['order_id'],'bill_type'=>'blances'),'bill_id');
                        if(!$res){
                            $rs_seller = $obj_refunds->generate($sdf, $controller, $msg);

                            // 增加经验值
                            $obj_member = kernel::single("b2c_mdl_members");
                            $obj_member->change_exp($money['member_id'], floor($total_money));
                        }
                    }
                }else{
                    //进行提成计算（正常流程）
                    if($money['is_protect']){
                        $cost_freight = $money['cost_freight']+$money['cost_payment']+$money['cost_protect'];
                    }else{
                        $cost_freight = $money['cost_freight']+$money['cost_payment'];
                    }
                    $total_money = $money['payed']+$money['pmt_order']-$cost_freight+($money['discount_value']);
                    $obj_items = kernel::single("b2c_mdl_order_items");
                    $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));

                    $profit = 0;
                    foreach($items as $k=>$v){
                        $obj_cat = kernel::single("b2c_mdl_goods_cat");
                        $obj_goods = kernel::single("b2c_mdl_goods");
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
                        if($total_money > 0){
                            $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                        }else{
                            $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum'];
                        }
                    }
                    $freight_pro = app::get('b2c')->getConf('member.profit');
                    $profit = $profit + $cost_freight*($freight_pro/100);
                    
                    //计算系统价格 
                    $math = kernel::single("ectools_math");
                    $profit = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);

                    $sdf['money'] = $money['payed']+($money['discount_value'])-$profit-($money['score_g'])/$point_money_value;
                    $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                    //end

                    unset($sdf['return_score']);

                    $refunds = kernel::single("ectools_mdl_refunds");
                    //$objOrder->op_id = $this->user->user_id;
                    //$objOrder->op_name = $this->user->user_data['account']['name'];
                    //$sdf['op_id'] = $this->user->user_id;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    
                    $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");

                    $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                        
                    $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                    $sdf['cur_money'] = $sdf['money'];
                    //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                    $sdf['op_id'] = 0;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    $sdf['status'] = 'ready';
                    $sdf['app_name'] = $arrPaymentInfo['app_name'];
                    $sdf['app_version'] = $arrPaymentInfo['app_version'];
                    $sdf['refund_type'] = '2';
                    $obj_ys = kernel::single("business_mdl_storemanger");
                    $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                    $sdf['account'] = $ys['company_name'];
                    $sdf['profit'] = $profit;
                    $obj_refunds = kernel::single("ectools_refund");
                    if ($obj_checkorder->check_order_finish($order_id['order_id'],'',$message)){
                        $res = $obj_order_bills->dump(array('rel_id'=>$sdf['order_id'],'bill_type'=>'blances'),'bill_id');
                        if(!$res){
                            $rs_seller = $obj_refunds->generate($sdf, $controller, $msg);

                            // 增加经验值
                            $obj_member = kernel::single("b2c_mdl_members");
                            $obj_member->change_exp($money['member_id'], floor($total_money));
                        }
                    }
                }
                //将款项打给卖家
                if($rs_seller){
                    $b2c_order_finish = kernel::single("b2c_order_finish");
                    $b2c_order_finish->generate($sdf, $controller, $message);
                    $refund = kernel::single("ectools_mdl_refunds");
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
                                    //$this->end(true, '确认收货成功！');

                                }else{
                                    //$this->end(true, '确认收货成功！');
                                }
                            }else{
                                $refund->update(array('memo'=>$result['1']),array('refund_id'=>$refund_id));
                                //$this->end(true, '确认收货成功！'.$result['1']);
                            }
                        }else{
                            $obj_refunds = kernel::single("ectools_refund");
                            $ref_rs = $obj_refunds->generate_after(array('refund_id'=>$refund_id,'refund_type'=>'2'));
               
                            if ($ref_rs)
                            {
                                //$this->end(true, '确认收货成功！');

                            }else{
                                //$this->end(false, '确认收货成功！更新结算单状态失败！');
                            }
                        }
                    }else{
                        //$this->end(false, '结算单类型错误！');
                    }
                }
            }
        }
    }

    function do_close($n_close){
        $controller   = kernel::single('b2c_ctl_site_order');
        foreach($n_close as $k=>$order_id){
            $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
            if (!$obj_checkorder->check_order_cancel($order_id['order_id'],'',$message))
            {
               //echo json_encode($message);
               exit;
            }
            
            $sdf['order_id'] = $order_id['order_id'];
            $sdf['op_id'] = '0';
            $sdf['opname'] = 'auto';
            
            $b2c_order_cancel = kernel::single("b2c_order_cancel");
            if ($b2c_order_cancel->generate($sdf, $controller, $message))
            {
                //ajx crm
                $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                $req_arr['order_id']=$order_id['order_id'];
                $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

                //echo json_encode('订单取消成功！');
            }
            else
            {
                //echo json_encode('订单取消失败！');
            }
        }
    }

    function do_refund_atuo_cancel($n_edit){
        $rp = kernel::single("aftersales_mdl_return_product");
        $obj_order = kernel::single("b2c_mdl_orders");
        foreach($n_edit as $k=>$ids){
            $rp->update(array('status'=>'10'),array('return_id'=>$ids['return_id']));
            //开始确认收货时间
            $confirm_time = $obj_order->getRow('confirm_time',array('order_id'=>$ids['order_id']));
            $time = $confirm_time['confirm_time'] + time();

            //查询订单状态
            $status = $obj_order->dump($ids['order_id'],'status');
            if($status['status'] == 'active'){
                $obj_order->update(array('refund_status'=>'2','confirm_time'=>$time),array('order_id'=>$ids['order_id']));
            }else{
                $obj_order->update(array('refund_status'=>'2'),array('order_id'=>$ids['order_id']));
            }

            $log_text = "系统自动撤销";
            $result = "SUCCESS";
            $returnLog = kernel::single("aftersales_mdl_return_log");
            $sdf_return_log = array(
                'order_id' => $ids['order_id'],
                'return_id' => $ids['return_id'],
                'op_id' => 0,
                'op_name' => 'auto',
                'alttime' => time(),
                'behavior' => 'cancel',
                'result' => $result,
                'role' => 'admin',
                'log_text' => $log_text,
            );

            $log_id = $returnLog->save($sdf_return_log);

            $objOrderLog = kernel::single("b2c_mdl_order_log");

            $sdf_order_log = array(
                'rel_id' => $ids['order_id'],
                'op_id' => 0,
                'op_name' => 'auto',
                'alttime' => time(),
                'bill_type' => 'order',
                'behavior' => 'refunds',
                'result' => $result,
                'log_text' => $log_text,
            );
            $log_id = $objOrderLog->save($sdf_order_log);
        }
    }

    function do_agree($n_agree){
        $controller = kernel::single('aftersales_ctl_site_member',array('app'=>app::get('aftersales'),'arg1'=>false));
        $obj_order = kernel::single("b2c_mdl_orders");
        foreach($n_agree as $key=>$return_id){
            $obj_return_policy = kernel::single('aftersales_data_return_policy');
           
            $obj_product = kernel::single("aftersales_mdl_return_product");
            $return_products = $obj_product->getList('*',array('return_id'=>$return_id['return_id']));
            
            if($return_products[0]['status'] != '1'){
                //$this->splash('failed',$url,app::get('aftersales')->_('非法请求'));
            }
            $sdf = array(
                'return_id' => $return_id['return_id'],
                'status' => '3',
            );
            
            $this->pagedata['return_status'] = $obj_return_policy->change_status($sdf);        
            if ($this->pagedata['return_status'])
                $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
            
            $obj_aftersales = kernel::servicelist("api.aftersales.request");
            foreach ($obj_aftersales as $obj_request)
            {
                $obj_request->send_update_request($sdf);
            }
            //生成退款单
            $sdf['order_id'] = $return_id['order_id'];
            $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));

            $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
            $sdf_order = $obj_order->dump($sdf['order_id'],'*',$subsdf);

            $sdf['money'] = $sdf_order['payed'];
            $sdf['return_score'] = $sdf_order['score_g']-$sdf_order['score_u'];

            $refunds = kernel::single("ectools_mdl_refunds");
            //$objOrder->op_id = $this->user->user_id;
            //$objOrder->op_name = $this->user->user_data['account']['name'];
            $sdf['op_id'] = 0;
            $sdf['op_name'] = 'auto';
            //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
            unset($sdf['inContent']);
            
            $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");
            $sdf['payment'] = ($sdf['payment']) ? $sdf['payment'] : $sdf_order['payinfo']['pay_app_id'];

            $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                
            $time = time();
            $sdf['refund_id'] = $refund_id = $refunds->gen_id();
            $sdf['pay_app_id'] = $sdf['payment'];
            $sdf['member_id'] = $sdf_order['member_id'] ? $sdf_order['member_id'] : 0;

            $obj_members = kernel::single("pam_mdl_account");
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
            //$sdf['op_id'] = $this->user->user_id;
            //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
            $sdf['status'] = 'ready';
            $sdf['app_name'] = $arrPaymentInfo['app_name'];
            $sdf['app_version'] = $arrPaymentInfo['app_version'];
            $sdf['refund_type'] = '1';
            $sdf['is_safeguard'] = $return_products[0]['is_safeguard'];
            if ($obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
            { 
                $obj_refunds = kernel::single("ectools_refund");
                if ($obj_refunds->generate($sdf, $controller, $msg))
                {
                    //进行退款操作
                    $refund = kernel::single("ectools_mdl_refunds");
                    $refund_data = $refund->dump($refund_id,'*');
                    $obj_bills = kernel::single("ectools_mdl_order_bills");
                    $order_id = $obj_bills->getRow('rel_id',array('bill_id'=>$refund_id));
                    $payment_id = $refund->get_payment($order_id['rel_id']);
                    $obj_payment = kernel::single("ectools_mdl_payments");
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
                    if($refund_data['pay_app_id'] != 'deposit'){
                        if($refund_data['cur_money'] == 0){
                            $obj_refunds = kernel::single("ectools_refund");
                            $ref_rs = $obj_refunds->generate_after($sdf);
                        }else{
                            $refund_data['payment_info'] = $cur_money;
                            $result = $obj_refunds->dorefund($refund_data,$this);
                            $obj_refunds->callback($refund_data,$result);
                        }

                        $is_refund_finished = false;
                        $obj_refund_lists = kernel::servicelist("order.refund_finish");
                        foreach ($obj_refund_lists as $order_refund_service_object)
                        {                
                            $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                        }
                        
                        if ($is_refund_finished)
                        {
                            //开始确认收货时间
                            $confirm_time = $obj_order->getRow('confirm_time,score_g,score_u,member_id',array('order_id'=>$sdf['order_id']));
                            $time = $confirm_time['confirm_time'] + time();

                            $point_money_value = app::get('b2c')->getConf('site.point_money_value');

                            //修改会员的冻结积分
                            $point_obj = app::get('pointprofessional')->model('members');
                            $reduce_score = $confirm_time['score_g'];
                            $point_obj->reduce_obtained($confirm_time['member_id'],$reduce_score,$sdf['order_id']);

                            //修改订单状态
                            $refund_status = array('refund_status'=>'4','confirm_time'=>$time,'score_g'=>0);
                            $rs = $obj_order->update($refund_status,array('order_id'=>$sdf['order_id']));

                            //退还积分
                            $obj_members_point = kernel::service('b2c_member_point_add');
                            $obj_members_point->change_point($sdf_order['member_id'],intval($refund_data['return_score']), $msg, 'order_refund_use', 1, $sdf['order_id'],0, 'refund');

                            //添加退款日志

                            $log_text = "系统自动同意退款";
                            $result_log = "SUCCESS";

                            $returnLog = kernel::single("aftersales_mdl_return_log");
                            $sdf_return_log = array(
                                'order_id' => $sdf['order_id'],
                                'return_id' => $return_id['return_id'],
                                'op_id' => '0',
                                'op_name' => 'auto',
                                'alttime' => time(),
                                'behavior' => 'agreereturn',
                                'result' => $result_log,
                                'role' => 'admin',
                                'log_text' => $log_text,
                            );

                            $log_id = $returnLog->save($sdf_return_log);
                        }
                        else
                        {
                            //$this->splash('failed',$url,$msg);
                        }
            
                    }else{
                        $is_refund_finished = false;
                        $obj_refund_lists = kernel::servicelist("order.refund_finish");
                        foreach ($obj_refund_lists as $order_refund_service_object)
                        {                
                            $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                        }
                        if ($is_refund_finished)
                        {
                            //开始确认收货时间
                            $confirm_time = $obj_order->getRow('confirm_time,score_g,score_u,member_id',array('order_id'=>$sdf['order_id']));
                            $time = $confirm_time['confirm_time'] + time();

                            $point_money_value = app::get('b2c')->getConf('site.point_money_value');

                            //修改会员的冻结积分
                            $point_obj = app::get('pointprofessional')->model('members');
                            $reduce_score = $confirm_time['score_g'];
                            $point_obj->reduce_obtained($confirm_time['member_id'],$reduce_score,$sdf['order_id']);

                            //修改订单状态
                            $refund_status = array('refund_status'=>'4','confirm_time'=>$time,'score_g'=>0);
                            $rs = $obj_order->update($refund_status,array('order_id'=>$sdf['order_id']));

                            //退还积分
                            $obj_members_point = kernel::service('b2c_member_point_add');
                            $obj_members_point->change_point($sdf_order['member_id'],intval($refund_data['return_score']), $msg, 'order_refund_use', 1, $sdf['order_id'],0, 'refund');

                            //添加退款日志

                            $log_text = "系统自动同意退款";
                            $result_log = "SUCCESS";

                            $returnLog = kernel::single("aftersales_mdl_return_log");
                            $sdf_return_log = array(
                                'order_id' => $sdf['order_id'],
                                'return_id' => $return_id['return_id'],
                                'op_id' => '0',
                                'op_name' => 'auto',
                                'alttime' => time(),
                                'behavior' => 'agreereturn',
                                'result' => $result_log,
                                'role' => 'admin',
                                'log_text' => $log_text,
                            );

                            $log_id = $returnLog->save($sdf_return_log);
                        }
                        else
                        {
                            //$this->splash('failed',$url,$msg);
                        }
                        //$this->splash('failed',$url,'结算失败！支付方式错误，请线下交易！');
                    }
                    
                }
                else
                {
                    //$this->splash('failed',$url,$msg);
                }
            }
        }
    }

    function do_agrees($n_agrees){
        //$url = $this->gen_url(array('app' =>'business','ctl'=>'site_member','act' =>'seller_order'));
        $controller = kernel::single('aftersales_ctl_site_member',array('app'=>app::get('aftersales'),'arg1'=>false));
        $rp = kernel::single("aftersales_mdl_return_product");
        foreach($n_agrees as $key=>$return_id){
            $objOrder = kernel::single("b2c_mdl_orders");

            $returns = $rp->getRow('*',array('return_id'=>$return_id['return_id']));
            
            //生成退款单
            $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));

            $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
            $sdf_order = $objOrder->dump($returns['order_id'],'*',$subsdf);

            $sdf['money'] = $returns['amount'];
            //$sdf['return_score'] = $sdf_order['score_g']-$sdf_order['score_u'];
            $sdf['order_id'] = $return_id['order_id'];
            $refunds = kernel::single("ectools_mdl_refunds");
            //$objOrder->op_id = $this->user->user_id;
            //$objOrder->op_name = $this->user->user_data['account']['name'];
            $sdf['op_id'] = 0;
            $sdf['op_name'] = 'auto';
            //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
            unset($sdf['inContent']);
            
            $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");
            $sdf['payment'] = ($sdf['payment']) ? $sdf['payment'] : $sdf_order['payinfo']['pay_app_id'];
            $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                
            $time = time();
            $sdf['refund_id'] = $refund_id = $refunds->gen_id();
            $sdf['pay_app_id'] = $sdf['payment'];
            $sdf['member_id'] = $sdf_order['member_id'] ? $sdf_order['member_id'] : 0;

            $obj_members = kernel::single("pam_mdl_account");
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
            $sdf['op_id'] = 0;
            //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
            $sdf['status'] = 'ready';
            $sdf['app_name'] = $arrPaymentInfo['app_name'];
            $sdf['app_version'] = $arrPaymentInfo['app_version'];
            $sdf['refund_type'] = '1';
            $sdf['is_safeguard'] = $returns['is_safeguard'];
            //echo "<pre>";print_r($sdf);exit;
            if ($obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
            {
                $obj_refunds = kernel::single("ectools_refund");

                //开始确认收货时间
                $confirm_time = $objOrder->getRow('confirm_time,status,score_u,member_id',array('order_id'=>$returns['order_id']));

                if($confirm_time['status'] == 'active'){
                    $rs_buyer = $obj_refunds->generate($sdf, $controller, $msg);
                }
                $time = $confirm_time['confirm_time'] + time();
                
                $refund_data = $refunds->dump($refund_id,'*');

                $score_u = $confirm_time['score_u']-$returns['return_score'];

                //修改订单状态
                if($confirm_time['status'] == 'active'){
                    $refund_status = array('refund_status'=>'4','confirm_time'=>$time,'score_u'=>$score_u);
                }else{
                    //修改订单状态
                    if($returns['safeguard_require'] == '3' || $returns['safeguard_require'] == '4'){
                        $refund_status = array('refund_status'=>'4','score_u'=>$score_u);
                    }else{
                        $refund_status = array('refund_status'=>'11','score_u'=>$score_u);
                    }
                }
                $rs = $objOrder->update($refund_status,array('order_id'=>$returns['order_id']));

                //退还积分
                $obj_members_point = kernel::service('b2c_member_point_add');
                $obj_members_point->change_point($confirm_time['member_id'],intval($returns['return_score']), $msg, 'order_refund_use', 1, $returns['order_id'],0, 'refund');

                $obj_bills = kernel::single("ectools_mdl_order_bills");
                $order_id = $obj_bills->getRow('rel_id',array('bill_id'=>$refund_id));
                $payment_id = $refunds->get_payment($order_id['rel_id']);
                $obj_payment = kernel::single("ectools_mdl_payments");
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
                    }

                    $obj_refund_lists = kernel::servicelist("order.refund_finish");
                    foreach ($obj_refund_lists as $order_refund_service_object)
                    {
                        $sdf['order_id'] = $returns['order_id'];
                        $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                    }
                    // 发送同步日志.
                    $order_refund_service_object->send_request($sdf);

                    //ajx crm
                    $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                    $req_arr['order_id']=$sdf['order_id'];
                    $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');
                    
                    $obj_aftersales = kernel::servicelist("api.aftersales.request");
                    foreach ($obj_aftersales as $obj_request)
                    {
                        $obj_request->send_update_request($sdf);
                    }

                    //判断如果已经全部退款  则给积分（没有退还商品的情况）
                    $order_data = $objOrder->getRow('pay_status',array('order_id'=>$sdf['order_id']));
                    if($order_data['pay_status'] == '5'){
                        $objOrder->update(array('status'=>'finish'),array('order_id'=>$sdf['order_id']));
                    }

                    $obj_return_policy = kernel::single('aftersales_data_return_policy');

                    $sdf_re = array(
                        'return_id' => $return_id['return_id'],
                        'status' => '3',
                    );
                    
                    $return_status = $obj_return_policy->change_status($sdf_re);

                    //添加退款日志

                    $log_text = "系统自动同意退款";
                    $result = "SUCCESS";

                    $returnLog = kernel::single("aftersales_mdl_return_log");
                    $sdf_return_log = array(
                        'order_id' => $sdf['order_id'],
                        'return_id' => $return_id['return_id'],
                        'op_id' => '0',
                        'op_name' => 'auto',
                        'alttime' => time(),
                        'behavior' => 'agreereturn',
                        'result' => $result,
                        'role' => 'admin',
                        'log_text' => $log_text,
                    );

                    $log_id = $returnLog->save($sdf_return_log);
                }else{
                    $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                    $req_arr['order_id']=$sdf['order_id'];
                    $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');


                    if($returns['safeguard_require'] == '3' || $returns['safeguard_require'] == '4'){
                        $re_sdf = array(
                            'return_id' => $return_id['return_id'],
                            'status' => '4',
                        );
                        $obj_refund_lists = kernel::servicelist("order.refund_finish");
                        foreach ($obj_refund_lists as $order_refund_service_object)
                        {                
                            $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                        }
                    }else{
                        $re_sdf = array(
                            'return_id' => $return_id['return_id'],
                            'status' => '15',
                        );
                    }
                    $obj_return_policy = kernel::single('aftersales_data_return_policy');
                    $this->pagedata['return_status'] = $obj_return_policy->change_status($re_sdf);        
                    if ($this->pagedata['return_status'])
                        $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
                    
                    $obj_aftersales = kernel::servicelist("api.aftersales.request");
                    foreach ($obj_aftersales as $obj_request)
                    {
                        $obj_request->send_update_request($sdf);
                    }

                    //添加退款日志
                    $log_text = "系统自动同意退款";
                    $result = "SUCCESS";

                    $returnLog = app::get('aftersales')->model("return_log");
                    $sdf_return_log = array(
                        'order_id' => $sdf['order_id'],
                        'return_id' => $return_id['return_id'],
                        'op_id' => 0,
                        'op_name' => 'auto',
                        'alttime' => time(),
                        'behavior' => 'agreereturn',
                        'result' => $result,
                        'role' => 'seller',
                        'log_text' => $log_text,
                    );

                    $log_id = $returnLog->save($sdf_return_log);

                    $objOrderLog = kernel::single("b2c_mdl_order_log");

                    $sdf_order_log = array(
                        'rel_id' => $sdf['order_id'],
                        'op_id' => 0,
                        'op_name' => 'auto',
                        'alttime' => time(),
                        'bill_type' => 'order',
                        'behavior' => 'refunds',
                        'result' => $result,
                        'log_text' => $log_text,
                    );
                    $log_id = $objOrderLog->save($sdf_order_log);
                }

            }
        
            //if ($this->pagedata['return_status'])
                //$this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];

            //$this->splash('success',$url,app::get('aftersales')->_('操作成功'));
        }
    }

    function do_refund_agrees($n_refund_agree){
        $controller = kernel::single('aftersales_ctl_site_member',array('app'=>app::get('aftersales'),'arg1'=>false));
        $rp = kernel::single("aftersales_mdl_return_product");
        $obj_order = kernel::single("b2c_mdl_orders");
        $system_money_decimals = app::get('b2c')->getConf('system.money.decimals');
        $system_money_operation_carryset = app::get('b2c')->getConf('system.money.operation.carryset');
        $refunds = kernel::single("ectools_mdl_refunds");
        $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");
        foreach($n_refund_agree as $key=>$return_id){
            //$url = $this->gen_url(array('app' =>'aftersales','ctl'=>'site_member','act' =>'seller_returns'));
            $returns = $rp->getRow('*',array('return_id'=>$return_id['return_id']));
            $obj_return_policy = kernel::single('aftersales_data_return_policy');

            $sdf = array(
                'return_id' => $return_id['return_id'],
                'status' => '6',
            );
            $sdf['order_id'] = $return_id['order_id'];
            $return_status = $obj_return_policy->change_status($sdf); 
            $obj_aftersales = kernel::servicelist("api.aftersales.request");
            foreach ($obj_aftersales as $obj_request)
            {
                $obj_request->send_update_request($sdf);
            }

            //判断是否是完结的订单
            $order_id = $rp->getRow('order_id,return_score',array('return_id'=>$return_id['return_id']));
            $status = $obj_order->getRow('status,score_u',array('order_id'=>$return_id['order_id']));
            if($status['status'] == 'active'){
                //生成退款单
               
                $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
                

                $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
                $sdf_order = $obj_order->dump($sdf['order_id'],'*',$subsdf);

                $sdf['money'] = $returns['amount'];
                $sdf['return_score'] = $sdf_order['score_g']-$sdf_order['score_u'];

                //$objOrder->op_id = $this->user->user_id;
                //$objOrder->op_name = $this->user->user_data['account']['name'];
                $sdf['op_id'] = 0;
                $sdf['op_name'] = 'auto';
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                unset($sdf['inContent']);
                
                $sdf['payment'] = ($sdf['payment']) ? $sdf['payment'] : $sdf_order['payinfo']['pay_app_id'];

                $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                    
                $time = time();
                $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                $sdf['pay_app_id'] = $sdf['payment'];
                $sdf['member_id'] = $sdf_order['member_id'] ? $sdf_order['member_id'] : 0;

                $obj_members = kernel::single("pam_mdl_account");
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
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                $sdf['status'] = 'ready';
                $sdf['app_name'] = $arrPaymentInfo['app_name'];
                $sdf['app_version'] = $arrPaymentInfo['app_version'];
                $sdf['refund_type'] = '1';
                $sdf['is_safeguard'] = $returns['is_safeguard'];
                if ($obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
                {
                    $obj_refunds = kernel::single("ectools_refund");
                    $rs_buyer = $obj_refunds->generate($sdf, $controller, $msg);
                    //需要结算退款单

                    $obj_bills = kernel::single("ectools_mdl_order_bills");
                    $order_id = $obj_bills->getRow('rel_id',array('bill_id'=>$refund_id));
                    $payment_id = $refunds->get_payment($order_id['rel_id']);
                    $obj_payment = kernel::single("ectools_mdl_payments");
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
                    $refund_data = $refunds->getRow('*',array('return_id'=>$sdf['refund_id']));
                    //echo "<pre>";print_r($refund_data);exit;
                    if($refund_data['pay_app_id'] != 'deposit'){
                        $obj_refunds = kernel::single("ectools_refund");
                        if($refund_data['cur_money'] == 0){
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
                    }

                    //添加退款日志

                    $log_text = "系统自动同意退款";
                    $result_log = "SUCCESS";

                    $returnLog = kernel::single("aftersales_mdl_return_log");
                    $sdf_return_log = array(
                        'order_id' => $sdf['order_id'],
                        'return_id' => $sdf['refund_id'],
                        'op_id' => '0',
                        'op_name' => 'auto',
                        'alttime' => time(),
                        'behavior' => 'agreereturn',
                        'result' => $result_log,
                        'role' => 'admin',
                        'log_text' => $log_text,
                    );

                    $log_id = $returnLog->save($sdf_return_log);

                    //生成运费结算单
                    if($returns['ship_cost'] > 0 || $returns['amount_seller']>0){
                        $freight_pro = app::get('b2c')->getConf('member.profit');
                        
                        $math = kernel::single("ectools_math");
                        $profit = ($returns['ship_cost']+$returns['amount_seller'])*($freight_pro/100);
                        $sdf['profit'] = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);
                        $sdf['money'] = $returns['ship_cost']+$returns['amount_seller']-$sdf['profit'];

                        unset($sdf['return_score']);

                        //$objOrder->op_id = $this->user->user_id;
                        //$objOrder->op_name = $this->user->user_data['account']['name'];
                        //$sdf['op_id'] = $this->user->user_id;
                        //$sdf['op_name'] = $this->user->user_data['account']['login_name'];

                        $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                            
                        $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                        $sdf['member_id'] = $sdf_order['store_id'] ? $sdf_order['store_id'] : 0;
                        $sdf['cur_money'] = $sdf['money'];
                        //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                        $sdf['op_id'] = 0;
                        $sdf['op_name'] = 'auto';
                        //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                        $sdf['status'] = 'ready';
                        $sdf['app_name'] = $arrPaymentInfo['app_name'];
                        $sdf['app_version'] = $arrPaymentInfo['app_version'];
                        $sdf['refund_type'] = '2';
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

                         if($result['0'] == "true"){
                            $obj_refunds = kernel::single("ectools_refund");
                            $ref_rs = $obj_refunds->generate_after($sdf);
                        }else{
                            $refunds->update(array('memo'=>$result['1']),array('refund_id'=>$sdf['refund_id']));
                        }
                    }
                 
                    
                    if ($is_refund_finished)
                    {
                        //发送同步日志.
                        $order_refund_service_object->send_request($sdf);

                        //ajx crm
                        $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                        $req_arr['order_id']=$sdf['order_id'];
                        $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

                        //积分重新计算
                        $obj_items = kernel::single("b2c_mdl_order_items");
                        $items = $obj_items->getList('score,sendnum',array('order_id'=>$sdf['order_id']));
                        $score = 0;
                        foreach($items as $key=>$val){
                            $score = $score+$val['score']*$val['sendnum'];
                        }

                        //开始确认收货时间
                        $confirm_time = $obj_order->getRow('confirm_time,score_g,member_id,member_id',array('order_id'=>$sdf['order_id']));
                        $time = $confirm_time['confirm_time'] + time();
                        
                        //修改订单状态
                        $refund_status = array('refund_status'=>'4','confirm_time'=>$time,'score_g'=>$score);
                        $rs = $obj_order->update($refund_status,array('order_id'=>$sdf['order_id']));

                        //修改会员的冻结积分
                        $point_obj = kernel::single("pointprofessional_mdl_members");
                        $reduce_score = $confirm_time['score_g']-$score;
                        $point_obj->reduce_obtained($confirm_time['member_id'],$reduce_score,$sdf['order_id']);

                        //退还积分
                        $obj_members_point = kernel::service('b2c_member_point_add');
                        $obj_members_point->change_point($confirm_time['member_id'],intval($order_id['return_score']), $msg, 'order_refund_use', 1, $sdf['order_id'],0, 'refund');

                        //$this->splash('success',$url,app::get('aftersales')->_('退款成功'));
                    }
                    else
                    {
                        //$this->splash('failed',$url,app::get('aftersales')->_('退款成功，发送日志失败'));
                    }
                }
            }else{
                //售后申请流程        
                /*$is_refund_finished = false;
                $obj_refund_lists = kernel::servicelist("order.refund_finish");
                foreach ($obj_refund_lists as $order_refund_service_object)
                {                
                    $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                }

                //判断是否是完结的订单
                $order_id = $rp->getRow('order_id,return_score',array('return_id'=>$_POST['return_id']));
                $status = $obj_order->getRow('status,score_u',array('order_id'=>$order_id['order_id']));*/

                //添加退款日志

                $log_text = "系统自动同意退款";
                $result = "SUCCESS";

                $returnLog = kernel::single("aftersales_mdl_return_log");
                $sdf_return_log = array(
                    'order_id' => $return_id['order_id'],
                    'return_id' => $return_id['return_id'],
                    'op_id' => 0,
                    'op_name' => 'auto',
                    'alttime' => time(),
                    'behavior' => 'agreereturn',
                    'result' => $result,
                    'role' => 'seller',
                    'log_text' => $log_text,
                );

                $log_id = $returnLog->save($sdf_return_log);

                $objOrderLog = kernel::single("b2c_mdl_order_log");

                $sdf_order_log = array(
                    'rel_id' => $return_id['order_id'],
                    'op_id' => 0,
                    'op_name' => 'auto',
                    'alttime' => time(),
                    'bill_type' => 'order',
                    'behavior' => 'refunds',
                    'result' => $result,
                    'log_text' => $log_text,
                );
                $log_id = $objOrderLog->save($sdf_order_log);
                
                //if ($is_refund_finished)
                //{
                    // 发送同步日志.
                    //$order_refund_service_object->send_request($sdf);

                    //ajx crm
                    $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                    $req_arr['order_id']=$return_id['order_id'];
                    $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');
                    //开始确认收货时间
                    $confirm_time = $obj_order->getRow('confirm_time',array('order_id'=>$return_id['order_id']));
                    $time = $confirm_time['confirm_time'] + time();
                    
                    //修改订单状态
                    if($returns['safeguard_require'] == '3' || $returns['safeguard_require'] == '4'){
                        $refund_status = array('refund_status'=>'4','score_u'=>$score_u);
                        $status = array('status'=>'4','close_time'=>time());
                        $obj_refund_lists = kernel::servicelist("order.refund_finish");
                        foreach ($obj_refund_lists as $order_refund_service_object)
                        {
                            $sdf['op_id'] = 0;
                            $sdf['op_name'] = 'auto';
                            $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                        }
                    }else{
                        $refund_status = array('refund_status'=>'11','score_u'=>$score_u);
                        $status = array('status'=>'15','close_time'=>time());
                    }
                    $rs = $obj_order->update($refund_status,array('order_id'=>$return_id['order_id']));
                    $rs = $rp->update($status,array('return_id'=>$return_id['return_id']));
                    //$this->splash('success',$url,app::get('aftersales')->_('退款成功,等待卖家打款到平台！'));
                /*}
                else
                {
                    $this->splash('failed',$url,app::get('aftersales')->_('退款成功，发送日志失败'));
                }*/
            }
        }
    }

    function do_refund_pass($n_refund_pass){
        $rp = kernel::single("aftersales_mdl_return_product");
        $objOrder = kernel::single("b2c_mdl_orders");
        $dly_add = kernel::single("business_mdl_dlyaddress");
        foreach($n_refund_pass as $key=>$return_id){
            $returns = $rp->getRow('*',array('return_id'=>$return_id['return_id']));
            $obj_return_policy = kernel::single('aftersales_data_return_policy');
            $dly_id = $dly_add->dump(array('store_id'=>$returns['store_id'],'refund'=>'true'));
            $close_time = time()+(app::get('b2c')->getConf('member.to_buyer_refund'))*86400;
            $sdf = array(
                'return_id' => $return_id['return_id'],
                'status' => '3',
                'refund_address' => $dly_id['da_id'],
                'close_time'=>$close_time,
            );

            //修改订单状态
            $rs = $objOrder->getRow('score_u',array('order_id'=>$returns['order_id']));
            $score_u = $rs['score_u'] - $returns['return_score'];
            
            $return_status = $obj_return_policy->change_status($sdf);        
            //if ($this->pagedata['return_status'])
                //$this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
            
            $obj_aftersales = kernel::servicelist("api.aftersales.request");
            foreach ($obj_aftersales as $obj_request)
            {
                $obj_request->send_update_request($sdf);
            }

            //积分重新计算
            $obj_items = kernel::single("b2c_mdl_order_items");
            $items = $obj_items->getList('score,sendnum',array('order_id'=>$returns['order_id']));
            $score = 0;
            foreach($items as $key=>$val){
                $score = $score+$val['score']*$val['sendnum'];
            }
            $data = array('score_g'=>$score,'refund_status'=>'3','score_u'=>$score_u);
            $objOrder->update($data,array('order_id'=>$returns['order_id']));

            //添加退款日志

            $log_text = "系统自动同意申请";
            $result_log = "SUCCESS";

            $returnLog = kernel::single("aftersales_mdl_return_log");
            $sdf_return_log = array(
                'order_id' => $returns['order_id'],
                'return_id' => $return_id['return_id'],
                'op_id' => '0',
                'op_name' => 'auto',
                'alttime' => time(),
                'behavior' => 'agreereturn',
                'result' => $result_log,
                'role' => 'admin',
                'log_text' => $log_text,
            );

            $log_id = $returnLog->save($sdf_return_log);

            $objOrderLog = kernel::single("b2c_mdl_order_log");

            $sdf_order_log = array(
                'rel_id' => $returns['order_id'],
                'op_id' => 0,
                'op_name' => 'auto',
                'alttime' => time(),
                'bill_type' => 'order',
                'behavior' => 'refunds',
                'result' => $result_log,
                'log_text' => $log_text,
            );
            $log_id = $objOrderLog->save($sdf_order_log);

            //$this->splash('success',$url,app::get('aftersales')->_('操作成功'));
        }
    }

    public function do_refund_cancel($n_cancels){
        $rp = kernel::single("aftersales_mdl_return_product");
        $objOrder = kernel::single("b2c_mdl_orders");
        foreach($n_cancels as $key=>$return_id){
            $data = array('status'=>'10');
            $rp->update($data,array('return_id'=>$return_id['return_id']));
            $confirm = $objOrder->dump(array('order_id'=>$return_id['order_id']));
            $confirm_time = $confirm['confirm_time']+time();
            $data_order = array('refund_status'=>'2','confirm_time'=>$confirm_time);
            $objOrder->update($data_order,array('order_id'=>$return_id['order_id']));
            
            //添加退款日志

            $log_text = "系统自动取消退款申请";
            $result_log = "SUCCESS";

            $returnLog = kernel::single("aftersales_mdl_return_log");
            $sdf_return_log = array(
                'order_id' => $return_id['order_id'],
                'return_id' => $return_id['return_id'],
                'op_id' => '0',
                'op_name' => 'auto',
                'alttime' => time(),
                'behavior' => 'agreereturn',
                'result' => $result_log,
                'role' => 'admin',
                'log_text' => $log_text,
            );

            $log_id = $returnLog->save($sdf_return_log);

            $objOrderLog = kernel::single("b2c_mdl_order_log");

            $sdf_order_log = array(
                'rel_id' => $return_id['order_id'],
                'op_id' => 0,
                'op_name' => 'auto',
                'alttime' => time(),
                'bill_type' => 'order',
                'behavior' => 'refunds',
                'result' => $result_log,
                'log_text' => $log_text,
            );
            $log_id = $objOrderLog->save($sdf_order_log);
        }
    }

    public function order_do_finish($order_id)
     {
        $point_money_value = app::get('b2c')->getConf('site.point_money_value');
        $controller   = kernel::single('b2c_ctl_site_order');
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        $obj_order_bills = kernel::single("ectools_mdl_order_bills");
        if ($obj_checkorder->check_order_finish($order_id,'',$message))
        {
            $sdf['order_id'] = $order_id;
            //$arrMember = $this->get_current_member();
            $sdf['op_id'] = '0';
            $sdf['opname'] = 'auto';
            $sdf['confirm_time'] = time();
            
            //生成结算单
            $obj_order = kernel::single("b2c_mdl_orders");
            $money = $obj_order->getRow('payed,pmt_order,cost_freight,is_protect,cost_protect,cost_payment,member_id,ship_status,score_u,score_g,discount_value',array('order_id'=>$order_id));
            $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
            if (!$obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
            {
                 //$this->end(false, $message);
            }

            $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
            $sdf_order = $obj_order->dump($sdf['order_id'],'*',$subsdf);

            $refunds = kernel::single("ectools_mdl_refunds");
            //$objOrder->op_id = $this->user->user_id;
            //$objOrder->op_name = $this->user->user_data['account']['name'];
            //$sdf['op_id'] = $this->user->user_id;
            //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
            unset($sdf['inContent']);
            
            $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");
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
            
            $return_product_obj = kernel::single("aftersales_mdl_return_product");
            $returns = $return_product_obj->getList('amount',array('order_id'=>$sdf['order_id'],'refund_type|in'=>array('3','4'),'status'=>'3'));
            if($returns[0]['amount']){
                if($money['is_protect']){
                    $cost_freight = $money['cost_freight']+$money['cost_payment']+$money['cost_protect']-$returns[0]['amount'];
                }else{
                    $cost_freight = $money['cost_freight']+$money['cost_payment']-$returns[0]['amount'];
                }
                if($money['discount_value'] > 0){
                    $total_money = ($money['payed'])+$money['pmt_order']-$cost_freight+($money['discount_value']);
                }else{
                    $total_money = ($money['payed'])+$money['pmt_order']-$cost_freight;
                }
                $obj_items = kernel::single("b2c_mdl_order_items");
                $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));
                //退款金额小于运费
                if($cost_freight >= 0){
                    $profit = 0;
                    foreach($items as $k=>$v){
                        $obj_cat = kernel::single("b2c_mdl_goods_cat");
                        $obj_goods = kernel::single("b2c_mdl_goods");
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
                        if($total_money>0){
                            $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                        }else{
                            $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum'];
                        }
                    }
                    $freight_pro = app::get('b2c')->getConf('member.profit');
                    $profit = $profit + $cost_freight*($freight_pro/100);
                }else{
                    $freight_pro = app::get('b2c')->getConf('member.profit');

                    $total_money = ($money['payed']+($money['discount_value']))*($freight_pro/100);
                }
                
                $sdf['money'] = ($money['payed']-($money['score_g'])/$point_money_value)-$profit;
                $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                //end
                unset($sdf['return_score']);

                $refunds = kernel::single("ectools_mdl_refunds");
                //$objOrder->op_id = $this->user->user_id;
                //$objOrder->op_name = $this->user->user_data['account']['name'];
                //$sdf['op_id'] = $this->user->user_id;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                
                $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");

                $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                    
                $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                $sdf['cur_money'] = $sdf['money'];
                //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                $sdf['op_id'] = 0;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                $sdf['status'] = 'ready';
                $sdf['app_name'] = $arrPaymentInfo['app_name'];
                $sdf['app_version'] = $arrPaymentInfo['app_version'];
                $sdf['refund_type'] = '2';
                $obj_ys = kernel::single("business_mdl_storemanger");
                $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                $sdf['account'] = $ys['company_name'];
                $sdf['profit'] = $profit;
                $res = $obj_order_bills->dump(array('rel_id'=>$sdf['order_id'],'bill_type'=>'blances'),'bill_id');
                if(!$res){
                    $obj_refunds = kernel::single("ectools_refund");
                    $rs_seller = $obj_refunds->generate($sdf, $controller, $msg);

                    // 增加经验值
                    $obj_member = kernel::single("b2c_mdl_members");
                    $obj_member->change_exp($money['member_id'], floor($total_money));
                }
            }elseif($money['ship_status'] == '3'){
                //部分退款的确认收货
                $obj_items = kernel::single("b2c_mdl_order_items");
                $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));
                
                $payed = 0;
                foreach($items as $k=>$v){
                    $payed = $payed+$v['price']*$v['sendnum'];
                }
                $payed = $payed - $money['pmt_order'];
                //剩余可打金额
                $return_product_obj = kernel::single("aftersales_mdl_return_product");
                $amount = $return_product_obj->getRow('amount',array('order_id'=>$sdf['order_id'],'status'=>'6'));

                $money_useful = ($money['payed'])+($money['discount_value']);
                //剩余杂费
                $cost_freight = $money_useful - $payed;

                $total_money = $payed+$money['pmt_order'];

                $profit = 0;
                foreach($items as $k=>$v){
                    $obj_cat = kernel::single("b2c_mdl_goods_cat");
                    $obj_goods = kernel::single("b2c_mdl_goods");
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
                    if($total_money>0){
                        $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                    }else{
                        $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum'];
                    }
                }
                $freight_pro = app::get('b2c')->getConf('member.profit');
                $profit = $profit + $cost_freight*($freight_pro/100);
                $sdf['money'] = $money_useful-$profit-($money['score_g'])/$point_money_value;
                $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                //end

                unset($sdf['return_score']);

                $refunds = kernel::single("ectools_mdl_refunds");
                //$objOrder->op_id = $this->user->user_id;
                //$objOrder->op_name = $this->user->user_data['account']['name'];
                //$sdf['op_id'] = $this->user->user_id;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                
                $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");

                $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                    
                $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                $sdf['cur_money'] = $sdf['money'];
                //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                $sdf['op_id'] = 0;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                $sdf['status'] = 'ready';
                $sdf['app_name'] = $arrPaymentInfo['app_name'];
                $sdf['app_version'] = $arrPaymentInfo['app_version'];
                $sdf['refund_type'] = '2';

                $obj_ys = kernel::single("business_mdl_storemanger");
                $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                $sdf['account'] = $ys['company_name'];
                $sdf['profit'] = $profit;
                $res = $obj_order_bills->dump(array('rel_id'=>$sdf['order_id'],'bill_type'=>'blances'),'bill_id');
                if(!$res){
                    $obj_refunds = kernel::single("ectools_refund");
                    $rs_seller = $obj_refunds->generate($sdf, $controller, $msg);

                    // 增加经验值
                    $obj_member = kernel::single("b2c_mdl_members");
                    $obj_member->change_exp($money['member_id'], floor($total_money));
                }
            }else{
                //进行提成计算（正常流程）
                if($money['is_protect']){
                    $cost_freight = $money['cost_freight']+$money['cost_payment']+$money['cost_protect'];
                }else{
                    $cost_freight = $money['cost_freight']+$money['cost_payment'];
                }
                $total_money = $money['payed']+$money['pmt_order']-$cost_freight+($money['discount_value']);
                $obj_items = kernel::single("b2c_mdl_order_items");
                $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));

                $profit = 0;
                foreach($items as $k=>$v){
                    $obj_cat = kernel::single("b2c_mdl_goods_cat");
                    $obj_goods = kernel::single("b2c_mdl_goods");
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
                    if($total_money>0){
                        $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                    }else{
                        $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum'];
                    }
                }
                $freight_pro = app::get('b2c')->getConf('member.profit');
                $profit = $profit + $cost_freight*($freight_pro/100);

                $sdf['money'] = $money['payed']+($money['discount_value'])-$profit-($money['score_g'])/$point_money_value;
                $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                //end

                unset($sdf['return_score']);

                $refunds = kernel::single("ectools_mdl_refunds");
                //$objOrder->op_id = $this->user->user_id;
                //$objOrder->op_name = $this->user->user_data['account']['name'];
                //$sdf['op_id'] = $this->user->user_id;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                
                $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");

                $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                    
                $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                $sdf['cur_money'] = $sdf['money'];
                //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                $sdf['op_id'] = 0;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                $sdf['status'] = 'ready';
                $sdf['app_name'] = $arrPaymentInfo['app_name'];
                $sdf['app_version'] = $arrPaymentInfo['app_version'];
                $sdf['refund_type'] = '2';
                $obj_ys = kernel::single("business_mdl_storemanger");
                $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                $sdf['account'] = $ys['company_name'];
                $sdf['profit'] = $profit;
                $res = $obj_order_bills->dump(array('rel_id'=>$sdf['order_id'],'bill_type'=>'blances'),'bill_id');
                if(!$res){
                    $obj_refunds = kernel::single("ectools_refund");
                    $rs_seller = $obj_refunds->generate($sdf, $controller, $msg);

                    // 增加经验值
                    $obj_member = kernel::single("b2c_mdl_members");
                    $obj_member->change_exp($money['member_id'], floor($total_money));
                }
            }
            
            //将款项打给卖家
            if($rs_seller){
                $b2c_order_finish = kernel::single("b2c_order_finish");
                $b2c_order_finish->generate($sdf, $controller, $message);
                $refund = kernel::single("ectools_mdl_refunds");
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
                                //$this->end(true, '确认收货成功！');

                            }else{
                                //$this->end(true, '确认收货成功！');
                            }
                        }else{
                            $refund->update(array('memo'=>$result['1']),array('refund_id'=>$refund_id));
                            //$this->end(true, '确认收货成功！'.$result['1']);
                        }
                    }else{
                        $obj_refunds = kernel::single("ectools_refund");
                        $ref_rs = $obj_refunds->generate_after(array('refund_id'=>$refund_id,'refund_type'=>'2'));
           
                        if ($ref_rs)
                        {
                            //$this->end(true, '确认收货成功！');

                        }else{
                            //$this->end(false, '确认收货成功！更新结算单状态失败！');
                        }
                    }
                }else{
                    //$this->end(false, '结算单类型错误！');
                }
            }
        }
    }

    function order_do_agree($return_id,$order_id){
        //echo "<pre>";print_r($n_agree);exit;
        $controller = kernel::single('aftersales_ctl_site_member',array('app'=>app::get('aftersales'),'arg1'=>false));
        $obj_order = kernel::single("b2c_mdl_orders");
        $obj_return_policy = kernel::single('aftersales_data_return_policy');
       
        $obj_product = kernel::single("aftersales_mdl_return_product");
        $return_products = $obj_product->getList('*',array('return_id'=>$return_id));
        
        if($return_products[0]['status'] != '1'){
            //$this->splash('failed',$url,app::get('aftersales')->_('非法请求'));
        }
        $sdf = array(
            'return_id' => $return_id,
            'status' => '3',
        );
        
        $this->pagedata['return_status'] = $obj_return_policy->change_status($sdf);        
        if ($this->pagedata['return_status'])
            $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
        
        $obj_aftersales = kernel::servicelist("api.aftersales.request");
        foreach ($obj_aftersales as $obj_request)
        {
            $obj_request->send_update_request($sdf);
        }
        //生成退款单
        $sdf['order_id'] = $order_id;
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));

        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $sdf_order = $obj_order->dump($sdf['order_id'],'*',$subsdf);

        $sdf['money'] = $sdf_order['payed'];
        $sdf['return_score'] = $sdf_order['score_g']-$sdf_order['score_u'];

        $refunds = kernel::single("ectools_mdl_refunds");
        //$objOrder->op_id = $this->user->user_id;
        //$objOrder->op_name = $this->user->user_data['account']['name'];
        $sdf['op_id'] = 0;
        $sdf['op_name'] = 'auto';
        //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
        unset($sdf['inContent']);
        
        $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");
        $sdf['payment'] = ($sdf['payment']) ? $sdf['payment'] : $sdf_order['payinfo']['pay_app_id'];

        $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
            
        $time = time();
        $sdf['refund_id'] = $refund_id = $refunds->gen_id();
        $sdf['pay_app_id'] = $sdf['payment'];
        $sdf['member_id'] = $sdf_order['member_id'] ? $sdf_order['member_id'] : 0;

        $obj_members = kernel::single("pam_mdl_account");
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
        //$sdf['op_id'] = $this->user->user_id;
        //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
        $sdf['status'] = 'ready';
        $sdf['app_name'] = $arrPaymentInfo['app_name'];
        $sdf['app_version'] = $arrPaymentInfo['app_version'];
        $sdf['refund_type'] = '1';
        $sdf['is_safeguard'] = $return_products[0]['is_safeguard'];
        if ($obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
        {
            $obj_refunds = kernel::single("ectools_refund");
            if ($obj_refunds->generate($sdf, $controller, $msg))
            {
                //进行退款操作
                $refund = kernel::single("ectools_mdl_refunds");
                $refund_data = $refund->dump($refund_id,'*');
                $obj_bills = kernel::single("ectools_mdl_order_bills");
                $order_id = $obj_bills->getRow('rel_id',array('bill_id'=>$refund_id));
                $payment_id = $refund->get_payment($order_id['rel_id']);
                $obj_payment = kernel::single("ectools_mdl_payments");
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

                $is_refund_finished = false;
                $obj_refund_lists = kernel::servicelist("order.refund_finish");
                foreach ($obj_refund_lists as $order_refund_service_object)
                {                
                    $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                }
                
                if ($is_refund_finished)
                {
                    //开始确认收货时间
                    $confirm_time = $obj_order->getRow('confirm_time,score_g,score_u,member_id',array('order_id'=>$sdf['order_id']));
                    $time = $confirm_time['confirm_time'] + time();

                    $point_money_value = app::get('b2c')->getConf('site.point_money_value');

                    //修改会员的冻结积分
                    $point_obj = app::get('pointprofessional')->model('members');
                    $reduce_score = $confirm_time['score_g'];
                    $point_obj->reduce_obtained($confirm_time['member_id'],$reduce_score,$sdf['order_id']);

                    //修改订单状态
                    $refund_status = array('refund_status'=>'4','confirm_time'=>$time,'score_g'=>0);
                    $rs = $obj_order->update($refund_status,array('order_id'=>$sdf['order_id']));

                    //退还积分
                    $obj_members_point = kernel::service('b2c_member_point_add');
                    $obj_members_point->change_point($sdf_order['member_id'],intval($refund_data['return_score']), $msg, 'order_refund_use', 1, $sdf['order_id'],0, 'refund');

                    //添加退款日志

                    $log_text = "系统自动同意退款";
                    $result_log = "SUCCESS";

                    $returnLog = kernel::single("aftersales_mdl_return_log");
                    $sdf_return_log = array(
                        'order_id' => $sdf['order_id'],
                        'return_id' => $return_id,
                        'op_id' => '0',
                        'op_name' => 'auto',
                        'alttime' => time(),
                        'behavior' => 'agreereturn',
                        'result' => $result_log,
                        'role' => 'admin',
                        'log_text' => $log_text,
                    );

                    $log_id = $returnLog->save($sdf_return_log);
                }
                else
                {
                    //$this->splash('failed',$url,$msg);
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
                }
                
            }
            else
            {
                //$this->splash('failed',$url,$msg);
            }
        }
    }

    function order_do_agrees($return_id,$order_id){
        $controller = kernel::single('aftersales_ctl_site_member',array('app'=>app::get('aftersales'),'arg1'=>false));
        $rp = kernel::single("aftersales_mdl_return_product");
        $objOrder = kernel::single("b2c_mdl_orders");

        $returns = $rp->getRow('*',array('return_id'=>$return_id));
        $sdf['order_id'] = $order_id;
        //生成退款单
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));

        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $sdf_order = $objOrder->dump($returns['order_id'],'*',$subsdf);

        $sdf['money'] = $returns['amount'];
        //$sdf['return_score'] = $sdf_order['score_g']-$sdf_order['score_u'];

        $refunds = kernel::single("ectools_mdl_refunds");
        //$objOrder->op_id = $this->user->user_id;
        //$objOrder->op_name = $this->user->user_data['account']['name'];
        $sdf['op_id'] = 0;
        $sdf['op_name'] = 'auto';
        //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
        unset($sdf['inContent']);
        
        $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");
        $sdf['payment'] = ($sdf['payment']) ? $sdf['payment'] : $sdf_order['payinfo']['pay_app_id'];
        $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
            
        $time = time();
        $sdf['refund_id'] = $refund_id = $refunds->gen_id();
        $sdf['pay_app_id'] = $sdf['payment'];
        $sdf['member_id'] = $sdf_order['member_id'] ? $sdf_order['member_id'] : 0;

        $obj_members = kernel::single("pam_mdl_account");
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
        $sdf['op_id'] = 0;
        //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
        $sdf['status'] = 'ready';
        $sdf['app_name'] = $arrPaymentInfo['app_name'];
        $sdf['app_version'] = $arrPaymentInfo['app_version'];
        $sdf['refund_type'] = '1';
        $sdf['is_safeguard'] = $returns['is_safeguard'];
        //echo "<pre>";print_r($sdf);exit;
        if ($obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
        {
            $obj_refunds = kernel::single("ectools_refund");

            //开始确认收货时间
            $confirm_time = $objOrder->getRow('confirm_time,status,score_u,member_id',array('order_id'=>$returns['order_id']));
            if($confirm_time['status'] == 'active'){
                $rs_buyer = $obj_refunds->generate($sdf, $controller, $msg);
            }
            $time = $confirm_time['confirm_time'] + time();
            
            $refund_data = $refunds->dump($refund_id,'*');

            $score_u = $confirm_time['score_u']-$returns['return_score'];

            //修改订单状态
            if($confirm_time['status'] == 'active'){
                $refund_status = array('refund_status'=>'4','confirm_time'=>$time,'score_u'=>$score_u);
            }else{
                //修改订单状态
                if($returns['safeguard_require'] == '3' || $returns['safeguard_require'] == '4'){
                    $refund_status = array('refund_status'=>'4','score_u'=>$score_u);
                }else{
                    $refund_status = array('refund_status'=>'11','score_u'=>$score_u);
                }
            }
            $rs = $objOrder->update($refund_status,array('order_id'=>$returns['order_id']));

            //退还积分
            $obj_members_point = kernel::service('b2c_member_point_add');
            $obj_members_point->change_point($confirm_time['member_id'],intval($returns['return_score']), $msg, 'order_refund_use', 1, $returns['order_id'],0, 'refund');
            
            $obj_bills = kernel::single("ectools_mdl_order_bills");
            $order_id = $obj_bills->getRow('rel_id',array('bill_id'=>$refund_id));
            $payment_id = $refunds->get_payment($order_id['rel_id']);
            $obj_payment = kernel::single("ectools_mdl_payments");
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
                    $obj_refunds = kernel::single("ectools_refund");
                    if($refund_data['cur_money'] == 0){
                        $ref_rs = $obj_refunds->generate_after($sdf);
                    }else{
                        $refund_data['payment_info'] = $cur_money;
                        $result = $obj_refunds->dorefund($refund_data,$this);
                        $obj_refunds->callback($refund_data,$result);
                    }
                }

                //添加退款日志

                $log_text = "系统自动同意退款";
                $result = "SUCCESS";

                $returnLog = kernel::single("aftersales_mdl_return_log");
                $sdf_return_log = array(
                    'order_id' => $sdf['order_id'],
                    'return_id' => $return_id,
                    'op_id' => '0',
                    'op_name' => 'auto',
                    'alttime' => time(),
                    'behavior' => 'agreereturn',
                    'result' => $result,
                    'role' => 'admin',
                    'log_text' => $log_text,
                );

                $log_id = $returnLog->save($sdf_return_log);

                $obj_refund_lists = kernel::servicelist("order.refund_finish");
                foreach ($obj_refund_lists as $order_refund_service_object)
                {
                    $sdf['order_id'] = $returns['order_id'];
                    $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                }
                // 发送同步日志.
                $order_refund_service_object->send_request($sdf);

                //ajx crm
                $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                $req_arr['order_id']=$sdf['order_id'];
                $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');
                
                $obj_aftersales = kernel::servicelist("api.aftersales.request");
                foreach ($obj_aftersales as $obj_request)
                {
                    $obj_request->send_update_request($sdf);
                }

                //判断如果已经全部退款  则给积分（没有退还商品的情况）
                $order_data = $objOrder->getRow('pay_status',array('order_id'=>$sdf['order_id']));

                if($order_data['pay_status'] == '5'){
                    $objOrder->update(array('status'=>'finish'),array('order_id'=>$sdf['order_id']));
                }

                $obj_return_policy = kernel::single('aftersales_data_return_policy');

                $sdf_re = array(
                    'return_id' => $return_id,
                    'status' => '3',
                );
                
                $return_status = $obj_return_policy->change_status($sdf_re);
            }else{
                $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                    $req_arr['order_id']=$sdf['order_id'];
                    $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');


                    if($returns['safeguard_require'] == '3' || $returns['safeguard_require'] == '4'){
                    $re_sdf = array(
                        'return_id' => $return_id,
                        'status' => '4',
                    );
                    $obj_refund_lists = kernel::servicelist("order.refund_finish");
                    foreach ($obj_refund_lists as $order_refund_service_object)
                    {                
                        $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                    }
                }else{
                    $re_sdf = array(
                        'return_id' => $return_id,
                        'status' => '15',
                    );
                }
                $obj_return_policy = kernel::single('aftersales_data_return_policy');
                $this->pagedata['return_status'] = $obj_return_policy->change_status($re_sdf);        
                if ($this->pagedata['return_status'])
                    $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
                
                $obj_aftersales = kernel::servicelist("api.aftersales.request");
                foreach ($obj_aftersales as $obj_request)
                {
                    $obj_request->send_update_request($sdf);
                }

                //添加退款日志
                $log_text = "系统自动同意退款";
                $result = "SUCCESS";

                $returnLog = app::get('aftersales')->model("return_log");
                $sdf_return_log = array(
                    'order_id' => $sdf['order_id'],
                    'return_id' => $return_id,
                    'op_id' => 0,
                    'op_name' => 'auto',
                    'alttime' => time(),
                    'behavior' => 'agreereturn',
                    'result' => $result,
                    'role' => 'seller',
                    'log_text' => $log_text,
                );

                $log_id = $returnLog->save($sdf_return_log);

                $objOrderLog = kernel::single("b2c_mdl_order_log");

                $sdf_order_log = array(
                    'rel_id' => $sdf['order_id'],
                    'op_id' => 0,
                    'op_name' => 'auto',
                    'alttime' => time(),
                    'bill_type' => 'order',
                    'behavior' => 'refunds',
                    'result' => $result,
                    'log_text' => $log_text,
                );
                $log_id = $objOrderLog->save($sdf_order_log);
            }
        }
        //if ($this->pagedata['return_status'])
            //$this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];

        //$this->splash('success',$url,app::get('aftersales')->_('操作成功'));
    }
    
    function order_do_refund_atuo_cancel($return_id,$order_id){
        $rp = kernel::single("aftersales_mdl_return_product");
        $obj_order = kernel::single("b2c_mdl_orders");

        $rp->update(array('status'=>'10'),array('return_id'=>$return_id));
        //开始确认收货时间
        $confirm_time = $obj_order->getRow('confirm_time',array('order_id'=>$order_id));
        $time = $confirm_time['confirm_time'] + time();

        //查询订单状态
        $status = $obj_order->dump($order_id,'status');
        if($status['status'] == 'active'){
            $obj_order->update(array('refund_status'=>'2','confirm_time'=>$time),array('order_id'=>$order_id));
        }else{
            $obj_order->update(array('refund_status'=>'2'),array('order_id'=>$order_id));
        }

        $log_text = "系统自动撤销";
        $result_log = "SUCCESS";
        $returnLog = kernel::single("aftersales_mdl_return_log");
        $sdf_return_log = array(
            'order_id' => $order_id,
            'return_id' => $return_id,
            'op_id' => 0,
            'op_name' => 'auto',
            'alttime' => time(),
            'behavior' => 'cancel',
            'result' => $result_log,
            'role' => 'admin',
            'log_text' => $log_text,
        );

        $log_id = $returnLog->save($sdf_return_log);

        $objOrderLog = kernel::single("b2c_mdl_order_log");

        $sdf_order_log = array(
            'rel_id' => $order_id,
            'op_id' => 0,
            'op_name' => 'auto',
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'refunds',
            'result' => $result_log,
            'log_text' => $log_text,
        );
        $log_id = $objOrderLog->save($sdf_order_log);
    }

    function order_do_refund_agrees($return_id,$order_id){
        $controller = kernel::single('aftersales_ctl_site_member',array('app'=>app::get('aftersales'),'arg1'=>false));
        $rp = kernel::single("aftersales_mdl_return_product");
        $obj_order = kernel::single("b2c_mdl_orders");
        $refunds = kernel::single("ectools_mdl_refunds");
        $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");

        $system_money_decimals = app::get('b2c')->getConf('system.money.decimals');
        $system_money_operation_carryset = app::get('b2c')->getConf('system.money.operation.carryset');

        //$url = $this->gen_url(array('app' =>'aftersales','ctl'=>'site_member','act' =>'seller_returns'));
        $returns = $rp->getRow('*',array('return_id'=>$return_id));
        $obj_return_policy = kernel::single('aftersales_data_return_policy');

        $sdf = array(
            'return_id' => $return_id,
            'status' => '6',
        );

        $return_status = $obj_return_policy->change_status($sdf); 
        $obj_aftersales = kernel::servicelist("api.aftersales.request");
        foreach ($obj_aftersales as $obj_request)
        {
            $obj_request->send_update_request($sdf);
        }
        $sdf['order_id'] = $order_id;
        //判断是否是完结的订单
        $status = $obj_order->getRow('status,score_u',array('order_id'=>$order_id));
        if($status['status'] == 'active'){
            //生成退款单
            $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder')); 

            $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
            $sdf_order = $obj_order->dump($sdf['order_id'],'*',$subsdf);

            $sdf['money'] = $returns['amount'];
            $sdf['return_score'] = $sdf_order['score_g']-$sdf_order['score_u'];

            //$objOrder->op_id = $this->user->user_id;
            //$objOrder->op_name = $this->user->user_data['account']['name'];
            $sdf['op_id'] = 0;
            $sdf['op_name'] = 'auto';
            //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
            unset($sdf['inContent']);
            
            $sdf['payment'] = ($sdf['payment']) ? $sdf['payment'] : $sdf_order['payinfo']['pay_app_id'];

            $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                
            $time = time();
            $sdf['refund_id'] = $refund_id = $refunds->gen_id();
            $sdf['pay_app_id'] = $sdf['payment'];
            $sdf['member_id'] = $sdf_order['member_id'] ? $sdf_order['member_id'] : 0;

            $obj_members = kernel::single("pam_mdl_account");
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
            $sdf['op_id'] = 0;
            //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
            $sdf['status'] = 'ready';
            $sdf['app_name'] = $arrPaymentInfo['app_name'];
            $sdf['app_version'] = $arrPaymentInfo['app_version'];
            $sdf['refund_type'] = '1';
            $sdf['is_safeguard'] = $returns['is_safeguard'];
            if ($obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
            {
                $obj_refunds = kernel::single("ectools_refund");
                $rs_buyer = $obj_refunds->generate($sdf, $controller, $msg);
                //需要结算退款单

                $obj_bills = kernel::single("ectools_mdl_order_bills");
                $order_id = $obj_bills->getRow('rel_id',array('bill_id'=>$refund_id));
                $payment_id = $refunds->get_payment($order_id['rel_id']);
                $obj_payment = kernel::single("ectools_mdl_payments");
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
                    $obj_refunds = kernel::single("ectools_refund");
                    if($refund_data['cur_money'] == 0){
                        $ref_rs = $obj_refunds->generate_after($sdf);
                    }else{
                        $refund_data['payment_info'] = $cur_money;
                        $result = $obj_refunds->dorefund($refund_data,$this);
                        $obj_refunds->callback($refund_data,$result);
                    }
                }

                //添加退款日志

                $log_text = "系统自动同意退款";
                $result_log = "SUCCESS";

                $returnLog = kernel::single("aftersales_mdl_return_log");
                $sdf_return_log = array(
                    'order_id' => $sdf['order_id'],
                    'return_id' => $return_id,
                    'op_id' => '0',
                    'op_name' => 'auto',
                    'alttime' => time(),
                    'behavior' => 'agreereturn',
                    'result' => $result_log,
                    'role' => 'admin',
                    'log_text' => $log_text,
                );

                $log_id = $returnLog->save($sdf_return_log);

                $obj_refund_lists = kernel::servicelist("order.refund_finish");
                foreach ($obj_refund_lists as $order_refund_service_object)
                {                
                    $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                }

                //生成运费结算单
                if($returns['ship_cost'] > 0 || $returns['amount_seller']>0){
                    $freight_pro = app::get('b2c')->getConf('member.profit');
                    $math = kernel::single("ectools_math");
                    $profit = ($returns['ship_cost']+$returns['amount_seller'])*($freight_pro/100);
                    $sdf['profit'] = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);
                    $sdf['money'] = $returns['ship_cost']+$returns['amount_seller']-$sdf['profit'];
                    unset($sdf['return_score']);

                    //$objOrder->op_id = $this->user->user_id;
                    //$objOrder->op_name = $this->user->user_data['account']['name'];
                    //$sdf['op_id'] = $this->user->user_id;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];

                    $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                        
                    $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                    $sdf['member_id'] = $sdf_order['store_id'] ? $sdf_order['store_id'] : 0;
                    $sdf['cur_money'] = $sdf['money'];
                    //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                    $sdf['op_id'] = 0;
                    $sdf['op_name'] = 'auto';
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    $sdf['status'] = 'ready';
                    $sdf['app_name'] = $arrPaymentInfo['app_name'];
                    $sdf['app_version'] = $arrPaymentInfo['app_version'];
                    $sdf['refund_type'] = '2';
                    $obj_ys = app::get('business')->model('storemanger');
                    $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                    $sdf['account'] = $ys['company_name'];
                    $rs_seller = $obj_refunds->generate($sdf, $controller, $msg);
                    //需要结算结算单

                    $obj_order->update(array('status'=>'finish'),array('order_id'=>$sdf['order_id']));

                    $refund_data = $refunds->getRow('*',array('refund_id'=>$sdf['refund_id']));
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

                     if($result['0'] == "true"){
                         $obj_refunds = kernel::single("ectools_refund");
                         $ref_rs = $obj_refunds->generate_after($sdf);
                     }else{
                         $refunds->update(array('memo'=>$result['1']),array('refund_id'=>$sdf['refund_id']));
                     }
                }
             
                
                
                if ($is_refund_finished)
                {
                    //发送同步日志.
                    $order_refund_service_object->send_request($sdf);

                    //ajx crm
                    $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                    $req_arr['order_id']=$sdf['order_id'];
                    $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

                    //积分重新计算
                    $obj_items = kernel::single("b2c_mdl_order_items");
                    $items = $obj_items->getList('score,sendnum',array('order_id'=>$sdf['order_id']));
                    $score = 0;
                    foreach($items as $key=>$val){
                        $score = $score+$val['score']*$val['sendnum'];
                    }

                    //开始确认收货时间
                    $confirm_time = $obj_order->getRow('confirm_time,score_g,member_id,member_id',array('order_id'=>$sdf['order_id']));
                    $time = $confirm_time['confirm_time'] + time();
                    
                    //修改订单状态
                    $refund_status = array('refund_status'=>'4','confirm_time'=>$time,'score_g'=>$score);
                    $rs = $obj_order->update($refund_status,array('order_id'=>$sdf['order_id']));

                    //修改会员的冻结积分
                    $point_obj = kernel::single("pointprofessional_mdl_members");
                    $reduce_score = $confirm_time['score_g']-$score;
                    $point_obj->reduce_obtained($confirm_time['member_id'],$reduce_score,$sdf['order_id']);

                    //退还积分
                    $obj_members_point = kernel::service('b2c_member_point_add');
                    $obj_members_point->change_point($confirm_time['member_id'],intval($order_id['return_score']), $msg, 'order_refund_use', 1, $sdf['order_id'],0, 'refund');

                    //$this->splash('success',$url,app::get('aftersales')->_('退款成功'));
                }
                else
                {
                    //$this->splash('failed',$url,app::get('aftersales')->_('退款成功，发送日志失败'));
                }
            }
        }else{
            //售后申请流程        
            /*$is_refund_finished = false;
            $obj_refund_lists = kernel::servicelist("order.refund_finish");
            foreach ($obj_refund_lists as $order_refund_service_object)
            {                
                $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
            }

            //判断是否是完结的订单
            $order_id = $rp->getRow('order_id,return_score',array('return_id'=>$_POST['return_id']));
            $status = $obj_order->getRow('status,score_u',array('order_id'=>$order_id['order_id']));*/

            //添加退款日志

            $log_text = "卖家同意退款";
            $result = "SUCCESS";

            $returnLog = kernel::single("aftersales_mdl_return_log");
            $sdf_return_log = array(
                'order_id' => $order_id,
                'return_id' => $return_id,
                'op_id' => 0,
                'op_name' => 'auto',
                'alttime' => time(),
                'behavior' => 'agreereturn',
                'result' => $result,
                'role' => 'seller',
                'log_text' => $log_text,
            );

            $log_id = $returnLog->save($sdf_return_log);
            
            //if ($is_refund_finished)
            //{
                // 发送同步日志.
                //$order_refund_service_object->send_request($sdf);

                //ajx crm
                $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                $req_arr['order_id']=$order_id;
                $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');
                //开始确认收货时间
                $confirm_time = $obj_order->getRow('confirm_time',array('order_id'=>$order_id));
                $time = $confirm_time['confirm_time'] + time();
                
                //修改订单状态
                if($returns['safeguard_require'] == '3' || $returns['safeguard_require'] == '4'){
                    $refund_status = array('refund_status'=>'4','score_u'=>$score_u);
                    $status = array('status'=>'4','close_time'=>time());
                    $obj_refund_lists = kernel::servicelist("order.refund_finish");
                    foreach ($obj_refund_lists as $order_refund_service_object)
                    {                
                        $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
                    }
                }else{
                    $refund_status = array('refund_status'=>'11','score_u'=>$score_u);
                    $status = array('status'=>'15','close_time'=>time());
                    $objOrderLog = kernel::single("b2c_mdl_order_log");

                    $sdf_order_log = array(
                        'rel_id' => $order_id,
                        'op_id' => 0,
                        'op_name' => 'auto',
                        'alttime' => time(),
                        'bill_type' => 'order',
                        'behavior' => 'refunds',
                        'result' => $result,
                        'log_text' => $log_text,
                    );
                    $log_id = $objOrderLog->save($sdf_order_log);
                }
                $rs = $obj_order->update($refund_status,array('order_id'=>$order_id));
                $rs = $rp->update($status,array('return_id'=>$return_id));
                //$this->splash('success',$url,app::get('aftersales')->_('退款成功,等待卖家打款到平台！'));
            /*}
            else
            {
                $this->splash('failed',$url,app::get('aftersales')->_('退款成功，发送日志失败'));
            }*/
        }
    }

    function order_do_refund_pass($n_refund_pass,$order_id){
        $rp = kernel::single("aftersales_mdl_return_product");
        $objOrder = kernel::single("b2c_mdl_orders");
        $dly_add = kernel::single("business_mdl_dlyaddress");

        $returns = $rp->getRow('*',array('return_id'=>$n_refund_pass));
        $obj_return_policy = kernel::single('aftersales_data_return_policy');
        $dly_id = $dly_add->dump(array('store_id'=>$returns['store_id'],'refund'=>'true'));
        $close_time = time()+(app::get('b2c')->getConf('member.to_buyer_refund'))*86400;
        $sdf = array(
            'return_id' => $n_refund_pass,
            'status' => '3',
            'refund_address' => $dly_id['da_id'],
            'close_time'=>$close_time,
        );

        //修改订单状态
        $rs = $objOrder->getRow('score_u',array('order_id'=>$returns['order_id']));
        $score_u = $rs['score_u'] - $returns['return_score'];
        
        $return_status = $obj_return_policy->change_status($sdf);        
        //if ($this->pagedata['return_status'])
            //$this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
        
        $obj_aftersales = kernel::servicelist("api.aftersales.request");
        foreach ($obj_aftersales as $obj_request)
        {
            $obj_request->send_update_request($sdf);
        }

        //积分重新计算
        $obj_items = kernel::single("b2c_mdl_order_items");
        $items = $obj_items->getList('score,sendnum',array('order_id'=>$returns['order_id']));
        $score = 0;
        foreach($items as $key=>$val){
            $score = $score+$val['score']*$val['sendnum'];
        }
        $data = array('score_g'=>$score,'refund_status'=>'3','score_u'=>$score_u);
        $objOrder->update($data,array('order_id'=>$returns['order_id']));
        
        //添加退款日志

        $log_text = "系统自动同意申请";
        $result = "SUCCESS";

        $returnLog = kernel::single("aftersales_mdl_return_log");
        $sdf_return_log = array(
            'order_id' => $returns['order_id'],
            'return_id' => $n_refund_pass,
            'op_id' => '0',
            'op_name' => 'auto',
            'alttime' => time(),
            'behavior' => 'agreereturn',
            'result' => $result,
            'role' => 'admin',
            'log_text' => $log_text,
        );

        $log_id = $returnLog->save($sdf_return_log);

        $objOrderLog = kernel::single("b2c_mdl_order_log");

        $sdf_order_log = array(
            'rel_id' => $returns['order_id'],
            'op_id' => 0,
            'op_name' => 'auto',
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'refunds',
            'result' => $result,
            'log_text' => $log_text,
        );
        $log_id = $objOrderLog->save($sdf_order_log);
        //$this->splash('success',$url,app::get('aftersales')->_('操作成功'));
    }

    public function order_do_refund_cancel($return_id){
        $rp = kernel::single("aftersales_mdl_return_product");
        $order_id = $rp->dump(array('return_id'=>$return_id));
        $objOrder = kernel::single("b2c_mdl_orders");
        $data = array('status'=>'10');
        $rp->update($data,array('return_id'=>$return_id));
        $data_order = array('refund_status'=>'2');
        $comfin_time = $objOrder->dump(array('order_id'=>$order_id['order_id']));
        $confirm_time = time()+$comfin_time['confirm_time'];
        $data_order = array('refund_status'=>'2','confirm_time'=>$confirm_time);
        $objOrder->update($data_order,array('order_id'=>$order_id['order_id']));

        //添加退款日志

        $log_text = "系统自动取消退款申请";
        $result = "SUCCESS";

        $returnLog = kernel::single("aftersales_mdl_return_log");
        $sdf_return_log = array(
            'order_id' => $order_id['order_id'],
            'return_id' => $return_id,
            'op_id' => '0',
            'op_name' => 'auto',
            'alttime' => time(),
            'behavior' => 'agreereturn',
            'result' => $result,
            'role' => 'admin',
            'log_text' => $log_text,
        );

        $log_id = $returnLog->save($sdf_return_log);

        $objOrderLog = kernel::single("b2c_mdl_order_log");

        $sdf_order_log = array(
            'rel_id' => $order_id['order_id'],
            'op_id' => 0,
            'op_name' => 'auto',
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'refunds',
            'result' => $result,
            'log_text' => $log_text,
        );
        $log_id = $objOrderLog->save($sdf_order_log);
    }


    public function create_blance()
     {
        $refunds = kernel::single("ectools_mdl_refunds");
        $n_finishs = $refunds->get_orders_no_blance();
        $controller   = kernel::single('b2c_ctl_site_order');
        $point_money_value = app::get('b2c')->getConf('site.point_money_value');

        $system_money_decimals = app::get('b2c')->getConf('system.money.decimals');
        $system_money_operation_carryset = app::get('b2c')->getConf('system.money.operation.carryset');

        foreach($n_finishs as $k=>$order_id){
            $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
            if ($obj_checkorder->check_order_finish_create_blance($order_id['order_id'],'',$message))
            {
                $sdf['order_id'] = $order_id['order_id'];
                //$arrMember = $this->get_current_member();
                $sdf['op_id'] = '0';
                $sdf['opname'] = 'auto';
                $sdf['confirm_time'] = time();
                
                //生成结算单
                $obj_order = kernel::single("b2c_mdl_orders");
                $money = $obj_order->getRow('payed,pmt_order,cost_freight,is_protect,cost_protect,cost_payment,member_id,ship_status,score_u,score_g,discount_value',array('order_id'=>$order_id['order_id']));
                $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
                if ($obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
                {
                     //$this->end(false, $message);
                }

                $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
                $sdf_order = $obj_order->dump($sdf['order_id'],'*',$subsdf);

                //$objOrder->op_id = $this->user->user_id;
                //$objOrder->op_name = $this->user->user_data['account']['name'];
                //$sdf['op_id'] = $this->user->user_id;
                //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                unset($sdf['inContent']);
                
                $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");
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
                
                $return_product_obj = kernel::single("aftersales_mdl_return_product");
                $returns = $return_product_obj->getList('amount',array('order_id'=>$sdf['order_id'],'refund_type|in'=>array('3','4'),'status'=>'3'));
                if($returns[0]['amount']){
                    if($money['is_protect']){
                        $cost_freight = $money['cost_freight']+$money['cost_payment']+$money['cost_protect']-$returns[0]['amount'];
                    }else{
                        $cost_freight = $money['cost_freight']+$money['cost_payment']-$returns[0]['amount'];
                    }
                    if($money['discount_value'] > 0){
                        $total_money = ($money['payed'])+$money['pmt_order']-$cost_freight+($money['discount_value']);
                    }else{
                        $total_money = ($money['payed'])+$money['pmt_order']-$cost_freight;
                    }
                    $obj_items = kernel::single("b2c_mdl_order_items");
                    $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));
                    //退款金额小于运费
                    if($cost_freight > 0){
                        $profit = 0;
                        foreach($items as $k=>$v){
                            $obj_cat = kernel::single("b2c_mdl_goods_cat");
                            $obj_goods = kernel::single("b2c_mdl_goods");
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
                            if($total_money>0){
                                $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                            }else{
                                $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum'];
                            }
                        }
                        $freight_pro = app::get('b2c')->getConf('member.profit');
                        $profit = $profit + $cost_freight*($freight_pro/100);
                    }else{
                        $freight_pro = app::get('b2c')->getConf('member.profit');

                        $total_money = ($money['payed']+($money['discount_value']))*($freight_pro/100);
                    }

                    //计算系统价格 
                    $math = kernel::single("ectools_math");
                    $profit = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);
                        
                    $sdf['money'] = ($money['payed']-($money['score_g'])/$point_money_value)-$profit;
                    $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                    //end
                    unset($sdf['return_score']);

                    $refunds = kernel::single("ectools_mdl_refunds");
                    //$objOrder->op_id = $this->user->user_id;
                    //$objOrder->op_name = $this->user->user_data['account']['name'];
                    //$sdf['op_id'] = $this->user->user_id;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    
                    $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");

                    $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                        
                    $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                    $sdf['cur_money'] = $sdf['money'];
                    //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                    $sdf['op_id'] = 0;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    $sdf['status'] = 'ready';
                    $sdf['app_name'] = $arrPaymentInfo['app_name'];
                    $sdf['app_version'] = $arrPaymentInfo['app_version'];
                    $sdf['refund_type'] = '2';
                    $obj_ys = kernel::single("business_mdl_storemanger");
                    $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                    $sdf['account'] = $ys['company_name'];
                    $sdf['profit'] = $profit;
                    $obj_refunds = kernel::single("ectools_refund");
                    if ($obj_checkorder->check_order_finish_create_blance($order_id['order_id'],'',$message)){
                        $rs_seller = $obj_refunds->generate($sdf, $controller, $msg);
                        // 增加经验值
                        $obj_member = kernel::single("b2c_mdl_members");
                        $obj_member->change_exp($money['member_id'], floor($total_money));
                    }
                }elseif($money['ship_status'] == '3'){
                    //部分退款的确认收货
                    $obj_items = kernel::single("b2c_mdl_order_items");
                    $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));
                    
                    $payed = 0;
                    foreach($items as $k=>$v){
                        $payed = $payed+$v['price']*$v['sendnum'];
                    }
                    $payed = $payed - $money['pmt_order'];
                    //剩余可打金额
                    $return_product_obj = kernel::single("aftersales_mdl_return_product");
                    $amount = $return_product_obj->getRow('amount',array('order_id'=>$sdf['order_id'],'status'=>'6'));

                    $money_useful = ($money['payed'])+($money['discount_value']);
                    //剩余杂费
                    $cost_freight = $money_useful - $payed;

                    $total_money = $payed+$money['pmt_order'];

                    $profit = 0;
                    foreach($items as $k=>$v){
                        $obj_cat = kernel::single("b2c_mdl_goods_cat");
                        $obj_goods = kernel::single("b2c_mdl_goods");
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
                        if($total_money>0){
                            $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                        }else{
                            $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum'];
                        }
                    }
                    $freight_pro = app::get('b2c')->getConf('member.profit');
                    $profit = $profit + $cost_freight*($freight_pro/100);

                    //计算系统价格 
                    $math = kernel::single("ectools_math");
                    $profit = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);

                    $sdf['money'] = $money_useful-$profit-($money['score_g'])/$point_money_value;
                    $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                    //end

                    unset($sdf['return_score']);

                    $refunds = kernel::single("ectools_mdl_refunds");
                    //$objOrder->op_id = $this->user->user_id;
                    //$objOrder->op_name = $this->user->user_data['account']['name'];
                    //$sdf['op_id'] = $this->user->user_id;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    
                    $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");

                    $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                        
                    $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                    $sdf['cur_money'] = $sdf['money'];
                    //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                    $sdf['op_id'] = 0;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    $sdf['status'] = 'ready';
                    $sdf['app_name'] = $arrPaymentInfo['app_name'];
                    $sdf['app_version'] = $arrPaymentInfo['app_version'];
                    $sdf['refund_type'] = '2';

                    $obj_ys = kernel::single("business_mdl_storemanger");
                    $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                    $sdf['account'] = $ys['company_name'];
                    $sdf['profit'] = $profit;
                    $obj_refunds = kernel::single("ectools_refund");
                    if ($obj_checkorder->check_order_finish_create_blance($order_id['order_id'],'',$message)){
                        $rs_seller = $obj_refunds->generate($sdf, $controller, $msg);

                        // 增加经验值
                        $obj_member = kernel::single("b2c_mdl_members");
                        $obj_member->change_exp($money['member_id'], floor($total_money));
                    }
                }else{
                    //进行提成计算（正常流程）
                    if($money['is_protect']){
                        $cost_freight = $money['cost_freight']+$money['cost_payment']+$money['cost_protect'];
                    }else{
                        $cost_freight = $money['cost_freight']+$money['cost_payment'];
                    }
                    $total_money = $money['payed']+$money['pmt_order']-$cost_freight+($money['discount_value']);
                    $obj_items = kernel::single("b2c_mdl_order_items");
                    $items = $obj_items->getList('*',array('order_id'=>$sdf['order_id']));

                    $profit = 0;
                    foreach($items as $k=>$v){
                        $obj_cat = kernel::single("b2c_mdl_goods_cat");
                        $obj_goods = kernel::single("b2c_mdl_goods");
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
                        if($total_money>0){
                            $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum']*(1-($money['pmt_order']/$total_money));
                        }else{
                            $profit = $profit + ($profit_point['profit_point']/100)*$v['price']*$v['sendnum'];
                        }
                    }
                    $freight_pro = app::get('b2c')->getConf('member.profit');
                    $profit = $profit + $cost_freight*($freight_pro/100);
                    
                    //计算系统价格 
                    $math = kernel::single("ectools_math");
                    $profit = $math->formatNumber($profit, $system_money_decimals, $system_money_operation_carryset);

                    $sdf['money'] = $money['payed']+($money['discount_value'])-$profit-($money['score_g'])/$point_money_value;
                    $sdf['score_cost'] = ($money['score_g'])/$point_money_value;
                    //end

                    unset($sdf['return_score']);

                    $refunds = kernel::single("ectools_mdl_refunds");
                    //$objOrder->op_id = $this->user->user_id;
                    //$objOrder->op_name = $this->user->user_data['account']['name'];
                    //$sdf['op_id'] = $this->user->user_id;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    
                    $objPaymemtcfg = kernel::single("ectools_mdl_payment_cfgs");

                    $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
                        
                    $sdf['refund_id'] = $refund_id = $refunds->gen_id();
                    $sdf['cur_money'] = $sdf['money'];
                    //$sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
                    $sdf['op_id'] = 0;
                    //$sdf['op_name'] = $this->user->user_data['account']['login_name'];
                    $sdf['status'] = 'ready';
                    $sdf['app_name'] = $arrPaymentInfo['app_name'];
                    $sdf['app_version'] = $arrPaymentInfo['app_version'];
                    $sdf['refund_type'] = '2';
                    $obj_ys = kernel::single("business_mdl_storemanger");
                    $ys = $obj_ys->getRow('*',array('store_id'=>$sdf['member_id']));
                    $sdf['account'] = $ys['company_name'];
                    $sdf['profit'] = $profit;
                    $obj_refunds = kernel::single("ectools_refund");
                    if ($obj_checkorder->check_order_finish_create_blance($order_id['order_id'],'',$message)){
                        $rs_seller = $obj_refunds->generate($sdf, $controller, $msg);

                        // 增加经验值
                        $obj_member = kernel::single("b2c_mdl_members");
                        $obj_member->change_exp($money['member_id'], floor($total_money));
                    }
                }
                //将款项打给卖家
                if($rs_seller){
                    $refund = kernel::single("ectools_mdl_refunds");
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
                                    //$this->end(true, '确认收货成功！');

                                }else{
                                    //$this->end(true, '确认收货成功！');
                                }
                            }else{
                                $refund->update(array('memo'=>$result['1']),array('refund_id'=>$refund_id));
                                //$this->end(true, '确认收货成功！'.$result['1']);
                            }
                        }else{
                            $obj_refunds = kernel::single("ectools_refund");
                            $ref_rs = $obj_refunds->generate_after(array('refund_id'=>$refund_id,'refund_type'=>'2'));
               
                            if ($ref_rs)
                            {
                                //$this->end(true, '确认收货成功！');

                            }else{
                                //$this->end(false, '确认收货成功！更新结算单状态失败！');
                            }
                        }
                    }else{
                        //$this->end(false, '结算单类型错误！');
                    }
                }
            }
        }
    }
}

