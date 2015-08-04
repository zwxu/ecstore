<?php


 
class pointprofessional_order_total_change
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
	 * 生成新的总的价格
	 * @param array - sdf 请求的参数（引用值）
	 * @param array - order sdf 目标订单数据（引用值）
	 * @param string - 商店设置的计算精度
	 * @param string - 商店设置的显示精度
	 */
	public function order_summary_change(&$sdf, &$order_data, $system_money_decimals, $system_money_operation_carryset)
	{
		if ($order_data['member_id'])
		{
			$obj_members = $this->app->model('members');
			if (isset($sdf['payment']['dis_point']) && $sdf['payment']['dis_point'] && intval($sdf['payment']['dis_point']) > 0)
			{
				// 得到积分相关的三个设置。
				$app_b2c = app::get('b2c');
				$site_point_deductible_value = $app_b2c->getConf('site.point_deductible_value');
				
				$objMath = kernel::single('ectools_math');
				$subtotal_consume_score = $order_data['score_u'];
				$point_dis_value = $objMath->number_multiple(array($site_point_deductible_value, $subtotal_consume_score));				
				
				// 生成订单addon字段
				if ($order_data['addon'])
				{
					$order_data['addon'] = unserialize($order_data['addon']);
				}
				
				$order_data['addon']['order_chgpointmoney'] = strval($point_dis_value);
				$order_data['addon']['order_chgpointscore'] = round($subtotal_consume_score);
			}
			/**
			 * 冻结这部分使用的积分
			 */
			$obj_members->freez($order_data['member_id'], $order_data['score_u']);			
			$obj_members->add_obtained($order_data['member_id'], $order_data['score_g']);				
		}
	}
}