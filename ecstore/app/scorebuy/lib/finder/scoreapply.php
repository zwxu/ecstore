<?php
class scorebuy_finder_scoreapply{
    function __construct($app){
        $this->app = $app;
        $this->router = app::get('desktop')->router();
        $this->businessActivity = $this->app->model('scoreapply');
    }//End

    var $detail_basic = '查看';
    function detail_basic($id){
        $render = $this->app->render();
        
        $render->pagedata['from_submit_url'] = $this->router->gen_url(array('app'=>'scorebuy','ctl'=>'admin_scoreapply','act'=>'audit'));
        $render->pagedata['from_cancle_url'] = $this->router->gen_url(array('app'=>'scorebuy','ctl'=>'admin_scoreapply','act'=>'audit','sign'=>'true'));
        $arr = $this->businessActivity->getList( '*',array('id'=>$id) );

        reset( $arr );
        $arr = current( $arr );
        $gid = $arr['gid'];
        $aid = $arr['aid'];
        $mid = $arr['member_id'];
        $isMemLv = $arr['isMemLv'];
        $render->pagedata['business'] = $arr;
        
        $activity = app::get('scorebuy')->model('activity')->getList('*',array('act_id'=>$aid));
        $activity = $activity[0];
        $activity['start_time'] = date('Y-m-d',$activity['start_time']);
        $activity['end_time'] = date('Y-m-d',$activity['end_time']);
        $activity['goodsLink'] = app::get('site')->router()->gen_url( array( 'app'=>'scorebuy','real'=>1,'ctl'=>'site_product','args'=>array($gid,'','',$arr['id']) ) );

        $render->pagedata['activity'] = $activity;//活动信息
        
        $sql = 'select name,price,freight_bear from sdb_b2c_goods where goods_id = "'.$gid.'"';
        $goods = app::get('b2c')->model('goods')->db->selectrow($sql);
        $render->pagedata['goods'] = $goods;//商品信息
        
        $member = app::get('pam')->model('account')->getList('*',array('account_id'=>$mid));
        $render->pagedata['member'] = $member[0];

        //加载会员等级信息 
        if($isMemLv){
            $memLvObj = app::get('b2c')->model('member_lv');
            $memLvScoreObj = app::get('scorebuy')->model('memberlvscore');
            $memLvs = $memLvObj->getList('member_lv_id,name',array('display'=>'false'));
            
            $memLvScores = $memLvScoreObj->getList('*',array('aid'=>$id,'gid'=>$gid));
            $memLvScoresArr = array();
            foreach($memLvScores as $k=>$v){
                $memLvScoresArr[$v['level_id']] = $v;
            }
            foreach($memLvs as $k=>$v){
                $memLvs[$k]['info'] = $memLvScoresArr[$v['member_lv_id']];
            }
            $render->pagedata['memberLv'] = $memLvs;
        }
        //加载会员等级信息

        $imageDefault = app::get('image')->getConf('image.set');
        $defaultImage = $imageDefault['M']['default_image'];
        $render->pagedata['defaultImage'] = $defaultImage;
        return $render->fetch('admin/finder/businessactivity.html');
    }
    
   
}