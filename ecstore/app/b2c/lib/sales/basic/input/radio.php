<?php

 
class b2c_sales_basic_input_radio
{
    public $type = 'radio';
    public function create($aData, $table_info=array()) {
        $html = '';
        $aData['default'] = (is_null($aData['default']) || (trim($aData['default']) == '') )? array() : (is_array($aData['default'])? $aData['default'] : explode(',',$aData['default']) ) ;
        if(is_array($aData['options'])) {
            foreach($aData['options'] as $key => $row) {
                $html .= '<label><input type="radio" name="'.$aData['name'].'" value="'.$key.'" '.(in_array($key,$aData['default'])? 'checked' : '').' />'.$row['name'].'</label>';
            }
        }
        return $aData['desc'].$html;
    }
}
?>
