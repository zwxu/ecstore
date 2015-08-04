<?php

 
/**
 * 电商支付控制类
 * 
 * @version 0.1
 * @package ectools.lib
 */
class ectools_pay extends ectools_operation
{    
    /**
     * 共有构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {        
        $this->app = $app;
    }
    
    /**
     * 最终的克隆方法，禁止克隆本类实例，克隆是抛出异常。
     * @params null
     * @return null
     */
    final public function __clone()
    {
        trigger_error(app::get('ectools')->_("此类对象不能被克隆！"), E_USER_ERROR);
    }
    
    /**
     * 创建支付单
     * @params array - 订单数据
     * @params obj - 应用对象
     * @params string - 支付单生成的记录
     * @return boolean - 创建成功与否
     */
    public function generate(&$sdf, &$controller=null, &$msg='')
    {
        // 异常处理    
        if (!isset($sdf) || !$sdf || !is_array($sdf))
        {
            trigger_error(app::get('ectools')->_("支付单信息不能为空！"), E_USER_ERROR);exit;
        }       
        
        $is_save = false;
        
        //$obj_api_payment = kernel::service("api.ectools.payment");
		/** 保留原来sdf数据 **/
		$tmp_sdf = $sdf;
		/** end **/
        $obj_payment_create = kernel::single('ectools_payment_create');
        $is_save = $obj_payment_create->generate($sdf, $msg);
		
		/** 合并原来和新数据 **/
		$sdf = array_merge($tmp_sdf, $sdf);
		/** end **/
        
        if (!$is_save)
        {
            $msg = app::get('ectools')->_('支付单生成失败！');
            return false;
        }
        
        // 支付方式的处理
        $str_app = "";
        $pay_app_id = ($sdf['pay_app_id']) ? $sdf['pay_app_id'] : $sdf['pay_type'];
        $obj_app_plugins = kernel::servicelist("ectools_payment.ectools_mdl_payment_cfgs");
        foreach ($obj_app_plugins as $obj_app)
        {
            $app_class_name = get_class($obj_app);
            $arr_class_name = explode('_', $app_class_name);
            if (isset($arr_class_name[count($arr_class_name)-1]) && $arr_class_name[count($arr_class_name)-1])
            {
                if ($arr_class_name[count($arr_class_name)-1] == $pay_app_id)
                {
                    $pay_app_ins = $obj_app;
                    $str_app = $app_class_name;
                }
            }
			else
			{
				if ($app_class_name == $pay_app_id)
				{
					$pay_app_ins = $obj_app;
					$str_app = $app_class_name;
				}
			}
        }
        
        $pay_app_ins = new $str_app($controller->app);
        
        if ($sdf['pay_type']=='online')
        {
            // 线上支付，如alipay，paypal，99bill，tenpay等等
			if ($pay_app_id != 'deposit')
			{
				$is_payed = $pay_app_ins->dopay($sdf);
			}
			else
			{
				$is_payed = $pay_app_ins->do_payment($sdf, $msg);
			}
            
            return $is_payed;
        }
        else
        {
            // 线下支付          
            $paymentsArr['trade_no'] = $paymentsArr['pay_app_id'] . ' trade no. ' . time();
            $paymentsArr['t_payed'] = time();
            
            //$obj_api_payment = kernel::service("api.ectools.payment");
            $obj_payment_update = kernel::single('ectools_payment_update');
            $sdf['status'] = 'succ';
            $obj_payment_update->generate($sdf, $msg);            
            // 调用orders对接接口 todo.
        }
        
        return true;
    }
    
    /**
     * 获取支付单序号
     * @param null 
     * @return string 支付单序号
     */
    public function get_payment_id()
    {
        $objModelPay = $this->app->model('payments');
        
        return $objModelPay->gen_id();
    }
    
