<?php

 
/**
 * 促销规则操作符接口
 */
interface b2c_interface_sales_operator
{
    public function getOperators();
    public function getString($aCondition);
    // public function validate($aV)
}
?>
