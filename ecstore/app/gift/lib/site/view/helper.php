<?php

 

class gift_site_view_helper 
{

    function function_header($params, &$smarty)
    {
		if ($smarty->app->app_id != 'b2c' && $smarty->app->app_id != 'gift') return;
		
		$app_dir = app::get('gift')->app_dir;
		$smarty->pagedata['ec_res_url'] = app::get('gift')->res_url;
		
		/** 不同的页面扩展不同的css **/
		$ext_filename = $smarty->_request->get_app_name() . '_' . $smarty->_request->get_ctl_name() . '.html';
		if (file_exists($app_dir.'/view/site/common/ext/'.$ext_filename))
			$smarty->pagedata['extends_header'] .= $smarty->fetch('site/common/ext/'.$ext_filename,'gift');
		/** end **/
    }

}//结束