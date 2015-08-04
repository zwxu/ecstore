<?php

 
/**
 * 预存款支付具体实现
 * 
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class ectools_payment_plugin_deposit extends ectools_payment_app implements ectools_interface_payment_app{
	/**
	 * @var string 支付方式名称
	 */
	public $name = '预存款支付';
	/**
     * @var string 支付方式接口名称
     */
    public $app_name = '预存款支付接口';
    /**
     * @var string 支付方式key
     */
    public $app_key = 'deposit';
	/** 
	 * @var string 中心化统一的key 
	 */
	public $app_rpc_key = 'deposit';
	/**
	 * @var string 统一显示的名称
	 */
    public $display_name = '预存款支付';
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
     * 构造方法
     * @param object 传递应用的app
     * @return null
     */
    public function __construct($app){
		parent::__construct($app);
		
        //$this->callback_url = $this->app->base_url(true)."/apps/".basename(dirname(__FILE__))."/".basename(__FILE__);
		$this->callback_url = "";
        $this->submit_url = '';
        $this->submit_method = 'POST';
        $this->submit_charset = 'utf-8';
    }
	
	/**
	 * 显示支付接口表单基本信息
	 * @params null
	 * @return string - description include account.
	 */
    public function admin_intro(){
        return app::get('ectools')->_('预存款支付后台自定义描述');
    }
    
	/**
     * 前台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
	public function intro(){
        return app::get('ectools')->_('预存款支付自定义描述');
    }
	
	/**
	 * 显示支付接口表单选项设置
	 * @params null
	 * @return array - 字段参数
	 */
    public function setting(){
        return array(
                    'pay_name'=>array(
                        'title'=>app::get('ectools')->_('支付方式名称'),
                        'type'=>'string'
                    ),
					'support_cur'=>array(
                        'title'=>app::get('ectools')->_('支持币种'),
                        'type'=>'text hidden',
						'options'=>$this->arrayCurrencyOptions,
                    ),
                    'pay_desc'=>array(
						'title'=>app::get('ectools')->_('描述'),
                        'type'=>'html',
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
     * 提交支付信息的接口
     * 支付接口表单提交方式
     * @param array 提交信息的数组
     * @return mixed false or null
     */
	public function dopay($payment)
	{
		
	}
	
	/**
     * 提交支付信息的接口
     * 无需表单正常支付
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function do_payment($payment, &$msg)
	{        
		$obj_pay_lists = kernel::servicelist("order.pay_finish");
		//$obj_pay = kernel::single("ectools_pay");
		$is_payed = 'succ';
		
		foreach ($obj_pay_lists as $order_pay_service_object)
		{
			$class_name = get_class($order_pay_service_object);
			//if (strpos($class_name, $this->app->app_id . '_') !== false)
			//{
				$objAdvance = $this->app->model('member_advance');
				if ($payment['member_id'])
				{
					if (!$objAdvance->check_account($payment['member_id'],$msg,$payment['money']))
					{
						$is_payed = 'failed';
						return false;
					}
				}
				else
				{
					$is_payed = 'failed';
					return false;
				}
				
				$obj_payment_update = kernel::single('ectools_payment_update');
                //判断是否合并支付
                if($payment['merge_payment_id'] != ''){
                    $obj_payments = app::get('ectools')->model('payments');
                    $pay_data = $obj_payments->getList('*',array('merge_payment_id'=>$payment['merge_payment_id']));
                    foreach($pay_data as $key=>$val){
                        $val['status'] = 'succ';
                        $is_payed = $obj_payment_update->generate($val, $msg);
                    }
                }else{
                    $payment['status'] = 'succ';
                    $is_payed = $obj_payment_update->generate($payment, $msg);
                }

				if (!$is_payed)
				{
					return false;
				}
				$db = kernel::database();
				$transaction_status = $db->beginTransaction();
				$is_payed = $order_pay_service_object->order_pay_finish($payment, 'succ', 'font',$msg);
				if (!$is_payed)
				{
					$db->rollback();
					return false;
				}
				
				$db->commit($transaction_status);
				// 支付扩展事宜 - 如果上面与中心没有发生交互，那么此处会发出和中心交互事宜.
				$order_pay_service_object->order_pay_finish_extends($payment);
				return $is_payed;
			//}
		}
		
		return false;	
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
     * 支付回调的方法
     * @param array 回调参数数组
     * @return array 处理后的结果
     */
	public function callback(&$recv)
	{
		
	}
	
	/**
     * 生成form的方法
     * @param null
     * @return string html
     */
	public function gen_form()
	{
		return '';
	}
}
