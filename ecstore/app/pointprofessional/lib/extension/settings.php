<?php

 
class pointprofessional_extension_settings
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
	 * @param array 引用的数组
	 * @return null
	 */
	public function settings(&$arr_settings=array())
	{
		$arr_ext_settings = array(
			'site.point_expired',
			'site.point_expried_method',
			'site.point_expired_value',
			'site.point_max_deductible_method',
			'site.point_max_deductible_value',
            'site.point_money_value',//获取积分时积分与金额比例
            'site.point_max_get_value',//商家积分设置最高比例
            'site.point_mim_get_value',//商家积分设置最低比例
			'site.point_deductible_value',
			'site.get_point_interval_time',
			'site.get_policy.stage',
			'site.consume_point.stage',
			'site.point_usage',
		);
		
		$arr_settings[app::get('b2c')->_('积分设置')] = array_merge($arr_settings[app::get('b2c')->_('积分设置')], $arr_ext_settings);
	}
}