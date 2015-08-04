<?php

 
class gift_finder_goods {

    function __construct(&$app) 
    {
        $this->app = $app;
        $this->router = app::get('site')->router();
    }//End 
    
    public $column_edit='编辑';
    public $column_edit_width='80';
    public function column_edit($row){
        return '<a href="index.php?app=gift&ctl=admin_gift&act=edit&gift_id=' . $row['goods_id']. '&finder_id='.$_GET['_finder']['finder_id'].'" target="_blank" >'.app::get('gigt')->_('编辑').'</a>&nbsp;&nbsp;'.
              '<a href="'. $this->router->gen_url(array('app'=>'gift', 'ctl'=>'site_gift', 'act'=>'index', 'arg0'=>$row['goods_id'])) . '" target="_blank" >'.app::get('gigt')->_('预览').'</a>';
    }




}
