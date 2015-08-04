<?php

	
/**
* 前台头尾部内容类
*/
class content_site_view_helper 
{
	/**
	* 头部
	* @param array 参数
	* @param object $smarty smarty实例
	* @return string  返回HTML内容
	*/
    function function_header($params, &$smarty)
    {
        if($smarty->app->app_id !='content') return '';

        $smarty->pagedata['TITLE'] = &$smarty->pagedata['title'];
        $smarty->pagedata['KEYWORDS'] = &$smarty->pagedata['keywords'];
        $smarty->pagedata['DESCRIPTION'] = &$smarty->pagedata['description'];
		
		$smarty->pagedata['ec_res_url'] = $smarty->app->res_url;
		$ext_filename = $smarty->_request->get_app_name() . '_' . $smarty->_request->get_ctl_name() . '_' . $smarty->_request->get_act_name() . '.html';
		if (file_exists($smarty->app->app_dir.'/view/site/common/ext/'.$ext_filename)) 
			$smarty->pagedata['extends_header'] = $smarty->fetch('site/common/ext/'.$ext_filename,$smarty->app->app_id);
        //$
        return $smarty->fetch('site/common/header.html', app::get('content')->app_id);
    }


/**
    function function_footer($params, &$smarty)
    {
        return ;
    }
//*/

}//End Class
