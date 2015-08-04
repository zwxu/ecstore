<?php

class complain_finder_reports
{
    var $detail_basic = '基本信息';
    var $detail_comment = '留言信息';
    function __construct(&$app){
       $this->app=$app;
       $this->app_b2c=app::get('b2c');
       
    }
    /*var $column_test='操作';
    public function column_test($row){
       
       return '<a href="index.php?app=complain&ctl=admin_complain&act=test&p[0]=' . $row['complain_id'] . '" title="快递单打印" target="{message:\'启用中....\',updateMap:{},onComplete:function(re){var json=JSON.decode(re);if(json.success){if (finderGroup&&finderGroup[\'' . $_GET['_finder']['finder_id'] . '\']) finderGroup[\'' . $_GET['_finder']['finder_id'] . '\'].refresh();}}}">启用</a>';
    }*/
    public function detail_basic($reports_id){
       $render = $this->app->render();
       $mdl_complain=$this->app->model('reports');
       $complain=$mdl_complain->get_List('*',array('reports_id'=>$reports_id));

       $render->pagedata['member_info'] = $this->get_member_info($complain[0]['member_id']);
       $render->pagedata['store_info'] = $this->get_store_info($complain[0]['store_id']);

       $render->pagedata['complain']=$complain[0];
       return $render->fetch('admin/reports/detail_basic.html');
    }
    public function detail_comment($reports_id){
       $render = $this->app->render();
       $mdl_complain=$this->app->model('reports');
       $subsdf = array('reports_comments'=>'*'); 
       $complain=$mdl_complain->dump($reports_id, '*', $subsdf);
       $complain['member_info'] = $this->get_member_info($complain['member_id']);
       $complain['store_info'] = $this->get_store_info($complain['store_id']);

       $render->pagedata['complain']=$complain;
       $render->pagedata['res_url']=$this->app->res_url;
       return $render->fetch('admin/reports/detail_comment.html');
    }


    public function get_member_info($member_id){
        $obj_member=$this->app_b2c->model('members');
        $member_info=$obj_member->get_member_info($member_id);

        
        return $member_info;
    }
    public function get_store_info($store_id){
       $obj_strman = app::get('business')->model('storemanger');
       $store_info=$obj_strman->dump($store_id);       
       return $store_info;
       //echo '<pre>';print_r($store_info);echo '</pre>';
    }

}