    /**
     * 后台订单支付
     * @param array sdf结构数据
     * @param string messaage 引用值
     * @return boolean 成功与否
     */
    public function gopay(&$sdf, &$msg='')
    {
        // 异常处理    
        if (!isset($sdf) || !$sdf || !is_array($sdf))
        {
            trigger_error(app::get('ectools')->_("支付单信息不能为空！"), E_USER_ERROR);exit;
        }       
        
        $is_save = false;
        
        $obj_payment_create = kernel::single('ectools_payment_create');
        $is_save = $obj_payment_create->generate($sdf, $msg);
        
        if (!$is_save)
        {
            return false;
        }
        
		$sdf['status'] = 'succ';
        $obj_payment_update = kernel::single('ectools_payment_update');
        $is_save = $obj_payment_update->generate($sdf, $msg);
        
        if (!$is_save)
        {
            return false;
        }
        
        return true;
    }

    /**
     * 合并付款创建支付单
     * @params array - 订单数据
     * @params obj - 应用对象
     * @params string - 支付单生成的记录
     * @return boolean - 创建成功与否
     */
    public function all_generate(&$sdf, &$controller=null, &$msg='')
    {
        // 异常处理    
        if (!isset($sdf) || !$sdf || !is_array($sdf))
        {
            trigger_error(app::get('ectools')->_("支付单信息不能为空！"), E_USER_ERROR);exit;
        }       
        
        $is_save = false;
        
        //$obj_api_payment = kernel::service("api.ectools.payment");
		/** 保留原来sdf数据 **/
		$tmp_sdf = $sdf;
		/** end **/
        $obj_payment_create = kernel::single('ectools_payment_create');
        $is_save = $obj_payment_create->all_generate($sdf, $msg);
		
		/** 合并原来和新数据 **/
		$sdf = array_merge($tmp_sdf, $sdf);
		/** end **/
        if (!$is_save)
        {
            $msg = app::get('ectools')->_('支付单生成失败！');
            return false;
        }
        
        return true;
    }

    public function all_dopay(&$sdf, &$controller=null, &$msg=''){
        // 支付方式的处理
        $str_app = "";
        $pay_app_id = ($sdf['pay_app_id']) ? $sdf['pay_app_id'] : $sdf['pay_type'];
        $obj_app_plugins = kernel::servicelist("ectools_payment.ectools_mdl_payment_cfgs");
        foreach ($obj_app_plugins as $obj_app)
        {
            $app_class_name = get_class($obj_app);
            $arr_class_name = explode('_', $app_class_name);
            if (isset($arr_class_name[count($arr_class_name)-1]) && $arr_class_name[count($arr_class_name)-1])
            {
                if ($arr_class_name[count($arr_class_name)-1] == $pay_app_id)
                {
                    $pay_app_ins = $obj_app;
                    $str_app = $app_class_name;
                }
            }
			else
			{
				if ($app_class_name == $pay_app_id)
				{
					$pay_app_ins = $obj_app;
					$str_app = $app_class_name;
				}
			}
        }
        
        $pay_app_ins = new $str_app($controller->app);
        
        if ($sdf['pay_type']=='online')
        {
            // 线上支付，如alipay，paypal，99bill，tenpay等等
			if ($pay_app_id != 'deposit')
			{
				$is_payed = $pay_app_ins->dopay($sdf);
			}
			else
			{
				$is_payed = $pay_app_ins->do_payment($sdf, $msg);
			}
            
            return $is_payed;
        }
        else
        {
            // 线下支付          
            $paymentsArr['trade_no'] = $paymentsArr['pay_app_id'] . ' trade no. ' . time();
            $paymentsArr['t_payed'] = time();
            
            //$obj_api_payment = kernel::service("api.ectools.payment");
            $obj_payment_update = kernel::single('ectools_payment_update');
            $sdf['status'] = 'succ';
            $obj_payment_update->generate($sdf, $msg);            
            // 调用orders对接接口 todo.
        }

        return true;
    }
}
