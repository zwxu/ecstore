<?php

 

class b2c_order_actionbutton{

    function __construct($app){
        $this->app = $app;
        $this->app_ectools = app::get('ectools');
    }
    
    
    
    //////////////////////////////////////////////////////////////////////////
    // order finder 按钮接口 设置可见的按钮 默认所有
    ///////////////////////////////////////////////////////////////////////////
    public function is_display( $arr=array() )
    {
        $this->is_display = $arr;
    }
    #End Func

    function get_buttons($sdf_order=array(), $is_all_disable = false)
    {
        $arr_order = array();
        $disable_payed = false;
        $disable_delivery = false;
        $disable_finish = false;
        $disable_refund = false;
        $disable_reship = false;
        $disable_cancel = false;
        $disable_delete = false;
        
        $flow_payed = false;
        $flow_delivery = false;
        $flow_finish = false;
        $flow_refund = false;
        $flow_reship = false;
        $flow_cancel = false;
        $flow_delete = false;
        
        if ($is_all_disable)
        {
            $disable_payed = true;
            $disable_delivery = true;
            $disable_finish = true;
            $disable_refund = true;
            $disable_reship = true;
            $disable_cancel = true;
            $disable_delete = true;
        }
        
        if ($sdf_order)
        {            
            if ($sdf_order['status'] != 'active' || $sdf_order['pay_status'] == 1 || $sdf_order['pay_status'] == 2 || $sdf_order['pay_status'] == 4 || $sdf_order['pay_status'] == 5)
            {
                $disable_payed = true;
            }
            if ($sdf_order['flow']['payed'])
                $flow_payed = true;
                
            if ($sdf_order['status'] != 'active' || $sdf_order['is_all_ship'] == 1 || $sdf_order['ship_status'] == 1)
            {
                $disable_delivery = true;
            }
            if ($sdf_order['flow']['consign'])
                $flow_delivery = true;
                
            if ($sdf_order['status'] != 'active')
                $flow_finish = true;
                
            if ($sdf_order['status'] != 'active' || $sdf_order['pay_status'] == 0 || $sdf_order['pay_status'] == 5)
            {
                $disable_refund = true;
            }
            if ($sdf_order['flow']['refund'])
                $flow_refund = true;
            
            if ($sdf_order['status'] != 'active' || $sdf_order['ship_status'] == 4 || $sdf_order['ship_status'] == 0)
            {
                $disable_reship = true;
            }
            if ($sdf_order['flow']['reship'])
                $flow_reship = true;
                
            if ($sdf_order['status'] != 'active' || $sdf_order['ship_status'] > 0 || $sdf_order['pay_status'] > 0)
            {
                $disable_cancel = true;
            }           
            
            if ($sdf_order['status'] != 'active')
            {
                $disable_finish = true;
            }
            
            if ($sdf_order['status'] != 'dead')
            {
                $disable_delete = true;
            }
        }
        $buttons = array(
            'sequence'=>array(
                'pay'=>array(
                    'label'=>app::get('b2c')->_('支付'),
                    'flow'=>$flow_payed,
                    'disable'=>$disable_payed,
                    'app'=>'b2c',
                    'act'=>'pay',
                ),
                'delivery'=>array(
                    'label'=>app::get('b2c')->_('发货'),
                    'flow'=>$flow_delivery,
                    'disable'=>$disable_delivery,
                    'app'=>'b2c',
                    'act'=>'delivery',
                ),
                'finish'=>array(
                    'label'=>app::get('b2c')->_('完成'),
                    'flow'=>$flow_finish,
                    'disable'=>$disable_finish,
                    'app'=>'b2c',
                    'act'=>'finish',
                    'confirm'=>app::get('b2c')->_('完成操作会将该订单归档并且不允许再做任何操作，确认要执行吗?'),
                ),
            ),
            're_sequence'=>array(
                'refund'=>array(
                    'label'=>app::get('b2c')->_('退款'),
                    'flow'=>$flow_refund,
                    'disable'=>$disable_refund,
                    'app'=>'b2c',
                    'act'=>'refund',
                ),
                'reship'=>array(
                    'label'=>app::get('b2c')->_('退货'),
                    'flow'=>$flow_reship,
                    'disable'=>$disable_reship,
                    'app'=>'b2c',
                    'act'=>'reship',
                ),
                'cancel'=>array(
                    'label'=>app::get('b2c')->_('作废'),
                    'flow'=>$flow_cancel,
                    'disable'=>$disable_cancel,
                    'app'=>'b2c',
                    'act'=>'cancel',
                    'confirm'=>app::get('b2c')->_('作废后该订单将不能做任何操作，只能删除，确认要执行吗?'),
                ),
                'delete'=>array(
                    'label'=>app::get('b2c')->_('删除'),
                    'flow'=>$flow_delete,
                    'disable'=>$disable_delete,
                    'app'=>'b2c',
                    'act'=>'delete',
                    'confirm'=>app::get('b2c')->_('只有作废后才能删除订单，确认要执行吗?'),
                ),
            ),
        );
        
        if( $this->is_display && is_array($this->is_display) ) {
            foreach( $buttons['sequence'] as $key => $val ) {
                if( !in_array($key,$this->is_display) ) unset($buttons['sequence'][$key]);
            }
            foreach( $buttons['re_sequence'] as $key => $val ) {
                if( !in_array($key,$this->is_display) ) unset($buttons['re_sequence'][$key]);
            }
        }
        return $buttons;
    }
    
    public function get_extension_buttons($sdf_order=array())
    {
        // 按钮的扩展
        $buttons = array();
        $ext_btn_service = kernel::servicelist('b2c.order.act_ext_btn');
        foreach ($ext_btn_service as $str_ext_btn)
        {
            $str_ext_btn->gen_btn($buttons, $sdf_order);
        }        
        
        return $buttons;
    }
}
