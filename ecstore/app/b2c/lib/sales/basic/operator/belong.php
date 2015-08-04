<?php

 
/**
 * 操作符 belong (属于,属于) [array]
 * $ 2010-05-11 18:40 $
 */
class b2c_sales_basic_operator_belong implements b2c_interface_sales_operator
{
    public function getOperators() {
        return array(
                    '{}'=>  array('name'=>app::get('b2c')->_('属于'),   'value'=>'{}',  'type'=>'belong', 'object'=>'b2c_sales_basic_operator_belong', 'alias'=>array('{}')),
                    '!{}'=> array('name'=>app::get('b2c')->_('不属于'), 'value'=>'!{}', 'type'=>'belong', 'object'=>'b2c_sales_basic_operator_belong', 'alias'=>array('!{}')),
               );
    }

    /**
     * Enter description here...
     *
     * @param array $aCondition // array(
     *                                'attribute'=>'xxx',
     *                                'operator'=>'xxx',
     *                                'value'   => 'xxx'
     *                             )
     */
    public function getString($aCondition) {
       return false; // 不般是不会那么过滤的 暂时不写... 2010-05-11 19:02 wubin
    }

    /**
     * validate
     *
     * @param string $operator  // 操作符
     * @param mix $value        // 规则里设定的值
     * @param mix $validate     // 购物车项中取出的对应的'attribute'[path] 的值
     * @return boolean
     */
    public function validate($operator,$value,$validate) {
        switch($operator) {
            case '{}':
                if(is_array($value)) return in_array($validate,$value);
                return (strpos($value,$validate) >= 0);break;
            case '!{}':
                if(is_array($value)) return !in_array($validate,$value);
                return (strpos($value,$validate) === false);break;
        }
        return false;
    }
}
?>
