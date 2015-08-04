<?php

class business_business_menugoodcat
{
    /**
     * 构造方法
     * @param object app
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
	
	/**
	 * 生成自己app会员中心的菜单
	 * @param array - 会员中心的菜单数组，引用值
	 * @param array - url 参数
	 * @return boolean - 是否成功
	 */
	public function get_extends_menu(&$arr_menus, $args=array())
	{
		$arr_extends = array();

			$arr_extends = array(
				array('label' => $this->app->_('商品管理'),
					'mid'=>7,
					'items' => array(
						array('label' => $this->app->_('分类管理'),'app'=>'business','ctl'=>'site_goodcat','link'=>'return_goodcat'),
                        
                        array('label' => $this->app->_('品牌管理'),'app'=>'business','ctl'=>'site_brand','link'=>'return_brand'),
					),
				)
			);
			
			$arr_menus = array_merge($arr_menus, $arr_extends);

			return true;
	}
}