<?php

class complain_buyer_orders
{
   function __construct($app){
       $this->app=$app;
   }
    //查询个订单的权限
    public function get_orders_html($v){    
        //$pay_status=$v['pay_status'];
        //$pay_app_id=$v['payinfo']['pay_app_id'];
        //$ship_status=$v['ship_status'];
        $order_id=$v['order_id'];
        //$confirm_time=$v['confirm_time'];
        //$pay_time=$v['pay_time'];
        //$need_send=$v['need_send'];
        //$refund_status=$v['refund_status'];
        //$is_extend=$v['is_extend'];
        $mdl_complain=$this->app->model('complain');
        $complain=$mdl_complain->getlist('*',array('order_id'=>$v['order_id']));
        $title='投诉卖家';
        if($complain[0]){
           $complain_id=$complain[0]['complain_id'];
           switch($complain[0]['status']){
              case 'intervene':{
                $title='投诉中';
                 break;
              }
              case 'success':{
                $title='投诉成立';
                 break;
              }
              case 'error':{
                $title='投诉不成立';
                 break;
              }
              
              case 'cancel':{
                $title='投诉撤销';
                 break;
              }
           }
        }
        if($title=='投诉卖家'){
            $url = app::get('site')->router()->gen_url(array('app' => 'complain', 'ctl' => 'site_buyer_complain', 'act' => 'add', 'arg0' => $v['member_id'], 'arg1' => $v['order_id'], 'arg2' => $v['store_id']));
        }else{
            $url = app::get('site')->router()->gen_url(array('app' => 'complain', 'ctl' => 'site_buyer_complain', 'act' => 'show_comment', 'arg0' =>$complain_id ));
            
        }
        $html = "<a href=".$url." class='font-blue operate-btn'>".$title."</a>";
        return $html;
    }
}