<?php

/**
 * 外部支付方式网关统一的接口方法
 * 
 * @version 0.1
 * @package ectools.lib.payment
 */
class ectools_payment_app
{
	/**
	 * @var attribute array
	 */
	protected $fields = array();
	/**
	 * @var app object.
	 */
	protected $app;
	/**
	 * @var string callback_url
	 */
	protected $callback_url;
	/**
	 * @var string submit_url
	 */
	protected $submit_url;
	/**
	 * @var string submit_method
	 */
	protected $submit_method;
	/**
	 * @var string submit_charset
	 */
	protected $submit_charset;
	/**
	 * @var array 支持的货币
	 */
	protected $arrayCurrencyOptions = array();

	/**
	 * 构造方法
	 * @params string - app id
	 * @return null
	 */
	public function __construct($app)
	{
		$this->app = $app;
		$this->arrayCurrencyOptions = array(
			'1'=>app::get('ectools')->_('人民币'),
			'2'=>app::get('ectools')->_('其他'),
			'3'=>app::get('ectools')->_('商店默认货币'),
			'4'=>app::get('ectools')->_('人民币与其他币种'),
			'5'=>app::get('ectools')->_('新台币'),
		);
	}

	/**
	 * 设置属性
	 * @params string key
	 * @params string value
	 * @return null
	 */
	protected function add_field($key, $value='')
	{
		if (!$key)
		{
			trigger_error(app::get('ectools')->_("Key不能为空！"), E_USER_ERROR);exit;
		}

		$this->fields[$key] = $value;
	}

	/**
	 * 得到配置参数
	 * @params string key
	 * @payment api interface class name
	 */
	protected function getConf($key, $pkey)
	{
        $val = app::get('ectools')->getConf($pkey);
        $val = unserialize($val);

		return $val['setting'][$key];
	}

	/**
	 * 生成支付方式提交的表单的请求
	 * @params null
	 * @return string
	 */
	protected function get_html()
	{
		// 简单的form的自动提交的代码。
		header("Content-Type: text/html;charset=".$this->submit_charset);
		$strHtml ="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
		<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-US\" lang=\"en-US\" dir=\"ltr\">
		<head>
		</head><body><div>Redirecting...</div>";
		$strHtml .= '<form action="' . $this->submit_url . '" method="' . $this->submit_method . '" name="pay_form" id="pay_form">';

		// Generate all the hidden field.
		foreach ($this->fields as $key=>$value)
		{
			$strHtml .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
		}

		$strHtml .= '<input type="submit" name="btn_purchase" value="'.app::get('ectools')->_('购买').'" style="display:none;" />';
		$strHtml .= '</form><script type="text/javascript">
						window.onload=function(){
							document.getElementById("pay_form").submit();
						}
					</script>';
		$strHtml .= '</body></html>';
		return $strHtml;
	}
}