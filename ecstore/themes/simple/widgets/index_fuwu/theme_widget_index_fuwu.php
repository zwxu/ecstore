<?php
function theme_widget_index_fuwu(&$setting,&$smarty) {
    //获取后台绑定城市
    $mdl_city = app::get('site')->model('city');
    $provinces = $mdl_city->getList('distinct(province_id) as region_id,province_name as local_name');
    foreach($provinces as $key=>$val){
        $cities = $mdl_city->getList('city_name as local_name,city_id as region_id,province_id as  p_region_id',array('province_id'=>$val['region_id']));
        $regions[$key]['cities'] = $cities;
        $regions[$key]['region_id'] = $val['region_id'];
        $regions[$key]['local_name'] = $val['local_name'];
    }
    $setting['regions'] = json_encode($regions);
    $setting['base_url'] = kernel::base_url().'/';
    return $setting;
}

?>
