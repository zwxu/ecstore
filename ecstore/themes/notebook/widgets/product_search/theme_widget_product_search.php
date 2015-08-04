<?php
function theme_widget_product_search(&$setting,&$render){
    $data['store_id'] = $render->pagedata['store_id'];
    return $data;
}
?>
