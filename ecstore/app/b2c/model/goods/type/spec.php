<?php

 

class b2c_mdl_goods_type_spec extends dbeav_model{
    var $has_many = array(
    );

    function get_type_spec($type_id){
		if (!$type_id) return array();
        return $this->getList('*',array('type_id'=>$type_id));
    }
}
