<?php
function theme_widget_store_server(&$setting,&$render){
    $member_id = $smarty->pagedata['member_info']['member_id'];
    $sto= kernel::single("business_memberstore",$member_id);
    $store_id = $sto->storeinfo['store_id'];
    $number = app::get('business')->model('customer_service')->getList('number,type',array('store_id'=>$store_id,'is_defult'=>'1'));
    $setting['number']=$number['0']['number'];
    $setting['type']=$number['0']['type'];
    return $data;
}
?>
