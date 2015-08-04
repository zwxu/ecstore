<?php
class package_mdl_activity extends dbeav_model{
    function __construct($app){
        parent::__construct($app);
        $this->use_meta();
    }
    
    function modifier_business_type($row){
        $obj_cat = app::get('b2c')->model('goods_cat');
        $arr_cat = array_filter(explode(',', $row));
        if(!$arr_cat) return '';
        $sdf = $obj_cat->getList('cat_name',array('cat_id'=>$arr_cat));
        $arr_catname = array();
        foreach((array)$sdf as $items){
            $arr_catname[] = $items['cat_name'];
        }
        return implode(',', $arr_catname);
    }
}