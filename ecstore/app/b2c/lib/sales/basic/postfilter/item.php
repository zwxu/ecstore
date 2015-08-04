<?php

 
/**
 * items基类
 * $ 2010-05-08 19:38 $
 */
class b2c_sales_basic_postfilter_item extends b2c_sales_basic_item
{
    /**
     * item validate
     *
     * @param array $objects     // 购物车单项数据
     * @param array $aCondition  // 条件规则
     * @return boolean
     */
    public function validate($objects,$aCondition) {
        // 没有操作符 说明规则有问题 返回false
        if(empty($aCondition['operator'])) return false;
        $aOperator = $this->getOperator($aCondition['operator']);
        if(empty($aOperator)) return false; // 没有相关操作符信息

        // 没有attribute 说明规则有问题 返回false
        if(empty($aCondition['attribute'])) return false;
        $aAttribute = $this->getAttribute($aCondition['attribute']);
        if(empty($aAttribute)) return false; // 没有相关的属性信息

        // 从$object数组里取指定的值
        $validate = $this->_getData($objects,$aAttribute['path']);

        return kernel::single($aOperator['object'])->validate($aCondition['operator'],$aCondition['value'],$validate);
    }
}

