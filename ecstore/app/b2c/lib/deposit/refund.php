<?php

 

class b2c_deposit_refund implements b2c_deposit_interface
{
    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    /**
     * 得到支付对象类别 - 当前为预存款
     * @param null
     * @param string - 预存款类别
     */
    public function get_type()
    {
        return 'deposit';
    }
    
    /**
     * 预存款充值处理
     * @param array sdf 预存款的标准数据结构
     * @param array 支付单和支付对象关联表
     * @param string type 在线或者线下
     * @param string 操作员id
     * @param string 操作员name
     * @param array errorMsg error message
	 * @param boolean 前台操作还是后台操作
     * @return boolean
     */
    public function generate_bills($sdf, $arr_bills, $type='online', &$op_id, &$op_name, &$errorMsg,$is_frontend=true)
    {
        // 退款到预存款账号
        $objAdvance = $this->app->model("member_advance");
        $objAdvance->add($arr_bills['member_id'], $sdf['money'], app::get('b2c')->_('预存款退款'), $msg, $sdf['refund_id'], $sdf['order_id'], $sdf['payment'], app::get('b2c')->_('退还订单消费'),0,$is_frontend);
        
        return true;
    }
}