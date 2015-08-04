<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class chinapay_ctl_site_request extends site_controller 
{
	function __construct($app)
	{
		$this->app = $app;
	}
    public function index() 
    {
		$this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/chinapay_payment_plugin_chinapay', 'callback');
		$this->callback_url = $this->callback_url."?".http_build_query($_POST); 
		header('Location:' .$this->callback_url);
		exit;
    }
        
    public function serverCallback() {
        $servercallback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/chinapay_payment_plugin_chinapay', 'callback');
        
        $httpclient = kernel::single('base_httpclient');
        $httpclient->timeout = 30;
        $result = $httpclient->post($servercallback_url,$_POST);
        exit;
    }

}//End Class
