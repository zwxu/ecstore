<?php

 
class b2c_finder_dlycorp{

    var $detail_basic = '编辑配送公司';
    
    function __construct($app)
    {
        $this->app = $app;
    }
    
    public function detail_basic($id){
        $dly_corp = $this->app->model('dlycorp');
        if($_POST){
            $_POST['corp_id'] = $id;
            $result = $dly_corp->save($_POST);
            //echo $result;
        }
        else
        {
            $render = $this->app->render();
            $row = $dly_corp->dump($id);
            $render->pagedata['dlycrop'] = $row;
            
            return $render->fetch("admin/delivery/dlycrop_view.html", $this->app->app_id);
        }
    }
    
    public $column_editbutton = '编辑';
    public function column_editbutton($row)
    {
        return '<a href="index.php?app=b2c&ctl=admin_dlycorp&act=showEdit&corp_id='.$row['corp_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&finder_id='.$_GET['_finder']['finder_id'].'" target="dialog::{title:\''.app::get('b2c')->_('编辑物流公司信息').'\',width:500,height:259}"><span>'.app::get('b2c')->_('编辑').'</span></a>';
    }
}
