<?php

 

/**
 * 订单操作的接口方法的说明
 * 定义订单处理的基础方法
 * 现在主要的订单处理行动（支付，发货，完成，退款，退货和作废）
 */
interface b2c_interface_order_operation
{    
    /**
     * 统一处理方法入口
     * @params array - 订单数据
     * @params object - 控制器对象
     * @params string - 支付单生成的记录
     * @return boolean - 创建成功与否
     */
    public function generate($sdf, &$controller=null, &$msg='');
    
     /**
     * 订单冻结
     * @params string order_id
     * @return boolean - 冻结是否成功
     */
    public function freezeGoods($order_id);
    
    /**
     * 解除冻结订单
     * @params string order_id
     * @return boolean - 解冻是否成功 
     */
    public function unfreezeGoods($order_id);
    
    /**
     * 剪掉库存
     * @params array 更改便准数据
     * @return boolean - 库存是否剪掉
     */
    public function minus_stock(&$arrData);
    
    /**
     * 还原库存
     * @params string order_id
     * @params boolean - 库存是否还原成功
     */
    public function restore_stock(&$arrData);
}
