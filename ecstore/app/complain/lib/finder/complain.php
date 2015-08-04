<?php

class complain_finder_complain
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
    public function detail_basic($complain_id){
       $mdl_order=$this->app_b2c->model('orders');
       $render = $this->app->render();
       $mdl_complain=$this->app->model('complain');
        $complain=$mdl_complain->dump($complain_id);
       $render->pagedata['complain']=$complain;
       /*$order_id=$complain['order_id'];
       $subsdf = array('order_pmt'=>array('*'),'order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $aOrder = $mdl_order->dump($order_id, '*', $subsdf);
       $render->pagedata['aOrder']=$aOrder;
       //echo '<pre>';print_r($aOrder);exit;*/
       return $render->fetch('admin/complain/detail_basic.html');
    }
    public function detail_comment($complain_id){
       $render = $this->app->render();
       $mdl_complain=$this->app->model('complain');
       $subsdf = array('complain_comments'=>'*');
        $complain=$mdl_complain->dump($complain_id, '*', $subsdf);
       $render->pagedata['complain']=$complain;
       $render->pagedata['res_url']=$this->app->res_url;
       return $render->fetch('admin/complain/detail_comment.html');
    }
}