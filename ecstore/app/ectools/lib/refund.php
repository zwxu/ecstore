<?php

 
/**
 * 电商退款统一处理入口
 * 
 * @version 0.1
 * @package ectools.lib
 */
class ectools_refund extends ectools_operation
{    
    /**
     * 私有构造方法，不能直接实例化，只能通过调用getInstance静态方法被构造
     * @params object 当前应用的app对象
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
     * 创建退款单
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
            trigger_error(app::get('ectools')->_("退款单信息不能为空！"), E_USER_ERROR);exit;
        }

        $is_save = false;
        
        // 保存的接口方法        
        $obj_refund_create = kernel::single("ectools_refund_create");
        $is_save = $obj_refund_create->generate($sdf);
        
        if (!$is_save)
        {
            $msg = app::get('ectools')->_('退款单生成失败！');
            return false;
        }
		
		/*$obj_refund_special = kernel::servicelist('ectools_refund.ectools_mdl_special_refund');
		foreach ($obj_refund_special as $obj_app)
		{
			$is_save = $obj_app->dorefund($sdf);
		}
        
		if ($is_save)
		{
			$obj_api_refund = kernel::single("ectools_refund_update");
			$sdf['status'] = 'succ';
			$is_save = $obj_api_refund->generate($sdf);
			
			if (!$is_save)
			{
				$msg = app::get('ectools')->_('退款单编辑失败！');
				return false;
			}
		}*/
        
        return $is_save;
    }

    public function generate_after($sdf)
    {
		$obj_refund_special = kernel::servicelist('ectools_refund.ectools_mdl_special_refund');
		foreach ($obj_refund_special as $obj_app)
		{
			$is_save = $obj_app->dorefund($sdf);
		}

        $obj_api_refund = kernel::single("ectools_refund_update");
        $sdf['status'] = 'succ';
        $sdf['t_payed'] = time();
        $is_save = $obj_api_refund->generate($sdf);
        
        if (!$is_save)
        {
            $msg = app::get('ectools')->_('退款单编辑失败！');
            return false;
        }
        
        return $is_save;
    }

    public function dorefund($sdf,$controller)
    {
		$str_app = "";
        $pay_app_id = ($sdf['pay_app_id']) ? $sdf['pay_app_id'] : $sdf['pay_type'];
        $obj_app_plugins = kernel::servicelist("ectools_refund.ectools_mdl_refund_cfgs");
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
            $is_refund = $pay_app_ins->dorefund($sdf); 
            return $is_refund;
        }else{

        }

        return "success";
    }

    public function callback($sdf,$result,$type="")
    {
        $obj_refunds = kernel::single("ectools_refund");
        $refunds = app::get('ectools')->model('refunds');
		if($type=="server"){
            if($sdf['status'] == 'ready'){
                if($result == 'success'){
                    $ref_rs = $obj_refunds->generate_after($sdf);
                }else{
                    $ref_rs = $obj_refunds->generate_after($sdf,'failed');
                    $refunds->update(array('memo'=>$result),array('refund_id'=>$sdf['refund_id']));
                }
            }
        }else{
            if($sdf['status'] == 'ready'){
                if($result == 'success'){
                }else{
                    $ref_rs = $obj_refunds->generate_after($sdf,'failed');
                    $refunds->update(array('memo'=>$result),array('refund_id'=>$sdf['refund_id']));
                }
            }
        }

        return true;
    }
}

?>
