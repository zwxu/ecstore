<?php

/*
 * @package site
 * @author edwin.lzh@gmail.com
 * @license
 */
class site_ctl_admin_sitemaps extends site_admin_controller
{
    /*
     * workground
     * @var string
     */
    var $workground = 'seo_ctl_admin_sitemaps';

    /*
     * ï¿½Ð±ï¿½
     * @public
     */
    public function index(){
    	$shop_base = app::get('site')->router()->gen_url(array('app'=>'site', 'ctl'=>'sitemaps', 'act'=>'catalog', 'full'=>1));
    	$this->pagedata['url'] = $shop_base;
		$this->page('admin/sitemaps/index.html');
    }

}//End Class
