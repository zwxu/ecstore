<?php

 
/**
 * 支付单创建的具体实现逻辑
 * 
 * @version 0.1
 * @package ectools.lib.payment
 */
class ectools_payment_create
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
	 * 支付单创建
	 * @param array sdf
	 * @param string message
	 * @return boolean success or failure
	 */
	public function generate(&$sdf, &$msg='')
	{
		// 创建订单是和中心的交互
		$is_payed = false;		            			
	  
		// 获得的支持变量的信息
		$objMath = kernel::single('ectools_math');		
		$payment_cfgs = $this->app->model('payment_cfgs');
		$arrPyMethod = $payment_cfgs->getPaymentInfo($sdf['pay_app_id']);            
		
		$class_name = "";
		$obj_app_plugins = kernel::servicelist("ectools_payment.ectools_mdl_payment_cfgs");
		foreach ($obj_app_plugins as $obj_app)
		{
			$app_class_name = get_class($obj_app);
			$arr_class_name = explode('_', $app_class_name);
			if (isset($arr_class_name[count($arr_class_name)-1]) && $arr_class_name[count($arr_class_name)-1])
			{
				if ($arr_class_name[count($arr_class_name)-1] == $sdf['pay_app_id'])
				{
					$pay_app_ins = $obj_app;
					$class_name = $app_class_name;
				}
			}
			else
			{
				if ($app_class_name == $sdf['pay_app_id'])
				{
					$pay_app_ins = $obj_app;
					$class_name = $app_class_name;
				}
			}
		}
		$strPaymnet = $this->app->getConf($class_name);
		$arrPayment = unserialize($strPaymnet);
		$objCur = $this->app->model('currency');
		$aCur = $objCur->getDefault();
		$objModelPay = $this->app->model('payments');
		
		$account = $sdf['shopName'] ? $sdf['shopName'] : $sdf['account'];
		$account = $account ? $account : $arrPyMethod['app_display_name'];
		$bank = ($arrPyMethod['app_key'] == 'deposit' || $arrPyMethod['app_key'] == 'offline') ? $this->app->getConf("system.shopname") : $arrPyMethod['app_display_name'];
		$bank = $sdf['bank'] ? $sdf['bank'] : $bank;
		
		if ($sdf['pay_object'] == 'order')
		{
			$currency = $sdf['currency'] ? $sdf['currency'] : $aCur['cur_code'];
			$money = $sdf['money'];
			$pay_fee = $arrPayment['setting']['pay_fee'];//支付费率        
			$paycost = $sdf['payinfo']['cost_payment'];
			//$cur_money = $objCur->get_cur_money($sdf['money'], $currency);
			$cur_money = $sdf['cur_money'];
		}
		else
		{
			$currency = $aCur['cur_code'];
			$money = $sdf['money'];
			$paycost = 0;
			$cur_money = $money;
		}         
		
		$pay_type = ($arrPyMethod['app_pay_type'] == 'true') ? 'online' : 'offline';
		$pay_type = $sdf['pay_type'] ? $sdf['pay_type'] : $pay_type;	
		
		$time = time();
		
          
          $pay_account = $sdf['pay_account'];
          if( !$pay_account )
            {
              if( $sdf['member_id'] ){
                $pam_account = app::get('pam')->model('account');
                if($ret = $pam_account->getList('login_name',array('account_id'=> $sdf['member_id'])) ){
                  $pay_account =  $ret[0]['login_name'];
                }
              }
              else {
                $pay_account = app::get('ectools')->_('非会员顾客');
              }
            }
		$paymentArr = array(
			'payment_id' => $sdf['payment_id'],
			'account' => $account ? $account : $bank,
			'member_id' => ($sdf['member_id']) ? $sdf['member_id'] : '0',
			'bank' => $bank,
			'pay_account' => $pay_account,
			'currency' => $currency,
			'money' => $money,
			'paycost' => $paycost,
			'cur_money' => $cur_money,
			'pay_type' => $pay_type,
			'pay_app_id' => $sdf['pay_app_id'],
			'pay_name' => $arrPyMethod['app_display_name'],
			'pay_ver' => $arrPyMethod['app_version'],
			'op_id' => ($sdf['op_id']) ? $sdf['op_id'] : ($sdf['member_id'] ? $sdf['member_id'] : '0'),
			'ip' => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['HTTP_HOST'],
			't_begin' => $time,
			't_payed' => $time,
			't_confirm' => $time,
			'status' => $sdf['status'],
			'trade_no' => '',
			'memo' => (!$sdf['memo']) ? '' : $sdf['memo'],
			'return_url' => $sdf['return_url'],
			'orders' => array(
				array(
					'rel_id' => $sdf['rel_id'],
					'bill_type' => 'payments',
					'pay_object' => $sdf['pay_object'],
					'bill_id' => $sdf['payment_id'],
					'money' => $money,
				)
			)
		);
		
		$sdf = $paymentArr;
		
		$is_save = $objModelPay->save($paymentArr);
		
		if ($is_save)
		{
			return true;
		}
		else
		{
			$msg = app::get('ectools')->_('支付单生成失败！');
			return false;
		}
	}

    /**
	 * 合并支付单创建
	 * @param array sdf
	 * @param string message
	 * @return boolean success or failure
	 */
	public function all_generate(&$sdf, &$msg='')
	{
		// 创建订单是和中心的交互
		$is_payed = false;		            			
	  
		// 获得的支持变量的信息
		$objMath = kernel::single('ectools_math');		
		$payment_cfgs = $this->app->model('payment_cfgs');
		$arrPyMethod = $payment_cfgs->getPaymentInfo($sdf['pay_app_id']);            
		
		$class_name = "";
		$obj_app_plugins = kernel::servicelist("ectools_payment.ectools_mdl_payment_cfgs");
		foreach ($obj_app_plugins as $obj_app)
		{
			$app_class_name = get_class($obj_app);
			$arr_class_name = explode('_', $app_class_name);
			if (isset($arr_class_name[count($arr_class_name)-1]) && $arr_class_name[count($arr_class_name)-1])
			{
				if ($arr_class_name[count($arr_class_name)-1] == $sdf['pay_app_id'])
				{
					$pay_app_ins = $obj_app;
					$class_name = $app_class_name;
				}
			}
			else
			{
				if ($app_class_name == $sdf['pay_app_id'])
				{
					$pay_app_ins = $obj_app;
					$class_name = $app_class_name;
				}
			}
		}
		$strPaymnet = $this->app->getConf($class_name);
		$arrPayment = unserialize($strPaymnet);
		$objCur = $this->app->model('currency');
		$aCur = $objCur->getDefault();
		$objModelPay = $this->app->model('payments');
		
		$account = $sdf['shopName'] ? $sdf['shopName'] : $sdf['account'];
		$account = $account ? $account : $arrPyMethod['app_display_name'];
		$bank = ($arrPyMethod['app_key'] == 'deposit' || $arrPyMethod['app_key'] == 'offline') ? $this->app->getConf("system.shopname") : $arrPyMethod['app_display_name'];
		$bank = $sdf['bank'] ? $sdf['bank'] : $bank;
		
		if ($sdf['pay_object'] == 'order')
		{
			$currency = $sdf['currency'] ? $sdf['currency'] : $aCur['cur_code'];
			$money = $sdf['money'];
			$pay_fee = $arrPayment['setting']['pay_fee'];//支付费率        
			$paycost = $sdf['payinfo']['cost_payment'];
			//$cur_money = $objCur->get_cur_money($sdf['money'], $currency);
			$cur_money = $sdf['cur_money'];
		}
		else
		{
			$currency = $aCur['cur_code'];
			$money = $sdf['money'];
			$paycost = 0;
			$cur_money = $money;
		}         
		
		$pay_type = ($arrPyMethod['app_pay_type'] == 'true') ? 'online' : 'offline';
		$pay_type = $sdf['pay_type'] ? $sdf['pay_type'] : $pay_type;	
		
		$time = time();
		
          
          $pay_account = $sdf['pay_account'];
          if( !$pay_account )
            {
              if( $sdf['member_id'] ){
                $pam_account = app::get('pam')->model('account');
                if($ret = $pam_account->getList('login_name',array('account_id'=> $sdf['member_id'])) ){
                  $pay_account =  $ret[0]['login_name'];
                }
              }
              else {
                $pay_account = app::get('ectools')->_('非会员顾客');
              }
            }
		$paymentArr = array(
			'payment_id' => $sdf['payment_id'],
            'merge_payment_id' => $sdf['merge_payment_id'],
			'account' => $account ? $account : $bank,
			'member_id' => ($sdf['member_id']) ? $sdf['member_id'] : '0',
			'bank' => $bank,
			'pay_account' => $pay_account,
			'currency' => $currency,
			'money' => $money,
			'paycost' => $paycost,
			'cur_money' => $cur_money,
			'pay_type' => $pay_type,
			'pay_app_id' => $sdf['pay_app_id'],
			'pay_name' => $arrPyMethod['app_display_name'],
			'pay_ver' => $arrPyMethod['app_version'],
			'op_id' => ($sdf['op_id']) ? $sdf['op_id'] : ($sdf['member_id'] ? $sdf['member_id'] : '0'),
			'ip' => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['HTTP_HOST'],
			't_begin' => $time,
			't_payed' => $time,
			't_confirm' => $time,
			'status' => $sdf['status'],
			'trade_no' => '',
			'memo' => (!$sdf['memo']) ? '' : $sdf['memo'],
			'return_url' => $sdf['return_url'],
			'orders' => array(
				array(
					'rel_id' => $sdf['rel_id'],
					'bill_type' => 'payments',
					'pay_object' => $sdf['pay_object'],
					'bill_id' => $sdf['payment_id'],
					'money' => $money,
				)
			)
		);
		
		$sdf = $paymentArr;
		
		$is_save = $objModelPay->save($paymentArr);
		
		if ($is_save)
		{
			return true;
		}
		else
		{
			$msg = app::get('ectools')->_('支付单生成失败！');
			return false;
		}
	}
}