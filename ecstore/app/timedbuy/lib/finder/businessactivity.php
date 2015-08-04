<?php
class timedbuy_finder_businessactivity{
    function __construct($app){
        $this->app = $app;
        $this->router = app::get('desktop')->router();
        $this->businessActivity = $this->app->model('businessactivity');
    }//End

    var $detail_basic = '查看';
    function detail_basic($id){
         $render = $this->app->render();
        
        $render->pagedata['from_submit_url'] = $this->router->gen_url(array('app'=>'timedbuy','ctl'=>'admin_businessactivity','act'=>'audit'));
        $render->pagedata['from_cancle_url'] = $this->router->gen_url(array('app'=>'timedbuy','ctl'=>'admin_businessactivity','act'=>'audit','sign'=>'true'));
        $arr = $this->businessActivity->getList( '*',array('id'=>$id) );

        $sto= kernel::single("business_memberstore",$arr[0]['member_id']);        
        $render->pagedata['issue_typename'] = $sto->storeinfo['issue_typename'];
        $render->pagedata['store_gradename'] = $sto->storeinfo['store_gradename'];
        $render->pagedata['store_name'] = $sto->storeinfo['store_name'];

        reset( $arr );
        $arr = current( $arr );
        $gid = $arr['gid'];
        $aid = $arr['aid'];
        $mid = $arr['member_id'];
        $render->pagedata['business'] = $arr;
        
        $activity = app::get('timedbuy')->model('activity')->getList('*',array('act_id'=>$aid));
        $activity = $activity[0];
        $activity['start_time'] = date('Y-m-d',$activity['start_time']);
        $activity['end_time'] = date('Y-m-d',$activity['end_time']);
        $render->pagedata['activity'] = $activity;//活动信息
        $sql = 'select name,price,freight_bear from sdb_b2c_goods where goods_id = "'.$gid.'"';

        $goods = app::get('b2c')->model('goods')->db->selectrow($sql);
        $render->pagedata['goods'] = $goods;//商品信息
        
        $member = app::get('pam')->model('account')->getList('*',array('account_id'=>$mid));
        $render->pagedata['member'] = $member[0];
        
        
        $imageDefault = app::get('image')->getConf('image.set');
        $defaultImage = $imageDefault['M']['default_image'];
        $render->pagedata['defaultImage'] = $defaultImage;

        $url = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg1'=>$gid));
        $render->pagedata['url'] = $url;
        return $render->fetch('admin/finder/businessactivity.html');
    }
    
   
}