<?php

 
function theme_widget_mr_hotpeople($setting,&$smarty){
  foreach($setting['top_link_title'] as $tk=>$tv){
        $res['href'][$tk]['top_link_title'] = $tv;
        $res['href'][$tk]['top_link_url'] = $setting['top_link_url'][$tk];
  }

  return $res;
}
?>
