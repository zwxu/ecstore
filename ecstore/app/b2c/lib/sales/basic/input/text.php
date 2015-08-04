<?php

 
class b2c_sales_basic_input_text
{
    public $type = 'text';
    public function create($aData, $table_info=array()) {
        $vtype = "required";
        $vtype = (isset($aData['vtype']) && !empty($aData['vtype']))? (is_array($aData['vtype']))? $vtype."&".implode("&",$aData['vtype']) : ($vtype."&".$aData['vtype']) : $vtype;
        $size =  (isset($aData['size']) && intval($aData['size']))? " size=".$aData['size'] : '';
        return '<input type="text" name="'.$aData['name'].'" value="'.$aData['default'].'" vtype="'.$vtype.'" '.$size.' />'.$aData['desc'];
    }
}
?>
