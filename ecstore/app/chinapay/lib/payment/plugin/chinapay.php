<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

require_once('netpayclient.php');
final class chinapay_payment_plugin_chinapay extends ectools_payment_app implements ectools_interface_payment_app {

    public $name = '银联在线支付ChinaPay';
    public $app_name = '银联在线支付ChinaPay接口';
    public $app_key = 'chinapay';
	/** 中心化统一的key **/
	public $app_rpc_key = 'chinapay';
    public $display_name = '银联在线支付ChinaPay';
    public $curname = 'CNY';
    public $ver = '1.0';
    /**
     * @var string 当前支付方式所支持的平台
     */
    public $platform = 'ispc';

	// 扩展参数
	public $supportCurrency = array("CNY"=>"156");

    //构造支付接口基本信息
    public function __construct($app)
	{
		parent::__construct($app);
        $this->callback_url  = app::get('site')->router()->gen_url(array('app'=>'chinapay','ctl'=>'site_request','act'=>'index','full'=>1));
        $this->servercallback_url = app::get('site')->router()->gen_url(array('app'=>'chinapay','ctl'=>'site_request','act'=>'serverCallback','full'=>1));
        
        $this->submit_url = 'https://payment.ChinaPay.com/pay/TransGet';
        if(defined('CHINAPAY')){
		    $this->submit_url = "http://payment-test.ChinaPay.com/pay/TransGet";//测试
        }
        $this->submit_method = 'POST';
        $this->submit_charset = 'utf-8';
    }

    //显示支付接口表单基本信息
    public function admin_intro()
	{
        return app::get('chinapay')->_('银联在线支付（ChinaPay）是银联电子支付服务有限公司主要从事以互联网等新兴渠道为基础的网上支付。');
    }

