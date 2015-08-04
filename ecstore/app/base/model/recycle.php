<?php

 

class base_mdl_recycle extends base_db_model{

    function  save(&$data,$mustUpdate = null){
        $return = parent::save($data,$mustUpdate);
    }
    function get_item_type(){
        $rows = $this->db->select('select distinct(item_type) from '.$this->table_name(1).' ');
        return $rows;
    }
}
