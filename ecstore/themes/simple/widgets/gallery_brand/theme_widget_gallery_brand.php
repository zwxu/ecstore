<?php
function theme_widget_gallery_brand(&$setting,&$smarty) {
    $show_nums=intval($setting['show_nums']?$setting['show_nums']:'7');
    $brandids=array_values($setting['brand']);
    
    $mdl_brand = app::get('b2c')->model('brand');
    $bland_list=$mdl_brand->getList('brand_id,brand_url,brand_name,brand_logo,fav_count',array('brand_id'=>$brandids,'disabled'=>'false'));
    $bland_list=utils::array_change_key($bland_list,'brand_id');
    
    $searchid=array_keys($bland_list);
    foreach($brandids as $key=> $id){
        if(in_array($id,$searchid)==false){
            unset($brandids[$key]);
        }
    }
    $brand_show=array_slice($brandids,0,$show_nums,true);
    $brand=array();
    $imageDefault = app::get('image')->getConf('image.set');
    foreach($brand_show as $key=>$v){
        if(empty($bland_list[$v]['brand_logo'])){
            $bland_list[$v]['brand_logo']=$imageDefault['S']['default_image'];
        }
        $brand[]=$bland_list[$v];
    }
    return $brand;
}