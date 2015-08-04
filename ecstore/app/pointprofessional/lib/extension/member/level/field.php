<?php

 
class pointprofessional_extension_member_level_field
{
	/**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {        
        $this->app = $app;
    }
	
	/**
	 * 扩展setting的方法
	 * @param string 过期是否开启
	 * @param string 过期设置的方式
	 * @param array member level array
	 * @return null
	 */
	public function get_html($is_point_expired, $point_expired_method, $arr_lv)
	{
		//if ($is_point_expired == 'true')
		//{
			//$render = $this->app->render();
			//$render->pagedata['is_expired'] = 'true';
			//if ($point_expired_method == '1')
				//$render->pagedata['is_expired_end'] = 'true';
			//else
				//$render->pagedata['is_expired_interval'] = 'true';
			
			//$render->pagedata['lv'] = $arr_lv;
			
			//return $render->fetch('admin/member/lv_ext.html');
		//}
		
		return '';
	}
}