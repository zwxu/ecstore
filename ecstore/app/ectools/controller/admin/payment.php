<?php

 
class ectools_ctl_admin_payment extends desktop_controller{
	
	public function __construct($app)
	{
		parent::__construct($app);
		header("cache-control: no-store, no-cache, must-revalidate");
	}
	
    function index(){
        $this->finder('ectools_mdl_payments',array(

            'title'=>app::get('ectools')->_('收款单'),

            'allow_detail_popup'=>true,
        	'use_buildin_export'=>true,
            ));
    }
    
    /**
     * 新建支付订单
     * @params array - 订单详细内容
     * @return boolean - 订单成功与否
     */
    public function addnew($arrPayments=array())
    {
        echo __FILE__.':'.__LINE__;
    }
    
}
