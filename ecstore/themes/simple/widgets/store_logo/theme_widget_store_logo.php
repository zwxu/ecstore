<?php

function theme_widget_store_logo(&$setting,&$smarty){
  $store_id = $smarty->pagedata['store_id'];
  if($store_id){
	$setting['store_id'] = $store_id;
	$store_name = app::get('business')->model('storemanger')->getList('store_id,store_name,store_grade',array('store_id'=>$store_id));
	$store_grade = $store_name[0]['store_grade'];
	$gradeInfo = app::get('business')->model('storegrade')->getList('grade_id,issue_type',array('grade_id'=>$store_grade));
	if($gradeInfo[0]['issue_type']=='3'){
		$setting['isBrand'] = 'true';
	}
	$setting['store_name'] = $store_name[0]['store_name'];
	//客服
	$member_id = $smarty->pagedata['member_info']['member_id'];
    $sto= kernel::single("business_memberstore",$member_id);
    $store_id = $sto->storeinfo['store_id'];
    $number = app::get('business')->model('customer_service')->getList('number,type',array('store_id'=>$store_id,'is_defult'=>'1'));
    $setting['number']=$number['0']['number'];
    $setting['type']=$number['0']['type'];
  }

  return $setting;
}
?>


