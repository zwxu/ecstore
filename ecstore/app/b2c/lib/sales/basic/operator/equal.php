<?php

 
/**
 * 操作符 equal (等于,不等于)
 * $ 2010-05-11 16:21 $
 */
class b2c_sales_basic_operator_equal implements b2c_interface_sales_operator
{
    public function getOperators() {
        return array(
                    '='   => array('name'=>app::get('b2c')->_('等于'),    'value'=>'==',   'type'=>'equal', 'object'=>'b2c_sales_basic_operator_equal', 'alias'=>array('=','==','===')),
                    '<>'  => array('name'=>app::get('b2c')->_('不等于'),  'value'=>'<>',  'type'=>'equal', 'object'=>'b2c_sales_basic_operator_equal', 'alias'=>array('<>','!=','!==')),
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
        if($aCondition['operator'] == '==' || $aCondition['operator'] == '===') {
            $aCondition['operator'] = '=';
        }
        if($aCondition['operator'] == '!=' || $aCondition['operator'] == '!==') {
            $aCondition['operator'] = '<>';
        }

        $sWhere = $aCondition['operator']."'".$aCondition['value']."' ";

        if(is_array($aCondition['attribute'])) {
             return " ".$aCondition['attribute']['ref_id']." IN (SELECT `".$aCondition['attribute']['pkey']."` FROM ".$aCondition['attribute']['table']." WHERE ".$aCondition['attribute']['attribute'].$sWhere.") ";
        }
        return $aCondition['attribute'].$sWhere;
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
            case '=': case '==': case '===':
                return ($validate == $value);break;
            case '<>': case '!=': case '!==':
                return ($validate != $value);break;
        }
        return false;
    }
}
?>
