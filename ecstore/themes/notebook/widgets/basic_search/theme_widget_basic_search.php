<?php


function theme_widget_basic_search(&$setting,&$smarty){
    $data['search_key'] = $GLOBALS['runtime']['search_key'];
    $data['st']=$GLOBALS['runtime']['st'];
	if($setting['top_link_title']){
		foreach($setting['top_link_title'] as $tk=>$tv){
			$res['search'][$tk]['top_link_title'] = $tv;
			$res['search'][$tk]['top_link_url'] = $setting['top_link_url'][$tk];
		}
	}
    $res['search_key'] = $data['search_key'];
    $res['st'] = $data['st'];
    $obj = kernel::service('autocomplete.associate_autocomplete_goods');
	if ($obj && method_exists($obj, 'get_widgets_top_html')){
        // $res['top_html'] = $obj->get_widgets_top_html(); 
		$res['top_html'] = ''; 
	}
	if ($obj && method_exists($obj, 'get_widgets_bottom_html')){
        // $res['bottom_html'] = $obj->get_widgets_bottom_html();
		$res['bottom_html'] = '';
	}
    return $res;
}
?>
