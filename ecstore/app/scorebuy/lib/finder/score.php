<?php

class scorebuy_finder_score{

    function __construct($app){
        $this->app = $app;
        $this->router = app::get('desktop')->router();
        $this->activity = $this->app->model('activity');
    }//End
    
    public $column_edit = '操作';
    public $column_edit_width = 110;
    public $column_business_type = '商户经营范围';
    public $column_business_type_width = 150;

    public function column_edit($row){
        $row = $this->activity->getList('*',array('act_id'=>$row['act_id']));
        $row = $row[0];
        if($row['act_status'] != '2'){
            $html = '<a href="'. $this->router->gen_url( array('app'=>'scorebuy','ctl'=>'admin_score','act'=>'edit','act_id'=>$row['act_id']) ) .'" >'.app::get('scorebuy')->_('编辑').'</a>&nbsp;&nbsp;';
            return $html;
        }
    }

    function column_business_type($row){
        $business_type = $this->activity->dump(array('act_id'=>$row['act_id']),'business_type');
        $business_type = $business_type['business_type'];
        $businee_type = array_filter(explode(',',$business_type));
        $catObj = app::get('b2c')->model('goods_cat');
        $cats = $catObj->getList('cat_name',array('cat_id'=>$businee_type));
        $cat_names = array();
        foreach($cats as $k=>$cat){
            $cat_names[] = $cat['cat_name'];
        }
        return implode(',',$cat_names);

    }

}