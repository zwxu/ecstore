<?php

 
class b2c_sales_basic_input_hidden
{
    public $type = 'hidden';
    public function create($aData, $table_info=array()) {
        return '<input type="hidden" name="'.$aData['name'].'" value="'.$aData['default'].'" />'.$aData['desc'];
    }
}
?>
