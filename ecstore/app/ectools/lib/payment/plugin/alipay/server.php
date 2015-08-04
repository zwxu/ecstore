<?php

 
/**
 * alipay notify 验证接口
 * 
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
class ectools_payment_plugin_alipay_server extends ectools_payment_app {
	
	/**
	 * 支付后返回后处理的事件的动作
	 * @params array - 所有返回的参数，包括POST和GET
	 * @return null
	 */
    public function callback(&$recv)
	{
        #键名与pay_setting中设置的一致
        $mer_id = $this->getConf('mer_id', substr(__CLASS__, 0, strrpos(__CLASS__, '_')));
        $mer_id = $mer_id == '' ? '2088002003028751' : $mer_id;
        $mer_key = $this->getConf('mer_key', substr(__CLASS__, 0, strrpos(__CLASS__, '_')));
        $mer_key = $mer_key=='' ? 'afsvq2mqwc7j0i69uzvukqexrzd0jq6h' : $mer_key;         

        if($this->is_return_vaild($recv,$mer_key)){
            $ret['payment_id'] = $recv['out_trade_no'];
			$ret['account'] = $mer_id;
			$ret['bank'] = app::get('ectools')->_('支付宝');
			$ret['pay_account'] = app::get('ectools')->_('付款帐号');
			$ret['currency'] = 'CNY';
			$ret['money'] = $recv['total_fee'];
			$ret['paycost'] = '0.000';
			$ret['cur_money'] = $recv['total_fee'];
            $ret['trade_no'] = $recv['trade_no'];
			$ret['t_payed'] = strtotime($recv['notify_time']) ? strtotime($recv['notify_time']) : time();
			$ret['pay_app_id'] = "alipay";
			$ret['pay_type'] = 'online';			
			$ret['memo'] = $recv['body'];
			
            switch($recv['trade_status']){
				// ipn方式回来
				case 'WAIT_BUYER_PAY':
					echo "success";
					$ret['status'] = 'ready';
					break;
                case 'TRADE_FINISHED':
					echo "success";
                    $ret['status'] = 'succ';
                    break;
                case 'TRADE_SUCCESS':
					echo "success";
                    $ret['status'] = 'succ';
                    break;
                case 'WAIT_SELLER_SEND_GOODS':
					echo 'success';
                    $ret['status'] = 'progress';
                    break;
           }

        }else{
            $ret['message'] = 'Invalid Sign';            
            $ret['status'] = 'invalid';
        }
		
		return $ret;
    }
    
    /**
     * 检验返回数据合法性
     * @param mixed $form 包含签名数据的数组
     * @param mixed $key 签名用到的私钥
     * @access private
     * @return boolean
     */
    public function is_return_vaild($form,$key)
	{
        ksort($form);
        foreach($form as $k=>$v){
            if($k!='sign'&&$k!='sign_type'){
                $signstr .= "&$k=$v";
            }
        }

        $signstr = ltrim($signstr,"&");
        $signstr = $signstr.$key;   

        if($form['sign']==md5($signstr)){
            return true;
        }
        #记录返回失败的情况	
		kernel::log(app::get('ectools')->_('支付单号：') . $form['out_trade_no'] . app::get('ectools')->_('签名验证不通过，请确认！')."\n");
		kernel::log(app::get('ectools')->_('本地产生的加密串：') . $signstr);
		kernel::log(app::get('ectools')->_('支付宝传递打过来的签名串：') . $form['sign']);
		$str_xml .= "<alipayform>";
        foreach ($form as $key=>$value)
        {
            $str_xml .= "<$key>" . $value . "</$key>";
        }
        $str_xml .= "</alipayform>";
         
        return false;
    }
    
}
