<?php

 
class b2c_desktop_widgets_workcount implements desktop_interface_widget{
    
    var $order = 1;
    function __construct($app){
        $this->app = $app; 
        $this->render =  new base_render(app::get('b2c'));  
    }
    
    function get_title(){
            
        return app::get('b2c')->_("待处理事项");
        
    }
    function get_html(){

        $render = $this->render;
        $mdl_order = $this->app->model('orders');        
        $member_comments = kernel::single('b2c_message_order');

        $b2c_message_disask = kernel::single('b2c_message_disask');
        $filter = array('pay_status'=>array('0'),'ship_status'=>array('0'),'status'=>'active');
        $order_new = $mdl_order->count($filter);
        $render->pagedata['order_new'] = $order_new;

        $filter = array('pay_status'=>array('1','2','3'),'ship_status'=>array('0','2'),'status'=>'active');
        $need_send = $mdl_order->count($filter);
        $render->pagedata['need_send'] = $need_send;

        $filter = array('adm_read_status'=>'false','object_type'=>'order');
        $new_order_msg = $member_comments->count($filter);
        $render->pagedata['new_order_msg'] = $new_order_msg;

        $filter = array('adm_read_status'=>'false','object_type'=>'ask');
        $new_gask = $b2c_message_disask->count($filter);
        $render->pagedata['new_gask'] = intval($new_gask);

        //新留言
        $filter = array('adm_read_status'=>'false','for_comment_id'=>'0','object_type'=>'message');
		$oMsg = kernel::single("b2c_message_order");
        $new_leave_message = $oMsg->count($filter);
        $render->pagedata['new_leave_message'] = intval($new_leave_message);

        //新消息
        $filter = array('adm_read_status'=>'false');
        $member_comments = $this->app->model('member_comments');
        $member_comments->set_type('msgtoadmin');
        $new_message = $member_comments->count($filter);
        $render->pagedata['new_message'] = intval($new_message);


        //新评论
        $filter = array('adm_read_status'=>'false');
        $member_comments = $this->app->model('member_comments');
        $member_comments->set_type('discuss');
        $new_discuss = $member_comments->count($filter);
        $render->pagedata['new_discuss'] = intval($new_discuss);
        
        //库存警报 影响后台登陆加载速度慢 
        $alert_num = $this->app->getConf('system.product.alert.num');
        $mdl_products = $this->app->model('products');
        $alert_num_count = $mdl_products->db->select("select count(DISTINCT goods_id) as g_count from sdb_b2c_products where goods_type='normal' and store<='".$alert_num."'");
        $render->pagedata['alert_num_count'] = $alert_num_count[0]['g_count'] > 0 ? $alert_num_count[0]['g_count'] : 0;

        //到货通知
        $member_goods = $this->app->model('member_goods');
        $goods_notice = $member_goods->count(array('type'=>'sto','status'=>'ready'));
        $render->pagedata['goods_notice'] = intval($goods_notice);

        $mdl_orders = $this->app->model('orders');
        
        //今日订单
        $today_filter = array(
                    '_createtime_search'=>'between',
                    'createtime_from'=>date('Y-m-d',strtotime('TODAY')),
                    'createtime_to'=>date('Y-m-d'),
                    'createtime' => date('Y-m-d'),
                    '_DTIME_'=>
                        array(
                            'H'=>array('createtime_from'=>'00','createtime_to'=>date('H')),
                            'M'=>array('createtime_from'=>'00','createtime_to'=>date('i'))
                        )
                );
        $today_order = $mdl_orders->count($today_filter);
        $render->pagedata['today_order'] = intval($today_order);

        //昨日订单
        $date = strtotime('yesterday');
        $yesterday_filter = array(
                    '_createtime_search'=>'between',
                    'createtime_from'=>date('Y-m-d',$date),
                    'createtime_to'=>date('Y-m-d',strtotime('today')),
                    'createtime' => date('Y-m-d',$date),
                    '_DTIME_'=>
                        array(
                            'H'=>array('createtime_from'=>'00','createtime_to'=>date('H',$date)),
                            'M'=>array('createtime_from'=>'00','createtime_to'=>date('i',$date))
                        )
                );
        $yesterday_order = $mdl_orders->count($yesterday_filter);
        //var_dump($yesterday_order);
        $render->pagedata['yesterday_order'] = intval($yesterday_order);


        //今日已付款订单
        $mdl_orders = $this->app->model('orders');
        $today_filter = array_merge($today_filter,array('pay_status'=>'1'));
        $today_payed = $mdl_orders->count($today_filter);
        $render->pagedata['today_payed'] = intval($today_payed);

        //昨日已付款订单
        $date = strtotime('yesterday');
        $yesterday_filter = array_merge($yesterday_filter,array('pay_status'=>'1'));
        $yesterday_payed = $mdl_orders->count($yesterday_filter);
        $render->pagedata['yesterday_payed'] = intval($yesterday_payed);

        
        $mdl_member = $this->app->model('members');
        //今日新增会员
        $today_filter = array(
                    '_regtime_search'=>'between',
                    'regtime_from'=>date('Y-m-d',strtotime('TODAY')),
                    'regtime_to'=>date('Y-m-d'),
                    'regtime' => date('Y-m-d'),
                    '_DTIME_'=>
                        array(
                            'H'=>array('regtime_from'=>'00','regtime_to'=>date('H')),
                            'M'=>array('regtime_from'=>'00','regtime_to'=>date('i'))
                        )
                );
        $today_reg = $mdl_member->count($today_filter);
        $render->pagedata['today_reg'] = intval($today_reg);

        //昨日新增
        $date = strtotime('yesterday');
        $yesterday_filter = array(
                    '_regtime_search'=>'between',
                    'regtime_from'=>date('Y-m-d',$date),
                    'regtime_to'=>date('Y-m-d',strtotime('today')),
                    'regtime' => date('Y-m-d',$date),
                    '_DTIME_'=>
                        array(
                            'H'=>array('regtime_from'=>'00','regtime_to'=>date('H',$date)),
                            'M'=>array('regtime_from'=>'00','regtime_to'=>date('i',$date))
                        )
                );
        $yesterday_reg = $mdl_member->count($yesterday_filter);
        $render->pagedata['yesterday_reg'] = intval($yesterday_reg);

        //会员总数
        $member_count = $mdl_member->count(null);
        $render->pagedata['member_count'] = intval($member_count);
    
        //商品总数
        $mdl_goods = $this->app->model('goods');
        $goods_count = $mdl_goods->count(null);
        $render->pagedata['goods_count'] = intval($goods_count);
        
        //页面输出根本没用到，就暂时注释掉了
        //已下架的商品
        //$market_count = $mdl_goods->count(array('marketable'=>'false'));
        //$render->pagedata['market_count'] = intval($market_count);
        
        //缺货商品
        //$lack_goods = $mdl_goods->count(array('marketable'=>'true','nostore_sell'=>0,'store'=>0));
        //$render->pagedata['lack_goods'] = intval($lack_goods);
        
        //商品促销
        //$mdl_sales_rule_goods = $this->app->model('sales_rule_goods');
        //$rule_goods_count = $mdl_sales_rule_goods->count(null);
        //$render->pagedata['rule_goods_count'] = intval($rule_goods_count);

        //订单促销
        //$mdl_sales_rule_orders = $this->app->model('sales_rule_order');
        //$rule_orders_count = $mdl_sales_rule_orders->count(array('rule_type'=>'N'));
        //$render->pagedata['rule_orders_count'] = intval($rule_orders_count);

        //优惠券
        //$mdl_coupons = $this->app->model('coupons');
        //$coupons_count = $mdl_coupons->count(null);
        //$render->pagedata['coupons_count'] = intval($coupons_count);

        //积分优惠券
        //if(app::get('gift')->is_actived()){

        //    $mdl_coupons = app::get('gift')->model('goods');
        //    $score_coupons_count = $mdl_coupons->count();
        //    $render->pagedata['score_coupons_count'] = intval($score_coupons_count);
        //    $render->pagedata['gift_is_installed'] = true;
        //}

        $render->pagedata['data'] = '';
        return $render->fetch('desktop/widgets/workcount.html');
    }
    function get_className(){
        
          return " valigntop";
    }
    function get_width(){
          
          return "l-2";
        
    }
    
}

?>