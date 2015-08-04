<?php

 
class ectools_ctl_admin_refund extends desktop_controller{
    
	public function __construct($app)
	{
		parent::__construct($app);
        $this->router = app::get('desktop')->router();
		header("cache-control: no-store, no-cache, must-revalidate");
	}
	
    public function index(){
        $this->finder('ectools_mdl_refunds',array(

            'title'=>app::get('ectools')->_('退款单'),'allow_detail_popup'=>true,
            'base_filter'=>array('refund_type'=>1),
            'actions'=>array(
                            
                        ),
           	'use_buildin_export'=>true,
            ));
    }

    public function _views(){

		$count_all = app::get('ectools')->model('refunds')->count(array('refund_type'=>'1'));
		$count_balance = app::get('ectools')->model('refunds')->count(array('status'=>'succ','refund_type'=>'1','is_safeguard'=>'1'));
		$count_no_balance = app::get('ectools')->model('refunds')->count(array('status'=>'ready','refund_type'=>'1','is_safeguard'=>'1'));
        $safeguard_balance = app::get('ectools')->model('refunds')->count(array('status'=>'succ','refund_type'=>'1','is_safeguard'=>'2'));
		$safeguard_no_balance = app::get('ectools')->model('refunds')->count(array('status'=>'ready','refund_type'=>'1','is_safeguard'=>'2'));

        return array(
                0=>array('label'=>app::get('ectools')->_('全部'),'optional'=>false,'filter'=>'','addon'=>$count_all,'href'=>$this->router->gen_url(array('app'=>'ectools','ctl'=>'admin_refund','act'=>'index','view'=>0))),
                1=>array('label'=>app::get('ectools')->_('确认收货前待处理退款'),'optional'=>false,'filter'=>array('status'=>'ready','is_safeguard'=>'1'),'addon'=>$count_no_balance,'href'=>$this->router->gen_url(array('app'=>'ectools','ctl'=>'admin_refund','act'=>'index','view'=>1))),
                2=>array('label'=>app::get('ectools')->_('确认收货前已处理退款'),'optional'=>false,'filter'=>array('status'=>'succ','is_safeguard'=>'1'),'addon'=>$count_balance,'href'=>$this->router->gen_url(array('app'=>'ectools','ctl'=>'admin_refund','act'=>'index','view'=>2))),
                3=>array('label'=>app::get('ectools')->_('确认收货后待处理退款'),'optional'=>false,'filter'=>array('status'=>'ready','is_safeguard'=>'2'),'addon'=>$safeguard_no_balance,'href'=>$this->router->gen_url(array('app'=>'ectools','ctl'=>'admin_refund','act'=>'index','view'=>3))),
                4=>array('label'=>app::get('ectools')->_('确认收货后已处理退款'),'optional'=>false,'filter'=>array('status'=>'succ','is_safeguard'=>'2'),'addon'=>$safeguard_balance,'href'=>$this->router->gen_url(array('app'=>'ectools','ctl'=>'admin_refund','act'=>'index','view'=>4))),
        );
    }
}
