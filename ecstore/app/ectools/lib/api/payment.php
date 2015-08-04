<?php

 
/**
 * 支付api接口，提供方法和外接通讯
 * 
 * @version 0.1
 * @package ectools.lib.api
 */
class ectools_api_payment
{
	/**
     * app object
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
        $this->objMath = kernel::single("ectools_math");
    }
	
    /**
     * 获取所有开启（激活）的支付方式
     * @param mixed 过滤条件
     * @return array 支付方式数组
     */
	public function get_all($sdf)
	{
		$arr_payments = array();
		$obj_payments_service_all = kernel::servicelist('ectools_payment.ectools_mdl_payment_cfgs');
		foreach ($obj_payments_service_all as $obj)
		{
			switch ($obj->app_key)
			{
				case 'offline':
					$payout_type = 'offline';
					break;
				case 'deposit':
					$payout_type = 'deposit';
					break;
				default:
					$payout_type = 'online';
					break;
			}
			$strPayment = $this->app->getConf(get_class($obj));
			$arrPaymnet = unserialize($strPayment);
			
			if (isset($arrPaymnet['status']) && $arrPaymnet['status'] == 'true')
			{
				$arr_payments[$obj->app_key] = array(
					'payout_type'=>$payout_type,
					'payment_name'=>(isset($arrPaymnet['setting']['pay_name']) && $arrPaymnet['setting']['pay_name']) ? $arrPaymnet['setting']['pay_name'] : $obj->display_name,
					'payment_id'=>(isset($obj->app_rpc_key) && $obj->app_rpc_key) ? $obj->app_rpc_key : $obj->app_key,
				);
			}
		}		
		
		return $arr_payments;
	}
}