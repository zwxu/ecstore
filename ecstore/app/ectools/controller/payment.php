<?php

 
class ectools_ctl_payment extends desktop_controller{

    var $workground = 'ectools_ctl_admin_order';
	
	public function __construct($app)
	{
		parent::__construct($app);
		header("cache-control: no-store, no-cache, must-revalidate");
	}
	
    function index(){
        $this->finder('ectools_mdl_payments',array(
            'title'=>app::get('ectools')->_('支付单'),
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
