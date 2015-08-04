<?php

 
/**
 * paypal notify 验证接口
 * 
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
class ectools_payment_plugin_paypal_server extends ectools_payment_app {
	
	/**
	 * 支付后返回后处理的事件的动作
	 * @params array - 所有返回的参数，包括POST和GET
	 * @return null
	 */
    public function callback(&$recv)
	{
		$mer_id = $this->getConf('mer_id', __CLASS__);
        $ret['payment_id'] = $paymentId = $recv['item_number'];
		$ret['account'] = $mer_id;
		$ret['bank'] = 'PayPal';
		$ret['pay_account'] = $recv['payer_id'];
		$ret['currency'] = $recv['mc_currency'];
		$ret['money'] = $recv['mc_gross'];
		$ret['paycost'] = '0.000';
		$ret['cur_money'] = $recv['mc_gross'];
		$ret['trade_no'] = $recv['txn_id'];
		$ret['t_payed'] = strtotime($recv['payment_date']);
		$ret['pay_app_id'] = "paypal";
		$ret['pay_type'] = 'online';
		$ret['memo'] = '';
		
        $money = $recv['mc_gross'];
		
		//从 PayPal 出读取 POST 信息同时添加变量„cmd‟
		$req = 'cmd=_notify-validate';
		foreach ($recv as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}
		//建议在此将接受到的信息记录到日志文件中以确认是否收到 IPN 信息
		//将信息 POST 回给 PayPal 进行验证
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type:application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length:" . strlen($req) ."\r\n\r\n";
		//在 Sandbox 情况下，设置：
		//$fp = fsockopen('ssl://www.sandbox.paypal.com',443,$errno,$errstr,30);
		$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
		//将 POST 变量记录在本地变量中
		//该付款明细所有变量可参考：
		//https://www.paypal.com/IntegrationCenter/ic_ipn-pdt-variable-reference.html
		$item_name = $recv['item_name'];
		$item_number = $recv['item_number'];
		$payment_status = $recv['payment_status'];
		$payment_amount = $recv['mc_gross'];
		$payment_currency = $recv['mc_currency'];
		$txn_id = $recv['txn_id'];
		$receiver_email = $recv['receiver_email'];
		$payer_email = $recv['payer_email'];
		
		//…
		//判断回复 POST 是否创建成功
		if (!$fp) 
		{
			//HTTP 错误
			$ret['status'] = 'error';
		}
		else 
		{
			//将回复 POST 信息写入 SOCKET 端口
			fputs ($fp, $header .$req);
			//开始接受 PayPal 对回复 POST 信息的认证信息
			while (!feof($fp)) 
			{
				$res = fgets ($fp, 1024);
				//已经通过认证
				if (strcmp ($res, "VERIFIED") == 0) 
				{
					//检查付款状态
					//检查 txn_id 是否已经处理过
					//检查 receiver_email 是否是您的 PayPal 账户中的 EMAIL 地址
					//检查付款金额和货币单位是否正确
					//处理这次付款，包括写数据库
					if ($recv['payment_status'] == "Completed" || $recv['payment_status'] == 'Processed')
						$succ="Y";
					else
						$succ="N";
					
					switch ($succ){
						//成功支付
						case "Y":
							$ret['status'] = 'succ';				
							break;
							//支付失败
						case "N":
							$ret['status'] = 'failed';
							break;
					}
				}
				else if (strcmp ($res, "INVALID") == 0) 
				{
					//未通过认证，有可能是编码错误或非法的 POST 信息
					$ret['status'] = 'invalid';
				}
			}
			fclose ($fp);
		}
		
		// Call to a function in bbc function.
		return $ret;
    }    
}
