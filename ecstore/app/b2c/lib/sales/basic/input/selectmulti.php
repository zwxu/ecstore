<?php

 
class b2c_sales_basic_input_selectmulti
{
    public $type = 'selectmulti';
    public function create($aData, $table_info=array()) {
        $aData['multi'] = true;
        return kernel::single('b2c_sales_basic_input_selectmulti')->create($aData);
    }
}
?>
