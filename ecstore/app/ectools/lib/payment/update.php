<?php

 
/**
 * 支付单修改的具体实现逻辑
 * 
 * @version 0.1
 * @package ectools.lib.payment
 */
class ectools_payment_update
{
	/**
	 * @var app object
	 */
	public $app;
	
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
	 * 支付单修改
	 * @param array sdf
	 * @param string message
	 * @return boolean sucess of failure
	 */
	public function generate(&$sdf, &$msg='')
	{
		// 修改支付单是和中心的交互
		$objPayments = $this->app->model('payments');
        $data['payment_id'] = $sdf['payment_id'];
        $data['trade_no'] = $sdf['trade_no'];
        $data['t_payed'] = $sdf['t_payed'];
        //$data['status'] = ($sdf['status'] == 'succ' || $sdf['status'] === true || $sdf['status'] == 'progress') ? $sdf['status'] : 'failed';
		$data['status'] = $sdf['status'];
		
		$filter = array(
			'payment_id' => $sdf['payment_id'],
			'status|noequal' => 'succ',
			'status|noequal' => 'progress',
		);
		$is_save = $objPayments->update($data, $filter);
		
		if ($is_save)
		{
			// 防止重复充值
			if ($objPayments->db->affect_row())
				return true;
			else
				return false;
		}
		else
		{
			$msg = app::get('ectools')->_('支付单修改失败！');
			return false;
		}
	}
}