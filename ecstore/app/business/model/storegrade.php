<?php
class business_mdl_storegrade extends dbeav_model {
    var $has_tag = true;
    var $defaultOrder = array('grade_id', ' DESC');
   
    var $has_one = array();
    

    public function count_finder($filter = null) {
        $row = $this -> db -> select('SELECT count( DISTINCT grade_id) as _count FROM ' . $this -> table_name(1) . ' WHERE ' . $this -> _filter($filter));
        return intval($row[0]['_count']);
    } 

    public function get_list_finder($cols = '*', $filter = array(), $offset = 0, $limit = -1, $orderType = null) {
        $tmp = $this -> getList('*', $filter, 0, -1, $orderType);

        if($limit<0) {
              return  $tmp;
        } else {
             return array_slice($tmp, 0, $limit);
        }
    } 

      
} 
