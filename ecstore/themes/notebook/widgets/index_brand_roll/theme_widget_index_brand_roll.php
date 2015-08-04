<?php
function theme_widget_index_brand_roll(&$setting,&$smarty) {
    $brandObj = app::get('b2c')->model('brand');
    $brands = $brandObj->getList('brand_logo,brand_name,brand_id',array('brand_id'=>$setting['brand'],'disabled'=>'false'),0,-1,'ordernum');
    $data = array();
    $num = 4;
    $i = 0;
    foreach($brands as $k=>$v){
        $j = $k%$num;
        $data[$i][$j] = $v;
        if($j == ($num-1)){
            $i++;
        }
    }

    return $data;
}

?>
