<?php

 
function theme_widget_other_menu($setting,&$smarty){
  
  foreach($setting['top_link_title'] as $tk=>$tv){
        $res['href'][$tk]['top_link_title'] = $tv;
        $res['href'][$tk]['top_link_url'] = $setting['top_link_url'][$tk];
  }
  $res['title'] = $setting['title'];
  if($setting['more'] == '1'){
    $res['more'] = true;
    $res['more_link'] = $setting['more_link'];
  }
  $res['service'] = app::get('b2c')->getConf('member.ServiceQQ');
  return $res;
}
?>
