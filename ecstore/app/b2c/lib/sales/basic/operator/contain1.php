<?php

 
/**
 * 操作符 contian (开头包含,结尾包含)
 * $ 2010-05-11 18:36 $
 */
class b2c_sales_basic_operator_contain1 implements b2c_interface_sales_operator
{
    public function getOperators() {
        return array(
                    '#()'=> array('name'=>app::get('b2c')->_('开头包含'), 'value'=>'#()', 'type'=>'contain1', 'object'=>'b2c_sales_basic_operator_contain1', 'alias'=>array('#()')),
                    '()#'=> array('name'=>app::get('b2c')->_('结尾包含'), 'value'=>'()#', 'type'=>'contain1', 'object'=>'b2c_sales_basic_operator_contain1', 'alias'=>array('()#')),
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
        $sWhere = '';
        switch($aCondition['operator']){
            case '#()': // 以$aData['value']开头
               if(is_string($aCondition['value'])) { // like
                  $sWhere = " LIKE '".$aCondition['value']."%'";
               }
               break;
            case '()#': // 以$aData['value']结尾
               if(is_string($aCondition['value'])) { // like
                   $sWhere = " LIKE '%".$aCondition['value']."'";
               }
              break;
        }

        if(empty($sWhere)) return false;

        if(is_array($aCondition['attribute'])) {
            return " ".$aCondition['attribute']['ref_id']." IN (SELECT `".$aCondition['attribute']['pkey']."` FROM ".$aCondition['attribute']['table']." WHERE ".$aCondition['attribute']['attribute'].$sWhere.") ";
        } else {
            return $aCondition['attribute']." ".$sWhere;
        }
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
            case '#()':
                return (strpos($validate,$value) == 0);break;
            case '()#':
                return ((strlen($validate) - strlen($value)) == strpos($validate,$value));break;
        }
        return false;
    }
}
?>
