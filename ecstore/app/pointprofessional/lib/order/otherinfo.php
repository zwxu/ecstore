<?php


 
class pointprofessional_order_otherinfo
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
	 * 得到订单积分折扣的信息
	 * @param array - order sdf - 引用值
	 * @return string html
	 */
	public function gen_point_discount(&$sdf)
	{
		if (!$sdf)
			return '';
		
		if ($sdf['addon'])
		{
			$sdf['addon'] = unserialize($sdf['addon']);
			$sdf['order_chgpointmoney'] = ($sdf['addon']['order_chgpointmoney']) ? $sdf['addon']['order_chgpointmoney'] : '0';
		}
		else
			$sdf['order_chgpointmoney'] = '';
		
		$render = $this->app->render();
		$render->pagedata['order_chgpointmoney'] = $sdf['order_chgpointmoney'];
		return $render->fetch('site/order/order_dis.html');
	}
}