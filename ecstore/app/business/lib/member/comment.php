<?php

class business_member_comment extends site_controller
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
        $comment = '';
        
        $pay_status=$v['pay_status'];
        $order_id=$v['order_id'];
        $status=$v['status'];

      
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
                    $comment .= "<a href=".$url." class='font-blue operate-btn'>商品评论</a>";
                }else{
                    $url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_comment', 'act' => 'addition', 'arg0' => $order_id));
                    $comment .= "<a href=".$url." class='font-blue operate-btn'>追加评论</a>";
                }
            }
        }
      
       return $comment;
    }

    
   
}