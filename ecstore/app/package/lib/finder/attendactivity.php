<?php
class package_finder_attendactivity{
    function __construct($app){
        $this->app = $app;
        $this->router = app::get('desktop')->router();
        $this->attendactivity = $this->app->model('attendactivity');
    }//End

    var $detail_basic = '查看';
    function detail_basic($id){
        $render = $this->app->render();
        
        $render->pagedata['from_submit_url'] = $this->router->gen_url(array('app'=>'package','ctl'=>'admin_attendactivity','act'=>'audit'));
        $render->pagedata['from_cancle_url'] = $this->router->gen_url(array('app'=>'package','ctl'=>'admin_attendactivity','act'=>'audit','sign'=>'true'));
        $arr = $this->attendactivity->getList( '*',array('id'=>$id) );

        reset( $arr );
        $arr = current( $arr );
        $gid = $arr['gid'];
        $aid = $arr['aid'];
        $arr['image'] = app::get('image')->model('image_attach')->getList('image_id',array('target_type'=>'package','target_id'=>$arr['id']));
        $member = app::get('pam')->model('account')->getList('*',array('account_id'=>$arr['member_id']));
        $arr['login_name'] = $member[0]['login_name'];
        $gid = array_filter(explode(',',$gid));
        if($gid){
            $sql = 'select goods_id,name,bn from sdb_b2c_goods where goods_id in ('.implode(',',$gid).')';
            $arr['goods'] = app::get('b2c')->model('goods')->db->select($sql);
            foreach((array)$arr['goods'] as $key => $value){
                $arr['goods'][$key]['url'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg'=>$value['goods_id']));
            }
        }
        $render->pagedata['business'] = $arr;
        
        $activity = app::get('package')->model('activity')->getList('*',array('act_id'=>$aid));
        $activity = $activity[0];
        $activity['start_time'] = date('Y-m-d',$activity['start_time']);
        $activity['end_time'] = date('Y-m-d',$activity['end_time']);
        $render->pagedata['activity'] = $activity;//活动信息

        $imageDefault = app::get('image')->getConf('image.set');
        $defaultImage = $imageDefault['M']['default_image'];
        $render->pagedata['defaultImage'] = $defaultImage;
        return $render->fetch('finder/attendactivity.html');
    }
}