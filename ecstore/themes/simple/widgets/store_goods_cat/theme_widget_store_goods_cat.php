<?php
function theme_widget_store_goods_cat(&$setting,&$render){
    $store_id = $render->pagedata['store_id'];
    //error_log(microtime().'='.$store_id."\n",3,DATA_DIR.'/theme_widget_store_goods_cat.txt');
    $data['store_id'] = $store_id;
    $mdl_goods_cat = app::get('business')->model('goods_cat');
    $_cats = $mdl_goods_cat->db->select("select a.cat_name,a.parent_id,a.custom_cat_id AS cat_id,(CASE WHEN b.p_order is null THEN 1 ELSE 2 END) AS step,(CASE WHEN b.p_order is null THEN CONCAT(a.p_order,',','0') ELSE CONCAT(b.p_order,',',a.p_order) END) AS ob from sdb_business_goods_cat a LEFT JOIN sdb_business_goods_cat b ON a.parent_id = b.custom_cat_id WHERE a.store_id=".intval($store_id)." ORDER BY ob,a.parent_id");
    $cats = array();
    foreach ($_cats as $cat) {
        if($cat['step']=='1'){
            $cats[$cat['cat_id']] = array_merge((array)$cats[$cat['cat_id']],$cat);
        }else{
            $cats[$cat['parent_id']]['child_cats'][$cat['cat_id']] = $cat;
        }
    }
    $data['cats'] = $cats;
    $data['title'] = $setting['title'];
    return $data;
}