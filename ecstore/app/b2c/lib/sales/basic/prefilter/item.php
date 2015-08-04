<?php

 
/**
 * items基类
 * $ 2010-05-08 19:38 $
 */
class b2c_sales_basic_prefilter_item extends b2c_sales_basic_item
{
    public function filter($aCondition) {
        if(isset($aCondition['attribute']) && isset($aCondition['operator'])) {
            return $this->_operate($aCondition);
        }
        return false;
    }

    protected function _operate($aCondition) {
        $aOperator = $this->getOperator($aCondition['operator']);
        if(empty($aOperator)) return false; // 获取操作符信息失败

        // attribute 的处理
        $aCondition['attribute'];
        $aAttribute = $this->getAttribute($aCondition['attribute']);
        if(empty($aAttribute)) return false; // 没有相关属性

        $aRef = kernel::single($aAttribute['object'])->getRefInfo(); // 如果返回的是空
        if($aRef) {// 如果是关联的属性
            $aRef['attribute'] = $aAttribute['path'];
            $aCondition['attribute'] = $aRef;
        } else {
            $aCondition['attribute'] = $aAttribute['path'];
        }

        return kernel::single($aOperator['object'])->getString($aCondition);
    }
}
?>
