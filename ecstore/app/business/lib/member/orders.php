<?php

class business_member_orders extends site_controller
{
    /**
     * 构造方法
     * @param object app
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
	
    //查询个订单的权限
    public function get_orders_html($v){
        $html = '';
        
        $pay_status=$v['pay_status'];
        $pay_app_id=$v['payinfo']['pay_app_id'];
        $ship_status=$v['ship_status'];
        $order_id=$v['order_id'];
        $confirm_time=$v['confirm_time'];
        $pay_time=$v['pay_time'];
        $need_send=$v['need_send'];
        $refund_status=$v['refund_status'];
        $is_extend=$v['is_extend'];
        $status=$v['status'];
        $need_edit=$v['need_edit'];
        $order_kind=$v['order_kind'];
        $is_aftersales_open = app::get('aftersales')->getConf('site.is_open_return_product');
        if ($order_kind == '3rdparty') {
            foreach(kernel::servicelist('3rdparty_goods_processor') as $processor) {
                if (($processor->goodsKindDetail() == $v['order_kind_detail']) && $processor->isCustom('order')) {
                    return $processor->orderHtml(array('order'=>$v, 'from'=>'buyer'));
                }
            }
        }

        if($status == 'active' && ( $pay_status == '0' || $pay_status == '3') && $pay_app_id != '-1' && $ship_status == '0'){

            $url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_order', 'act' => 'docancel', 'arg0' => $order_id , 'arg1' => 'buyer'));
            $html = $html.
            "<a href='javascript:cancel(".$order_id.")' class='font-blue operate-btn'>关闭交易</a>";
        }
        if(($ship_status == '1' || $ship_status == '3') && ($pay_status == '1' || $pay_status == '4') && $status == 'active' && ($refund_status == '0' || $refund_status == '2' || $refund_status == '4' || $refund_status == '10')){
            $url = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'dofinish', 'arg0' => $order_id , 'arg1' => 'buyer'));
            $url1 = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'extend_finish_apl', 'arg0' => $order_id , 'arg1' => 'buyer'));
            /*$mdl_return_product = app::get('aftersales')->model('return_product');
            $return_id = $mdl_return_product->getList('return_id',array('order_id'=>$order_id,'status'=>'1','refund_type'=>'2'));
            $return_ids = $mdl_return_product->getList('return_id',array('order_id'=>$order_id,'status'=>'1','refund_type'=>'3'));
            if(!isset($return_id[0]) && !isset($return_ids[0])){*/
                $html = $html."
                <a href=".$url." class='paymoney_btn operate-btn'>确认收货</a>";
            //}
        }
        if(($ship_status == '1' || $ship_status == '3') && ($pay_status == '1' || $pay_status == '4') && $status == 'active' && ($refund_status == '0' || $refund_status == '2' || $refund_status == '4' || $refund_status == '10') && $is_extend == '0'){
            $url = $this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'extend_finish_apl', 'arg0' => $order_id , 'arg1' => 'buyer'));
            /*$mdl_return_product = app::get('aftersales')->model('return_product');
            $return_id = $mdl_return_product->getList('return_id',array('order_id'=>$order_id,'status'=>'1','refund_type'=>'2'));
            $return_ids = $mdl_return_product->getList('return_id',array('order_id'=>$order_id,'status'=>'1','refund_type'=>'3'));
            if(!isset($return_id[0]) && !isset($return_ids[0])){*/
                $html = $html."
                <a href=".$url." class='font-blue operate-btn'>延长收货申请</a>";
            //}
        }
        if($pay_status != '0' && $pay_status != '5' && $status == 'active' && $ship_status == '0'  && ($refund_status == '0' || $refund_status == '2' || $refund_status == '4' || $refund_status == '10') && $order_kind == 'virtual'){
            $url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund', 'arg0' => $order_id , 'arg1' => 'buyer'));
            //$time = $confirm_time - (app::get('b2c')->getConf('member.to_finish'))*86400 + 86400;
            $paytime = $pay_time + (app::get('b2c')->getConf('member.to_refund'))*86400;
            
            if($is_aftersales_open && (time() > $paytime)){
                $html = $html."
                <a href=".$url." class='font-blue operate-btn'>申请退款</a>";
            }
            $html = $html."<a href='javascript:remind(".$order_id.")' class='font-blue operate-btn'>提醒卖家发货</a>";
            
            
        }
        if($is_aftersales_open && $status == 'active' && ($ship_status == '1' || $ship_status == '3') && ($refund_status == '0' || $refund_status == '2' || $refund_status == '4' || $refund_status == '10') && $pay_status != '0' && $pay_status != '5' && $order_kind == 'virtual'){
            //$url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add_before', 'arg0' => $order_id , 'arg1' => 'buyer'));
            $url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'gorefund_select', 'arg0' => $order_id , 'arg1' => 'buyer'));
            $html = $html."
            <a href=".$url." class='font-blue operate-btn'>退货/退款</a>";
        }
        if($is_aftersales_open && $status == 'finish' && ($ship_status == '1' || $ship_status == '3') && ($refund_status == '0' || $refund_status == '2' || $refund_status == '4' || $refund_status == '10') && $pay_status != '0' && $pay_status != '5' && $order_kind == 'virtual'){
            $url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'safeguard', 'arg0' => $order_id , 'arg1' => 'buyer'));
            $time = $confirm_time + (app::get('b2c')->getConf('member.to_buyer_slr'))*86400;
            if($time > time()){
                $html = $html."
                <a href=".$url." class='font-blue operate-btn'>申请维权</a>";
            }
        }
        if($is_aftersales_open && $need_send == '1' && $refund_status == '3'){
            $url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'refund_add_buyer', 'arg0' => $order_id));
            $html = $html."
            <a href=".$url." class='font-blue operate-btn'>填写退货信息</a>";
        }

        if($is_aftersales_open && $refund_status == '7'){
            $url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'edit_refund_app', 'arg0' => $order_id));
            $html = $html."
            <a href=".$url." class='font-blue operate-btn'>修改退货申请</a>";
        }

        if($is_aftersales_open && $refund_status == '6' && ($need_edit == '1' || $status == 'finish')){
            $url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'edit_refund_2', 'arg0' => $order_id));
            $html = $html."
            <a href=".$url." class='font-blue operate-btn'>修改退货申请</a>";
        }

        if($is_aftersales_open && $refund_status == '6' && $need_send == '0' && $need_edit == '0' && $status == 'active'){
            $url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'edit_refund', 'arg0' => $order_id));
            $html = $html."
            <a href=".$url." class='font-blue operate-btn'>修改退货申请</a>";
        }

       
        if($status == 'finish'){
            $objOrders = app::get('b2c')->model('orders');
            $order_info = $objOrders->getList('order_id,createtime,comments_count',array('order_id'=>$order_id,'status'=>'finish'));
            foreach($order_info as $rows){
                $day_1 = app::get('b2c')->getConf('site.comment_original_time');
                $day_2 = app::get('b2c')->getConf('site.comment_additional_time');
                $day_1 = intval($day_1)?intval($day_1):30;
                $day_2 = intval($day_2)?intval($day_2):90;
                if(intval($rows['comments_count']) > 1 || intval($rows['createtime']) < strtotime("-{$day_2} day")) continue;
                if(intval($rows['comments_count']) == 0 && intval($rows['createtime']) < strtotime("-{$day_1} day")) continue;
                if(intval($rows['comments_count']) == 0 && intval($rows['createtime']) >= strtotime("-{$day_1} day")){
                    $url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_comment', 'act' => 'discuss', 'arg0' => $order_id));
                    $html .= "<a href=".$url." class='font-blue operate-btn'>订单评论</a>";
                }else{
                    $url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_comment', 'act' => 'addition', 'arg0' => $order_id));
                    $html .= "<a href=".$url." class='font-blue operate-btn'>追加评论</a>";
                }
            }
        }
       
       return $html;
    }

    public function get_type(){
        return array('orders-extend'=>array('label'=>app::get('b2c')->_('申请延长收货时'),'level'=>9,'varmap'=>app::get('b2c')->_('登录名').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('订单号').'&nbsp;<{$order_id}>&nbsp;&nbsp;&nbsp;&nbsp;'));
    }
    
 
    public function reset_selllog(){
        $objLog = app::get('b2c')->model('sell_logs');
        $objGoods = app::get('b2c')->model('goods');
        $sql = "delete from sdb_b2c_sell_logs";
        $flag = $objLog->db->exec($sql);
        $sql = "insert into sdb_b2c_sell_logs (member_id,name,price,goods_id,product_id,product_name,spec_info,number,createtime) 
                select ifnull(o.member_id,0),ifnull(m.login_name,o.ship_email),i.price,p.goods_id,i.product_id,p.name,p.spec_info,i.nums,l.alttime 
                from sdb_b2c_order_log as l join sdb_b2c_orders as o on l.rel_id=o.order_id left join sdb_pam_account as m on o.member_id=m.account_id 
                join sdb_b2c_order_items as i on o.order_id=i.order_id left join sdb_b2c_products as p on i.product_id=p.product_id 
                where l.behavior='payments' and l.result='SUCCESS' and p.goods_id is not null";
        $flag = $objLog->db->exec($sql);
        $goods = array();
        $dayNum = $objGoods->day(time());
        foreach((array)$objLog->getList('goods_id,number,createtime',null,0,-1,'createtime asc') as $items){
            $goods[$items['goods_id']]['buy_count'] = intval($goods[$items['goods_id']]['buy_count'])+intval($items['number']);
            $day = floor(intval($items['createtime'])/86400);
            if($dayNum > $day+30){
                continue;
            }
            $goods[$items['goods_id']]['mbuy'][$day] = intval($goods[$items['goods_id']]['mbuy'][$day])+intval($items['number']);
            if($dayNum > $day+7){
                continue;
            }
            $goods[$items['goods_id']]['buy'][$day] = intval($goods[$items['goods_id']]['buy'][$day])+intval($items['number']);
        }
        $goods_id = array_keys($goods);
        if($goods_id)
        foreach((array)$objGoods->getList('goods_id,count_stat',array('goods_id|in'=>$goods_id),0,-1) as $rows){
            $items = $goods[$rows['goods_id']];
            $rows['count_stat'] = unserialize($rows['count_stat']);
            $rows['count_stat']['buy'] = (array)$items['buy'];
            $rows['count_stat']['mbuy'] = (array)$items['mbuy'];
            $sql = "update sdb_b2c_goods set buy_count=".intval($items['buy_count']).",buy_m_count=".intval(array_sum((array)$items['mbuy'])).",buy_w_count=".intval(array_sum((array)$items['buy'])).",count_stat='".serialize($rows['count_stat'])."' where goods_id=".intval($rows['goods_id']);
            $flag = $objLog->db->exec($sql);
        }
        $sql = "update sdb_business_storemanger as s inner join 
               (select sum(buy_m_count) as _count,store_id from sdb_b2c_goods group by store_id) as c on s.store_id=c.store_id 
                set s.buy_m_count=c._count ";
        $flag = $objLog->db->exec($sql);
        echo '成功';
    }
   
    public function get_orders_status_html($v, $from='buyer'){
        $html = '';
        
        $pay_status=$v['pay_status'];
        $ship_status=$v['ship_status'];
        $refund_status=$v['refund_status'];
        $status=$v['status'];
        if ($v['order_kind'] == '3rdparty') {
            foreach(kernel::servicelist('3rdparty_goods_processor') as $processor) {
                if (($processor->goodsKindDetail() == $v['order_kind_detail']) && $processor->isCustom('order')) {
                    return $processor->orderStatusHtml(array('order'=>$v, 'from'=>$from));
                }
            }
        }
        
        if ($from == 'buyer' || $from == 'seller') { // 订单列表
            if($refund_status == '0' || $refund_status == '2' || $refund_status == '4' || $refund_status == '10'){
                if ($status == 'finish') {
                    $html = '交易完成';
                } elseif ($status == 'dead') {
                    $html = '交易关闭';
                } else {
                    if ($pay_status == '1') {
                        $html = '已付款 ';
                    } elseif ($pay_status == '2') {
                        $html = '已付款至担保方 ';
                    } elseif ($pay_status == '3') {
                        $html = '待补款 ';
                    } elseif ($pay_status == '4') {
                        $html = '部分退款 ';
                    } elseif ($pay_status == '5') {
                        $html = '已退款 ';
                    } else {
                        $html = '待付款 ';
                    }
                    if ($ship_status == '1') {
                        $html .= '[已发货]';
                    } elseif ($ship_status == '2') {
                        $html .= '[部分发货]';
                    } elseif ($ship_status == '3') {
                        $html .= '[部分退货]';
                    } elseif ($ship_status == '4') {
                        $html .= '[已退货]';
                    } else {
                        if ($pay_status == '1' || $pay_status == '2') {
                            $html .= '[正在备货...]';
                        } elseif ($pay_status == '4' || $pay_status == '5') {
                            $html .= '[未发货]';
                        }
                    }
                }
            } elseif($refund_status == '1') {
                $html = '退款申请中,等待卖家审核';
            } elseif($refund_status == '3') {
                $html = '卖家同意退款,等待买家退货';
            } elseif($refund_status == '5') {
                $html = '买家已退货,等待卖家确认收货';
            } elseif($refund_status == '6') {
                $html = '卖家不同意协议,等待买家修改';
            } elseif($refund_status == '7') {
                $html = '买家已退货,卖家不同意协议,等待买家修改';
            } elseif($refund_status == '8') {
                $html = '平台介入,等待卖家举证';
            } elseif($refund_status == '9') {
                $html = '平台介入,等待平台处理';
            } elseif($refund_status == '11') {
                $html = '卖家同意退款,等待卖家打款至平台';
            } elseif($refund_status == '12') {
                $html = '卖家已退款,等待系统结算';
            }
        } elseif ($from == 'buyerdetail' || $from == 'sellerdetail') { // 买家订单详情
            if ($status == 'finish') {
                $html = '交易完成';
            } elseif ($status == 'dead') {
                $html = '交易关闭';
            } else {
                if ($pay_status == '1') {
                    $html = '已支付 ';
                } elseif ($pay_status == '2') {
                    $html = '已付款至担保方 ';
                } elseif ($pay_status == '3') {
                    $html = '部分付款 ';
                } elseif ($pay_status == '4') {
                    $html = '部分退款 ';
                } elseif ($pay_status == '5') {
                    $html = '全额退款 ';
                } else {
                    $html = '未付款 ';
                }
                if ($ship_status == '1') {
                    $html .= '已发货';
                } elseif ($ship_status == '2') {
                    $html .= '部分发货';
                } elseif ($ship_status == '3') {
                    $html .= '部分退货';
                } elseif ($ship_status == '4') {
                    $html .= '已退货';
                } else {
                    $html .= '[未发货]';
                }
            }
        }
        return $html;
    }
    
    public function get_seller_orders_html($v){
        $html = '';
        
        $pay_status=$v['pay_status'];
        $pay_app_id=$v['payinfo']['pay_app_id'];
        $ship_status=$v['ship_status'];
        $order_id=$v['order_id'];
        $refund_status=$v['refund_status'];
        $status=$v['status'];
        $is_cancel=$v['is_cancel'];
        $is_return=$v['is_return'];
        $is_returns=$v['is_returns'];
        $no_need_returns=$v['no_need_returns'];
        $return_id=$v['return_id'];
        $order_kind=$v['order_kind'];
        if ($order_kind == '3rdparty') {
            foreach(kernel::servicelist('3rdparty_goods_processor') as $processor) {
                if (($processor->goodsKindDetail() == $v['order_kind_detail']) && $processor->isCustom('order')) {
                    return $processor->orderHtml(array('order'=>$v, 'from'=>'seller'));
                }
            }
        }

        if($status == 'active' && ( $pay_status == '0' || $pay_status == '3') && $pay_app_id != '-1'){
            $html = $html.
            "<a href='javascript:cancel(".$order_id.")' class='font-blue operate-btn'>取消订单</a>";
        }
        if($pay_status == '1' && ($ship_status == '0' || $ship_status == '2')){
            $url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_order', 'act' => 'godelivery', 'arg0' => $order_id));
            $html = $html.
            "<a href=".$url." class='font-blue operate-btn'>发货</a>";
        }
        if($ship_status == '1' && $status != 'finish' && ($refund_status == '0' || $refund_status == '2' || $refund_status == '4' || $refund_status == '10') && $pay_status != '5'){
            $html = $html.
            "<a href='javascript:extend_confirm(".$order_id.")' class='font-blue operate-btn'>延长收货时间</a>";
        }
        if($status == 'active' && ($pay_status == '0' || $pay_status == '3') && $pay_app_id != '-1'){
            $html = $html.
            "<a href='javascript:change_price(".$order_id.")' class='font-blue operate-btn'>修改价格</a>";
        }
        if($is_cancel == '1' && $status != 'finish'){
            $url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'do_gorefund', 'arg0' => $order_id));
            $html = $html.
            "<a href=".$url." class='font-blue operate-btn'>处理订单申请</a>";
        }
        if($is_return == '1'){
            $url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'do_return', 'arg0' => $order_id));
            $html = $html.
            "<a href=".$url." class='font-blue operate-btn'>退货/退款</a>";
        }
        if($is_returns == '1'){
            $url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'do_return', 'arg0' => $order_id, 'arg1' => 3));
            $html = $html.
            "<a href=".$url." class='font-blue operate-btn'>退货/退款</a>";
        }
        if($no_need_returns == '1'){
            $url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'do_return', 'arg0' => $order_id, 'arg1' => 4));
            $html = $html.
            "<a href=".$url." class='font-blue operate-btn'>退货/退款</a>";
        }
        if($refund_status == '5' && $return_id){
            $url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'return_details', 'arg0' => $return_id));
            $html = $html.
            "<a href=".$url." class='font-blue operate-btn'>确认收到退货</a>";
        }
        if($refund_status == '8' && $return_id){
            $url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'seller_update', 'arg0' => $return_id));
            $html = $html.
            "<a href=".$url." class='font-blue operate-btn'>举证</a>";
        }
        if($refund_status == '11' && $return_id){
            $url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'upload_money', 'arg0' => $return_id));
            $html = $html.
            "<a href=".$url." class='font-blue operate-btn'>上传打款凭证</a>";
        }
        return $html;
    }
}