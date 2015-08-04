<?php

class b2c_sales_basic_input_datetime
{
    public $type = 'datetime';
    public function create($aData, $table_info=array()) {
        $html = '';
        $params = array(
            'type'  => 'time',
            'name'  => $aData['name'],
            'id'    => $aData['name'],
            'value' => $aData['default'],
            'vtype' => 'required'
        );
        $html .= kernel::single('base_view_input')->input_time($params);
        $html .= '<script>try{Ex_Loader("picker",function(){new DatePickers([$("'.$aData['name'].'")]);});}catch(e){$("'.$aData['name'].'").makeCalable();}</script>';
        return $aData['desc'].$html;
    }
}
