<?php

/**
 * 这个类实现前台会员中心按钮的的扩展
 * 
 * @version 0.1
 * @package aftersales.lib
 */
class aftersales_member_menuextends
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
		if ($this->app->getConf('site.is_open_return_product'))
		{
			$arr_extends = array(
				// array('label' => $this->app->_('售后服务'),
				// 	'mid'=>6,
				// 	'items' => array(
				// 		array('label' => $this->app->_('售后管理'),'app'=>'aftersales','ctl'=>'site_member','link'=>'return_policy','args'=>$args),
				// 	),
				// )
			);
			
			$arr_menus = array_merge($arr_menus, $arr_extends);
			return true;
		}
			
		return false;
	}
}