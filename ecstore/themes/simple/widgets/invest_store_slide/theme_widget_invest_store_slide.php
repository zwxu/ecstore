<?php

 
function theme_widget_invest_store_slide(&$setting,&$render){
    
	$limit=$setting['num']?$setting['num']:12;
	$storemanger=$setting['storemanger'];
	$mdl_storemanger=app::get('business')->model('storemanger');
	if(empty($storemanger)||!is_array($storemanger)){
	    $storemanger=$mdl_storemanger->get_list_approved('store_id,store_name,image',array(),0,$limit,'approved_time desc');
		
	}else{
		$arr_storemanger=$storemanger;
		unset($storemanger);
	    foreach((array)$arr_storemanger as $key=>$value){
		    $store=$mdl_storemanger->getList('store_id,store_name,image',array('store_id'=>$value),0,1);
			$storemanger[]=$store[0];
		}
	}
	foreach($storemanger as $key=>$value){
	    $arr[]=$value;
		if(count($arr)==2){
		    $data['storemanger'][]=$arr;
			unset($arr);
		}
		if(count($storemanger)%2!=0&&count($storemanger)==$key+1){			
		    $data['storemanger'][]=$arr;
			unset($arr);
		}
	}
	return $data;
}
?>
