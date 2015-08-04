<?php

 
class b2c_sales_csv extends desktop_io_type_csv {

    var $name = 'csv-逗号分隔的文本文件';
    var $importforObjects = 'goods';

    
    
    public function download($title='优惠券代码', $prex='coupon', $nums=0, $data=array()) {
        $aData = array(
                        'title' =>  $title,
                        'name'  =>  $prex .'-'. date("ymdhis") .'('. $nums .')',
                        'contents'  =>  $data,
                    );
        $this->export_header( $aData,$this );
        $this->export($aData, $this);
    }
    
    
    
    


}
?>
