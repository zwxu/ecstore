<?php


function theme_widget_store_info(&$setting,&$smarty){   
    // $setting['store']=$smarty->pagedata['store'];
    $store_id=$smarty->pagedata['store']['store_id'];
    $setting['store']=app::get('business')->model('storemanger')->getRow('*',array('store_id'=>$store_id));
    
    //QQ客服
    $member_id = $smarty->pagedata['member_info']['member_id'];
    $sto= kernel::single("business_memberstore",$member_id);
    $store_id = $sto->storeinfo['store_id'];
    $number = app::get('business')->model('customer_service')->getList('number,type',array('store_id'=>$store_id,'is_defult'=>'1'));
    $setting['number']=$number['0']['number'];
    $setting['type']=$number['0']['type'];

    //地区
    $area=$setting['store']['area'];
    $area_arr=explode(':',$area);
    $area_arr=explode('/',$area_arr[1]);
    $setting['store']['area']=$area_arr[0]."&nbsp;&nbsp;".$area_arr[1];
    $app=app::get('business');
    //店铺等级
    $grade=$app->model('storegrade')->dump($setting['store']['store_grade'],'grade_name');
    $setting['grade_name']=$grade['grade_name'];
    //会员登录
    $setting['isLogin']=isset($smarty->pagedata['member_info']['member_id']);
    //评分情况
    $objComment =$app->model('comment_stores_point');
    $store_info = $objComment->getStoreInfo($store_id);
    $setting['store_point']=$store_info['store_point'];
    return $setting;
}
?>
