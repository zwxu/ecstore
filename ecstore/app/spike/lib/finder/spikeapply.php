<?php
class spike_finder_spikeapply{
    function __construct($app){
        $this->app = $app;
        $this->router = app::get('desktop')->router();
        $this->businessActivity = $this->app->model('spikeapply');
    }//End

    var $detail_basic = '查看';
    function detail_basic($id){
        $render = $this->app->render();
        
        $render->pagedata['from_submit_url'] = $this->router->gen_url(array('app'=>'spike','ctl'=>'admin_spikeapply','act'=>'audit'));
        $render->pagedata['from_cancle_url'] = $this->router->gen_url(array('app'=>'spike','ctl'=>'admin_spikeapply','act'=>'audit','sign'=>'true'));
        $arr = $this->businessActivity->dump( array('id'=>$id),'*' );

        $gid = $arr['gid'];
        $aid = $arr['aid'];
        $mid = $arr['member_id'];
        $render->pagedata['business'] = $arr;
        
        $activity = $this->app->model('activity')->dump(array('act_id'=>$aid),'*');
        $activity['start_time'] = date('Y-m-d',$activity['start_time']);
        $activity['end_time'] = date('Y-m-d',$activity['end_time']);

        $activity['goodsLink'] = app::get('site')->router()->gen_url( array( 'app'=>'spike','real'=>1,'ctl'=>'site_product','args'=>array($gid,'','',$arr['id']) ) );
        $render->pagedata['activity'] = $activity;//活动信息

        $sql = 'select name,price,freight_bear from sdb_b2c_goods where goods_id = "'.$gid.'"';
        $goods = app::get('b2c')->model('goods')->db->selectrow($sql);
        $render->pagedata['goods'] = $goods;//商品信息

        //店铺信息
        $sto= kernel::single("business_memberstore",$mid);
        $member = array();
        $member['login_name'] = $sto->storeinfo['account_loginname'];
        $member['store_gradename'] = $sto->storeinfo['store_gradename'];
        $member['issue_typename'] = $sto->storeinfo['issue_typename'];
        $member['store_name'] = $sto->storeinfo['store_name'];
        $render->pagedata['member'] = $member;

        $imageDefault = app::get('image')->getConf('image.set');
        $defaultImage = $imageDefault['M']['default_image'];
        $render->pagedata['defaultImage'] = $defaultImage;
        return $render->fetch('admin/finder/businessactivity.html');
    }
    
   
}