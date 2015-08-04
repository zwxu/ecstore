<?php
 
 
class b2c_ctl_admin_reship extends desktop_controller{

    var $workground = 'b2c_ctl_admin_order';
    
    /**
     * 构造方法
     * @params object app object
     * @return null
     */
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $this->finder('b2c_mdl_reship',array(
            'title'=>app::get('b2c')->_('退货单'),
            'allow_detail_popup'=>true,
            'params'=>array(
                'bill_type' => 'reship',
            )
            ));
    }
    

    function addnew(){
        echo __FILE__.':'.__LINE__;
    }

}
