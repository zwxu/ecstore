<?php

/**
* 后台购买了该商品的用户还购买了的控制器类
*/
class recommended_ctl_admin_setting extends desktop_controller{
    /**
	* 控制器的构造方法
	* @param object $app 当前APP实例
	*/
    public function __construct($app){
        parent::__construct($app);
        $this->ui = new base_component_ui($this);
        $this->app = $app;
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    /**
	* 显示方法
	*/
    public function index(){

        if ( !$_POST ){
            $this->pagedata['period'] = $this->app->getConf('period');
            $this->page('setting.html');
        }
        else {
            $this->begin();
            $this->app->setConf( 'period', $_POST['period'] );
            $this->end( true, app::get('recommended')->_('时间段保存成功！') );
        }
    }
    /**
	* 更新数据的方法
	* @access public 
	*/
    public function update(){		
		$this->begin();
		
        $obj = kernel::single('recommended_data_operaction');
		if (!$obj->update($msg)){
			$this->end( false, $msg );
		}
        // move data to table recommended_goods_period
        if (!$obj->move($msg)){
			$this->end( false, $msg );
		}
		
		$this->end( true, app::get('recommended')->_('数据更新成功！') );
    }// end of function update
}