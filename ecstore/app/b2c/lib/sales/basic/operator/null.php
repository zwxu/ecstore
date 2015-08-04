<?php

 
/**
 * 操作符 null (为空,不为空)
 * $ 2010-05-11 18:14 $
 */
class b2c_sales_basic_operator_null implements b2c_interface_sales_operator
{
    public function getOperators() {
        return array(
                    'null' => array('name'=>app::get('b2c')->_('为空'),    'value'=>'null',  'type'=>'null', 'object'=>'b2c_sales_basic_operator_null', 'alias'=>array('null')),
                    '!null' => array('name'=>app::get('b2c')->_('不为空'), 'value'=>'!null', 'type'=>'null', 'object'=>'b2c_sales_basic_operator_null',  'alias'=>array('!null')),
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
        if($aCondition['operator'] == 'null') {
            $sWhere = ' IS NULL ';
        }
        if($aCondition['operator'] == '!null') {
            $sWhere = ' IS NOT NULL ';
        }

        if(empty($sWhere)) return false;

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
            case 'null':
                return (empty($validate) && $validate !== 0);break;
            case '!null':
                return (!empty($validate) || $validate === 0);break;
        }
        return false;
    }
}
?>
