<?php

 

class aftersales_ctl_admin_intereven extends desktop_controller{
    public $workground = 'ectools_ctl_admin_order';
    
    public function __construct($app)
    {
        parent::__construct($app);
        $this->router = app::get('desktop')->router();
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    
    public function index()
    {
        $this->finder('aftersales_mdl_return_product',array(
            'title'=>app::get('aftersales')->_('待处理退款纠纷'),
            'base_filter'=>array('is_intervene|in'=>array('3','4')),
            'actions'=>array(
                        ),'use_buildin_set_tag'=>true,'use_buildin_recycle'=>true,'use_buildin_filter'=>true,'use_buildin_export'=>true,
            ));
    }

    public function _views(){

		$count_all = app::get('aftersales')->model('return_product')->count(array('is_intervene|in'=>array('3','4')));
		$count_dai = app::get('aftersales')->model('return_product')->count(array('is_intervene'=>3));
		$count_yi = app::get('aftersales')->model('return_product')->count(array('is_intervene'=>4));

        return array(
                0=>array('label'=>app::get('aftersales')->_('全部'),'optional'=>false,'filter'=>array('is_intervene|in'=>array('3','4')),'addon'=>$count_all,'href'=>$this->router->gen_url(array('app'=>'aftersales','ctl'=>'admin_intereven','act'=>'index','view'=>0))),
                1=>array('label'=>app::get('ectools')->_('待处理'),'optional'=>false,'filter'=>array('is_intervene'=>3),'addon'=>$count_dai,'href'=>$this->router->gen_url(array('app'=>'aftersales','ctl'=>'admin_intereven','act'=>'index','view'=>1))),
                2=>array('label'=>app::get('ectools')->_('已处理'),'optional'=>false,'filter'=>array('is_intervene'=>4),'addon'=>$count_yi,'href'=>$this->router->gen_url(array('app'=>'aftersales','ctl'=>'admin_intereven','act'=>'index','view'=>2))),
        );
    }
    
}