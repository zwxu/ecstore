<?php
function theme_widget_invest_ad_pic(&$setting,&$smarty) {
  
    $data['qq']=app::get('b2c')->getConf('member.ServiceQQ');
	$data['setting']=$setting;
	return $data;
}

?>
