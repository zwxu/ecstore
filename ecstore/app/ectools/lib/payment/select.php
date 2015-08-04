<?php

 
/**
 * 支付方式的选择
 * 
 * @version 0.1
 * @package ectools.lib.payment
 */
class ectools_payment_select
{	
	/**
	 * 支付方式的过滤
	 * @param object controller
	 * @param array sdf construct
	 * @param string member id
	 * @param boolean backend or not
	 * @return string 结果html
	 */
	public function select_pay_method(&$controller, &$sdf, $member_id=0, $is_backend=false){
		$payment_cfg = app::get('ectools')->model('payment_cfgs');
		$currency = app::get('ectools')->model('currency');
		$payments = array();
		$arrPayments = $payment_cfg->getList('*', array('status' => 'true', 'platform'=>'ispc', 'is_frontend' => true));
		$arrDefCurrency = $currency->getDefault();
		if (!$sdf['cur_code'])
		{			
			$strDefCurrency = $arrDefCurrency['cur_code'];
		}
		else
			$strDefCurrency = $sdf['cur_code'];
		$currency = $sdf['cur'] ? $sdf['cur'] : $strDefCurrency;
		$def_payments = $sdf['def_payment'] ? $sdf['def_payment'] : '';
		$is_def_payment_match = false;
		$shop_def_currency = $arrDefCurrency['cur_code'];
		
		if ($def_payments)
		{
			$controller->pagedata['arr_def_payment'] = $payment_cfg->getPaymentInfo($def_payments);
			if ($def_payments == '-1')
				$is_def_payment_match = true;
		}
		
		if (!$member_id)
		{
			if (!$is_backend)
			{
				$arr_members = $controller->get_current_member();
				$member_id = $arr_members['member_id'];
			}
		}		
		
		if ($arrPayments)
		{
			foreach($arrPayments as $key=>$payment)
			{
				if (!$member_id)
				{
					if (trim($payment['app_id']) == 'deposit')
					{
						unset($arrPayments[$key]);
						continue;
					}
				}

				if (trim($payment['app_id']) == 'deposit')
				{
					if ($shop_def_currency == $currency)
					{
						if (isset($controller->pagedata['arr_def_payment']) && $controller->pagedata['arr_def_payment'])
						{
							$arr_def_payment = $controller->pagedata['arr_def_payment'];
							if ($arr_def_payment['app_id'] == $payment['app_id'])
								$is_def_payment_match = true;
						}
						$payments[] = $payment;
					}
					continue;
				}
				
				if (trim($payment['app_id']) == 'offline')
				{
					if (isset($controller->pagedata['arr_def_payment']) && $controller->pagedata['arr_def_payment'])
					{
						$arr_def_payment = $controller->pagedata['arr_def_payment'];
						if ($arr_def_payment['app_id'] == $payment['app_id'])
							$is_def_payment_match = true;
					}
					$payments[] = $payment;					
					continue;
				}
				
				if ($payment['supportCurrency'] && is_array($payment['supportCurrency']))
				{
					if (array_key_exists($currency, $payment['supportCurrency']))
					{
						if (isset($controller->pagedata['arr_def_payment']) && $controller->pagedata['arr_def_payment'])
						{
							$arr_def_payment = $controller->pagedata['arr_def_payment'];
							if ($arr_def_payment['app_id'] == $payment['app_id'])
								$is_def_payment_match = true;
						}
						$payments[] = $payment;
					}							
				}
			}
			
			$controller->pagedata['def_payments'] = $def_payments;
			$controller->pagedata['arr_def_payment'] = (isset($_COOKIE['purchase']['payment']) && $_COOKIE['purchase']['payment']) ? unserialize($_COOKIE['purchase']['payment']) : '';
			$controller->pagedata['is_def_payment_match'] = ($is_def_payment_match) ? 1 : 0;
			$controller->pagedata['payments'] = &$payments;
            $controller->pagedata['payments_num'] = count($payments);
			$controller->pagedata['order'] = &$sdf;
			
			if (!$is_backend)
			{
				$str_html = $controller->fetch("site/common/paymethod.html",$controller->pagedata['app_id']);
				$obj_ajax_View_help = kernel::service('ectools_payment_ajax_html');
				if (!$obj_ajax_View_help)
					return $str_html;
				else
					return $obj_ajax_View_help->get_html($str_html,'ectools_payment_select','select_pay_method');
			}
			else
			{
				$str_html = $controller->fetch("admin/order/paymethod.html",$controller->pagedata['app_id']);
				$obj_ajax_View_help = kernel::service('ectools_payment_ajax_html');
				if (!$obj_ajax_View_help)
					return $str_html;
				else
					return $obj_ajax_View_help->get_html($str_html,'ectools_payment_select','select_pay_method');
			}

		}
	}
	
	public function change_def_payment(&$controller, $pay_app_id='alipay')
	{
		$payment_cfg = app::get('ectools')->model('payment_cfgs');
		if ($pay_app_id)
		{
			if ($pay_app_id == -1)
				$pay_app_id = app::get('ectools')->_('货到付款');
				
			$controller->pagedata['arr_def_payment'] = $payment_cfg->getPaymentInfo($pay_app_id);
			$str_html = $controller->fetch("site/common/paymethod_def_info.html");
			$obj_ajax_View_help = kernel::service('ectools_payment_ajax_html');
			if (!$obj_ajax_View_help)
				return $str_html;
			else
				return $obj_ajax_View_help->get_html($str_html,'ectools_payment_select','change_def_payment');
		}
	}
}
