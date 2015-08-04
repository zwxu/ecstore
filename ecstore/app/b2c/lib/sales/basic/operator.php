<?php

/**
 * ﳵԤ˻(prefilter)
 * $ 2010-05-06 21:05 $
 */
class b2c_sales_basic_operator extends b2c_sales_basic_abstract
{
    private $_operator = null;
    
    function get_all() {
        if( $this->_operator==null ) {
            $aResult = array();
            foreach(kernel::servicelist('b2c_sales_basic_operator_apps') as $object) {
                if(!is_object($object)) continue;
                $aResult = array_merge($aResult,$object->getOperators());
            }
            $this->_operator = $aResult;
        }
        return $this->_operator;
    }
}
?>