    //显示支付接口表单选项设置
    public function setting()
	{
        return array(
				'pay_name'=>array(
					'title'=>app::get('ectools')->_('支付方式名称'),
					'type'=>'string',
					'validate_type' => 'required',
				),
				'mer_id'=>array(
					'title'=>app::get('chinapay')->_('客户号'),
					'type'=>'string',
					'validate_type' => 'required',
				),
				'pub_Pk'=>array(
                    'title'=>app::get('chinapay')->_('公钥文件'),
                    'type'=>'file',
                ),
				'mer_key'=>array(
					'title'=>app::get('chinapay')->_('私钥文件'),
					'type'=>'file',
				),
				'pay_fee'=>array(
					'title'=>app::get('ectools')->_('交易费率'),
					'type'=>'pecentage',
					'validate_type' => 'number',
				),
				'support_cur'=>array(
					'title'=>app::get('ectools')->_('支持币种'),
					'type'=>'text hidden cur',
					'options'=>$this->arrayCurrencyOptions,
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

    public function intro()
	{
        return app::get('chinapay')->_('银联在线支付（ChinaPay）是银联电子支付服务有限公司主要从事以互联网等新兴渠道为基础的网上支付。');
    }

	//支付接口表单提交方式
    public function dopay($payment)
	{
		$mer_id = $this->getConf('mer_id', __CLASS__);

        $TransType = '0001';
        if (!$payment['t_begin'])
            $payment['t_begin'] = time();
        $ordId = $this->intString(substr($mer_id, -5) . substr(date("YmdHis",$payment['t_begin']), -7), 16);
        $payment['cur_money'] = $this->intString($payment['cur_money']*100, 12);

		$arr_validate_data = array(
			'merid' => $mer_id,
			'orderno' => $ordId,
			'amount' => $payment['cur_money'],
			'currencycode' => $this->supportCurrency[$payment['currency']],
			'transdate' => date("Ymd", $payment['t_begin']),
			'transtype' => $TransType,
            'payment_id'=>$payment['payment_id'],
		);
		$chkvalue = $this->_get_mac($arr_validate_data, 'sign');

        switch ($chkvalue){
            case '-100':
                $errinfo='环境变量"NPCDIR"未设置';
                break;
            case '-101':
                $errinfo='商户密钥文件不存在或无法打开';
                break;
            case '-102':
                $errinfo='密钥文件格式错误';
                break;
            case '-103':
                $errinfo='秘钥商户号和用于签名的商户号不一致';
                break;
            case '-130':
                $errinfo='用于签名的字符串长度为空';
                break;
            case '-111':
                $errinfo='没有设置秘钥文件路径，或者没有设置“NPCDIR”环境变量';
                break;
            default:
                break;
        }

        if ($errinfo)
		{
            header("Content-Type:text/html;charset=utf-8");
            echo $errinfo;
            exit;
        }

		$this->add_field('MerId', $mer_id);
		$this->add_field('OrdId', $ordId);
		$this->add_field('TransAmt', $payment['cur_money']);
		$this->add_field('CuryId', '156');
		$this->add_field('TransDate', date("Ymd", $payment['t_begin']));
		$this->add_field('TransType', $TransType);
		$this->add_field('Version', '20070129');
		$this->add_field('BgRetUrl', $this->servercallback_url);
		$this->add_field('PageRetUrl', $this->callback_url);
		$this->add_field('GateId', '');
		$this->add_field('Priv1', $payment['payment_id']); //todo:需要在订单生成的时候做转换，主要用于外币支付时,紧做显示用不参与交易
		$this->add_field('ChkValue', $chkvalue);

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

    //验证提交表单参数有效性
    public function is_fields_valiad()
	{
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
        $ret['payment_id'] = $recv['Priv1'];
		$ret['account'] = $mer_id;
		$ret['bank'] = app::get('chinapay')->_('银联在线支付ChinaPay');
		$ret['pay_account'] = app::get('ectools')->_('付款帐号');
		$ret['currency'] = array_search($recv["currencycode"], $this->supportCurrency);
		$ret['money'] = intval($recv['amount'])/100;;
		$ret['paycost'] = '0.000';
		$ret['cur_money'] = intval($recv['amount'])/100;
		$ret['trade_no'] = '';
		$ret['t_payed'] = time();
		$ret['pay_app_id'] = "chinapay";
		$ret['pay_type'] = 'online';
		$ret['memo'] = '';

		if ($this->is_return_vaild($recv, ''))
		{
			if ($recv['status']=="1001")
			{
                $message = "支付成功！";
                $ret['status'] = 'succ';
            }
            else
			{
                $message = "支付失败！";
                $ret['status'] = 'failed';
            }
		}
		else
		{
			$message = "验证签名错误！";
            $ret['status'] = 'invalid';
		}

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
     * 生成检查签名
     * @param mixed $form 包含签名数据的数组
	 * @param string $method 生成用途
     * @access private
     * @return string
     */
    private function _get_mac($data, $method='sign')
	{
		$MerPrk = $this->getConf('mer_key', __CLASS__);
        $PubPk = $this->getConf('pub_Pk', __CLASS__);

		if (strtoupper(substr(PHP_OS,0,3))=="WIN")
		{
            //$chinapay = new COM('CPNPC.NPC');

            if (file_exists(DATA_DIR . '/cert/payment_plugin_chinapay/' . $MerPrk)&&file_exists(DATA_DIR . '/cert/payment_plugin_chinapay/' . $PubPk)){
                //$chinapay->setMerKeyFile(DATA_DIR . '/cert/payment_plugin_chinapay/' . $MerPrk);
                //$chinapay->setPubKeyFile(DATA_DIR . '/cert/payment_plugin_chinapay/' . $PubPk);
				buildKey(DATA_DIR . '/cert/payment_plugin_chinapay/' . $MerPrk);
            }
            if ($method=='sign')
			{
				$res = $this->_get_mac_sign($data, $chinapay);

			}
			else
			{
				$res = $this->_get_mac_check($data, $chinapay);
			}

        }
        else
		{
            if (file_exists(DATA_DIR . '/cert/payment_plugin_chinapay/' . $MerPrk)&&file_exists(DATA_DIR . '/cert/payment_plugin_chinapay/' . $PubPk))
			{
                //setMerKeyFile(DATA_DIR . '/cert/payment_plugin_chinapay/' . $MerPrk);
                //setPubKeyFile(DATA_DIR . '/cert/payment_plugin_chinapay/' . $PubPk);
				buildKey(DATA_DIR . '/cert/payment_plugin_chinapay/' . $MerPrk);
            }

			if ($method=='sign')
				$res = $this->_get_mac_sign($data);
			else
				$res = $this->_get_mac_check($data);
        }
		return $res;
    }

    /**
     * 生成检查签名
     * @param mixed $form 包含签名数据的数组
	 * @param mixed $chinapay com组件对象
     * @access private
     * @return string
     */
    private function _get_mac_check($data, $chinapay=null)
	{
		if (is_null($chinapay))
		{
			$res = verifyTransResponse($data['merid'], $data['orderno'], $data['amount'], $data['currencycode'], $data['transdate'], $data['transtype'], $data['status'], $data['checkvalue']);
		}
		else
		{
			$res = $chinapay->check($data['merid'], $data['orderno'], $data['amount'], $data['currencycode'], $data['transdate'], $data['transtype'], $data['status'], $data['checkvalue']);
		}

		return $res;
    }

	/**
	 * 生成发送验证签名
	 * @param mixed $form 包含签名数据的数组
	 * @param mixed $chinapay com组件对象
     * @access private
     * @return string
	 */
	private function _get_mac_sign($data, $chinapay=null)
	{

		//商户号，订单号，交易金额，货币代码，交易日期，交易类型
		if (is_null($chinapay))
		{
			$chkvalue = sign($data['merid'].$data['orderno'].$data['amount'].$data['currencycode'].$data['transdate'].$data['transtype'].$data['payment_id']);
		}
		else
		{
			$chkvalue = sign($data['merid'].$data['orderno'].$data['amount'].$data['currencycode'].$data['transdate'].$data['transtype'].$data['payment_id']);
		}

		return $chkvalue;
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
		$res = $this->_get_mac($form, 'check');
		if ($res == '0')
			return true;

        #记录返回失败的情况
        if (SHOP_DEVELOPER)
		{
			kernel::log($signstr);
        }
        return false;
    }

	/**
	 * 截取相应长度和本身字符串长度的差额对应的字符串
	 * @param string 被截取字符串
	 * @param int 长度
	 */
	private function intString($intvalue,$len)
	{
        $intstr = strval($intvalue);
        for ($i = 1; $i <= $len-strlen($intstr); $i++)
		{
            $tmpstr .= "0";
        }

        return $tmpstr . $intstr;
    }
}
