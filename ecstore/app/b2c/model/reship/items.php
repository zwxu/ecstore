<?php

 

class b2c_mdl_reship_items extends dbeav_model{
    /** 
     * 重写getList方法
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        $arr_list = parent::getList($cols,$filter,$offset,$limit,$orderType);
        $obj_extends_order_service = kernel::servicelist('b2c.api_reship_extends_actions');
        if ($obj_extends_order_service)
        {
            foreach ($obj_extends_order_service as $obj)
                $obj->extend_item_list($arr_list);
        }
        
        return $arr_list;
    }
}