<?php

class bdlink_mdl_link_ref extends dbeav_model {

    public function _dump($data) {
        $arr = $this->getList('*', $data);
        if( !isset($arr[0]) || empty($arr[0]) ) return false;
        $arr = $arr[0];
        $filter = array('refer_id'=>$arr['refer_id']);
        $tmp = kernel::single('bdlink_mdl_link')->getList('*', $filter);
        if( !isset($tmp[0]) || empty($tmp[0]) ) return false;
        return array_merge($arr, $tmp[0]);
    }
    
    
    
}