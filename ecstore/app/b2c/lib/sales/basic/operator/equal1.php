<?php

 
/**
 * 操作符 equal (大于,大于等于,小于,小于等于)
 * $ 2010-05-11 16:21 $
 */
class b2c_sales_basic_operator_equal1 implements b2c_interface_sales_operator
{
    public function getOperators() {
        return array(
                    '<'   => array('name'=>app::get('b2c')->_('小于'),    'value'=>'<',  'type'=>'equal1','object'=>'b2c_sales_basic_operator_equal1', 'alias'=>array('<')),
                    '<='  => array('name'=>app::get('b2c')->_('小于等于'), 'value'=>'<=', 'type'=>'equal1','object'=>'b2c_sales_basic_operator_equal1', 'alias'=>array('<=')),
                    '>'   => array('name'=>app::get('b2c')->_('大于'),    'value'=>'>',  'type'=>'equal1','object'=>'b2c_sales_basic_operator_equal1', 'alias'=>array('>')),
                    '>='  => array('name'=>app::get('b2c')->_('大于等于'), 'value'=>'>=', 'type'=>'equal1','object'=>'b2c_sales_basic_operator_equal1', 'alias'=>array('>=') )
        );
    }

    /**
     * Enter description here...
     *
     * @param array $aCondition // array(
     *                                'attribute'=>'xxx', // string | array()
     *                                'operator'=>'xxx',
     *                                'value'   => 'xxx'
     *                             )
     */
    public function getString($aCondition) {
        $sWhere = $aCondition['operator']." ".$aCondition['value']." ";

        if(is_array($aCondition['attribute'])) {
             return " ".$aCondition['attribute']['ref_id']." IN (SELECT `".$aCondition['attribute']['pkey']."` FROM ".$aCondition['attribute']['table']." WHERE ".$aCondition['attribute']['attribute'].$sWhere.") ";
        }
        return $aCondition['attribute']." ".$sWhere;
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
        //此处强制转换 针对$validate为空情况
        $validate = (float)$validate;
        
        switch($operator) {
            case '>':
                return ($validate > $value);break;
            case '>=':
                return ($validate >= $value);break;
            case '<':
                return ($validate < $value);break;
            case '<=':
                return ($validate <= $value);break;
        }
        return false;
    }
}
?>
