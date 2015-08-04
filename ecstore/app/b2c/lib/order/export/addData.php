<?php



class b2c_order_export_addData{

    function addData($data){
        $itemObj = app::get('b2c')->model('order_items');
        $names = $itemObj->getList('name',array('order_id'=>$data['order_id']));
        $goods_name = array();
        foreach($names as $name){
            $goods_name[] = $name['name'];
        }
        $data['goods_names'] = implode(',',$goods_name);
        return $data;
    }

}
