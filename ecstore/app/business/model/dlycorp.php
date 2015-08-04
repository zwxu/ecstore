<?php
  
class business_mdl_dlycorp extends dbeav_model{
    /*
    var $has_one = array(
        'dlycorp' => 'dlycorp@b2c:replace:crop_id^crop_id',
    );*/
    
    function save($data){
        $sql = "insert into ".$this->table_name(true)." (corp_id,store_id) value ";
        $temp = array();
        if(count($data)>0){
            foreach($data as $items){
                $temp[] = "({$items['corp_id']},{$items['store_id']})";
            }
        }else{
            return true;
        }
        if(count($temp)>0){
            $sql .= implode(',', $temp);
        }else{
            return true;
        }
        return $this->db->exec($sql);
    }
    
    function getdlycorp($store_id){
        $sql = "select b.* from ".$this->table_name(true)." as a join sdb_b2c_dlycorp as b on a.corp_id=b.corp_id where a.store_id = ".intval($store_id);
        return $this->db->select($sql);
    }
}