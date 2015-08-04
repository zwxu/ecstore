<?php

 
class b2c_sales_basic_input_select
{
    public $type = 'select';
    public function create($aData, $table_info=array()) {
        if(!isset($aData['options']) || empty($aData['options']) || !is_array($aData['options'])) return false;

        $options = '';
        $aData['default'] = (is_null($aData['default']) || (trim($aData['default']) == '') )? array() : (is_array($aData['default'])? $aData['default'] : explode(',',$aData['default']) ) ;
        if(is_array($aData['options'])) {
            foreach($aData['options'] as $key => $row) {
                $options .= '<option value="'.$key.'" '.((is_array($aData['default']))? ( in_array($key,$aData['default'])? 'selected ' : '' ) : ( ($key == $default)? 'selected ' : '' )).'>'.$row['name'].'</option>';
            }
        }

        if(empty($options)) return false; // 没有选项

        $aData['size'] = (is_null($aData['size']) || !intval($aData['size']))? ($aData['multi']? 5 : 1) : intval($aData['size']);
        return $aData['desc'].'<select name="'.$aData['name'].'" size="'.$aData['size'].'" '.(($aData['multi'])? 'multiple="multiple" ' : '').'>'.$options.'</select>';
    }
}
?>
