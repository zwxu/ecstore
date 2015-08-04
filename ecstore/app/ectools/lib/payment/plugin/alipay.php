<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
 * alipay担保交易支付网关（国内）
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class ectools_payment_plugin_alipay extends ectools_payment_app implements ectools_interface_payment_app {
	
	/**
	 * @var string 支付方式名称
	 */
    public $name = '支付宝支付';
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '支付宝支付接口';
     /**
     * @var string 支付方式key
     */
    public $app_key = 'alipay';
	/** 
	 * @var string 中心化统一的key 
	 */
	public $app_rpc_key = 'alipay';
	/**
	 * @var string 统一显示的名称
	 */
    public $display_name = '支付宝';
    /**
	 * @var string 货币名称
	 */
    public $curname = 'CNY';
    /**
	 * @var string 当前支付方式的版本号
	 */
    public $ver = '1.0';
    /**
     * @var string 当前支付方式所支持的平台
     */
    public $platform = 'ispc';
	
	/**
	 * @var array 扩展参数
	 */
	public $supportCurrency = array("CNY"=>"01");

    /**
	 * 构造方法
	 * @param null
	 * @return boolean
	 */
    public function __construct($app){
		parent::__construct($app);
		
        //$this->callback_url = $this->app->base_url(true)."/apps/".basename(dirname(__FILE__))."/".basename(__FILE__);
		$this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_alipay_server', 'callback');
		if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->notify_url, $matches))
		{
			$this->notify_url = str_replace('http://','',$this->notify_url);
			$this->notify_url = preg_replace("|/+|","/", $this->notify_url);
			$this->notify_url = "http://" . $this->notify_url;
		}
		else
		{
			$this->notify_url = str_replace('https://','',$this->notify_url);
			$this->notify_url = preg_replace("|/+|","/", $this->notify_url);
			$this->notify_url = "https://" . $this->notify_url;
		}
		$this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_alipay', 'callback');
		if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->callback_url, $matches))
		{
			$this->callback_url = str_replace('http://','',$this->callback_url);
			$this->callback_url = preg_replace("|/+|","/", $this->callback_url);
			$this->callback_url = "http://" . $this->callback_url;
		}
		else
		{
			$this->callback_url = str_replace('https://','',$this->callback_url);
			$this->callback_url = preg_replace("|/+|","/", $this->callback_url);
			$this->callback_url = "https://" . $this->callback_url;
		}
        //$this->submit_url = 'https://www.alipay.com/cooperate/gateway.do?_input_charset=utf-8';
        //ajx  按照相应要求请求接口网关改为一下地址
        $this->submit_url = 'https://mapi.alipay.com/gateway.do?_input_charset=utf-8';
        $this->submit_method = 'POST';
        $this->submit_charset = 'utf-8';
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function admin_intro(){
        $regIp = isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:$_SERVER['HTTP_HOST'];
        return '<img src="' . $this->app->res_url . '/payments/images/ALIPAY.gif"><br /><b style="font-family:verdana;font-size:13px;padding:3px;color:#000"><br>ShopEx联合支付宝推出优惠套餐：无预付/年费，单笔费率低至0.7%-1.2%，无流量限制。</b><div style="padding:10px 0 0 388px"><a  href="javascript:void(0)" onclick="document.ALIPAYFORM.submit();"><img src="' . $this->app->res_url . '/payments/images/alipaysq.png"></a></div><div>如果您已经和支付宝签约了其他套餐，同样可以点击上面申请按钮重新签约，即可享受新的套餐。<br>如果不需要更换套餐，请将签约合作者身份ID等信息在下面填写即可，<a href="http://www.shopex.cn/help/ShopEx48/help_shopex48-1235733634-11323.html" target="_blank">点击这里查看使用帮助</a><form name="ALIPAYFORM" method="GET" action="http://top.shopex.cn/recordpayagent.php" target="_blank"><input type="hidden" name="postmethod" value="GET"><input type="hidden" name="payagentname" value="支付宝"><input type="hidden" name="payagentkey" value="ALIPAY"><input type="hidden" name="market_type" value="from_agent_contract"><input type="hidden" name="customer_external_id" value="C433530444855584111X"><input type="hidden" name="pro_codes" value="6AECD60F4D75A7FB"><input type="hidden" name="regIp" value="'.$regIp.'"><input type="hidden" name="domain" value="'.$this->app->base_url(true).'"></form></div>';
    }

     /**
     * 后台配置参数设置
     * @param null
     * @return array 配置参数列表
     */
    public function setting(){
        return array(
                    'pay_name'=>array(
                        'title'=>app::get('ectools')->_('支付方式名称'),
                        'type'=>'string',
						'validate_type' => 'required',
                    ),
                    'mer_id'=>array(
                        'title'=>app::get('ectools')->_('合作者身份(parterID)'),
                        'type'=>'string',
						'validate_type' => 'required',
                    ),
                    'mer_key'=>array(
                        'title'=>app::get('ectools')->_('交易安全校验码(key)'),
                        'type'=>'string',
						'validate_type' => 'required',
                    ),
                    'support_cur'=>array(
                        'title'=>app::get('ectools')->_('支持币种'),
                        'type'=>'text hidden cur',
						'options'=>$this->arrayCurrencyOptions,
                    ),
                    'real_method'=>array(
                        'title'=>app::get('ectools')->_('选择接口类型'),
                        'type'=>'select',
                        'options'=>array('0'=>app::get('ectools')->_('使用标准双接口'),'2'=>app::get('ectools')->_('使用担保交易接口'),'1'=>app::get('ectools')->_('使用即时到帐交易接口'))
                    ),
                    'pay_fee'=>array(
                        'title'=>app::get('ectools')->_('交易费率'),
                        'type'=>'pecentage',
						'validate_type' => 'number',
                    ),
                    'pay_desc'=>array(
                        'title'=>app::get('ectools')->_('描述'),
                        'type'=>'string',
						'includeBase' => true,
                    ),
					'pay_type'=>array(
						 'title'=>app::get('ectools')->_('支付类型(是否在线支付)'),
						 'type'=>'hidden',
						 'name' => 'pay_type',
					),
					'status'=>array(
						'title'=>app::get('ectools')->_('是否开启此支付方式'),
						'type'=>'radio',
						'options'=>array('false'=>app::get('ectools')->_('否'),'true'=>app::get('ectools')->_('是')),
						'name' => 'status',
					),
                );
    }
	/**
     * 前台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function intro(){
        return app::get('ectools')->_('支付宝（中国）网络技术有限公司是国内领先的独立第三方支付平台，由阿里巴巴集团创办。支付宝致力于为中国电子商务提供“简单、安全、快速”的在线支付解决方案。').'
<a target="_blank" href="https://www.alipay.com/static/utoj/utojindex.htm">'.app::get('ectools')->_('如何使用支付宝支付？').'</a>';
    }
	
	/**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment)
	{
        $mer_id = $this->getConf('mer_id', __CLASS__);
        $mer_id = $mer_id == '' ? '2088002003028751' : $mer_id;
        $mer_key = $this->getConf('mer_key', __CLASS__);
        $mer_key = $mer_key=='' ? 'afsvq2mqwc7j0i69uzvukqexrzd0jq6h' : $mer_key;      
  
        $subject = $payment['account'].$payment['payment_id'];
        $subject = str_replace("'",'`',trim($subject));
        $subject = str_replace('"','`',$subject);
        $orderDetail = str_replace("'",'`',trim($orderDetail));
        $orderDetail = str_replace('"','`',$orderDetail);

        $virtual_method = $this->getConf('virtual_method', __CLASS__);#$config['virtual_method'];
        $real_method = $this->getConf('real_method', __CLASS__);#$config['real_method'];
		
        switch ($real_method){
            case '0': 
                $this->add_field('service', 'trade_create_by_buyer');#form['service'] = 'trade_create_by_buyer';
                break;
            case '1':
                $this->add_field('service', 'create_direct_pay_by_user');#$form['service'] = 'create_direct_pay_by_user';
                break;
            case '2':
                $this->add_field('service', 'create_partner_trade_by_buyer');#$form['service'] = 'create_partner_trade_by_buyer';
                break;
        }

        $this->add_field('logistics_type','POST');
        $this->add_field('logistics_payment','BUYER_PAY');
        $this->add_field('logistics_fee','0.00');

        $this->add_field('agent','C433530444855584111X');
        $this->add_field('payment_type',1);
        $this->add_field('partner',$mer_id);
        $this->add_field('return_url',$this->callback_url);
        $this->add_field('notify_url',$this->notify_url);
		if (isset($payment['subject']) && $payment['subject'])
			$this->add_field('subject',$payment['subject']);
        else
			$this->add_field('subject',$subject);
		if (isset($payment['body']) && $payment['body'])
			$this->add_field('body',$payment['body']);
		else
			$this->add_field('body',app::get('ectools')->_('网店订单')); 
        $this->add_field('out_trade_no',$payment['payment_id']);
        $this->add_field('price',number_format($payment['cur_money'],2,".",""));
        $this->add_field('quantity',1);
        
        if(preg_match("/^\d{16}$/",$mer_id)){
            $this->add_field('seller_id',$mer_id);
        }else{
            $this->add_field('seller_email',$mer_id);
        }

        $this->add_field('buyer_msg', $payment['remark'] ? $payment['remark'] : app::get('ectools')->_('无留言'));
        $this->add_field('_input_charset','utf-8');
        $this->add_field('sign',$this->_get_mac($mer_key));
        $this->add_field('sign_type','MD5');

        unset($this->fields['_input_charset']);

        if($this->is_fields_valiad())
		{
			// Generate html and send payment.
            echo $this->get_html();exit;
        }
		else
		{
            return false;
        }
    }

    /**
	 * 校验方法
	 * @param null
	 * @return boolean
	 */
    public function is_fields_valiad(){
        return true;    
    }
	
	/**
	 * 支付后返回后处理的事件的动作
	 * @params array - 所有返回的参数，包括POST和GET
	 * @return null
	 */
    public function callback(&$recv)
	{
        #键名与pay_setting中设置的一致
        $mer_id = $this->getConf('mer_id', __CLASS__);
        $mer_id = $mer_id == '' ? '2088002003028751' : $mer_id;
        $mer_key = $this->getConf('mer_key', __CLASS__);
        $mer_key = $mer_key=='' ? 'afsvq2mqwc7j0i69uzvukqexrzd0jq6h' : $mer_key;         

        if($this->is_return_vaild($recv,$mer_key)){
        //if(1 == 1){
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
                case 'TRADE_FINISHED':
                    if($recv['is_success']=='T'){                        
                        $ret['status'] = 'succ';
                    }else{                        
                        $ret['status'] =  'failed';
                    }
                    break;
                case 'TRADE_SUCCESS':
                    if($recv['is_success']=='T'){                        
                        $ret['status'] = 'succ';
                    }else{                        
                        $ret['status'] =  'failed';
                    }
                    break;
                case 'WAIT_SELLER_SEND_GOODS':
                    if($recv['is_success']=='T'){                        
                        $ret['status'] = 'progress';
                    }else{                        
                        $ret['status'] = 'failed';
                    }
                    break;
                case 'TRADE_SUCCES':    //高级用户
                    if($recv['is_success']=='T'){
                        $ret['status'] = 'succ';
                    }else{
                        $ret['status'] = 'failed';
                    }
                    break;
           }

        }else{
            $message = 'Invalid Sign';            
            $ret['status'] = 'invalid';
        }

        //增加账户中心处理
        if($ret['status'] == 'succ'){ 
        	 $obj_pay_center = kernel::servicelist('financial_center_api');
        	 foreach($obj_pay_center as $service){
              if(method_exists($service,'PaymentExtend')) {
                 $service->PaymentExtend($ret['payment_id']);
              }
           }
        }
        //增加账户中心处理
		
		return $ret;
    }
	
	/**
	 * 生成支付表单 - 自动提交
	 * @params null
	 * @return null
	 */
    public function gen_form()
	{
      $tmp_form='<a href="javascript:void(0)" onclick="document.applyForm.submit();">'.app::get('ectools')->_('立即申请支付宝').'</a>';
      $tmp_form.="<form name='applyForm' method='".$this->submit_method."' action='" . $this->submit_url . "' target='_blank'>";
	  // 生成提交的hidden属性
      foreach($this->fields as $key => $val)
	  {
            $tmp_form.="<input type='hidden' name='".$key."' value='".$val."'>";
      }
	  
      $tmp_form.="</form>";
	  
      return $tmp_form;
    }


    /**
     * 生成签名
     * @param mixed $form 包含签名数据的数组
     * @param mixed $key 签名用到的私钥
     * @access private
     * @return string
     */
    public function _get_mac($key){
        ksort($this->fields);
        reset($this->fields);    
        $mac= "";
        foreach($this->fields as $k=>$v){
            $mac .= "&{$k}={$v}";
        }
        $mac = substr($mac,1);
        $mac = md5($mac.$key);  //验证信息 
        return $mac;
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
