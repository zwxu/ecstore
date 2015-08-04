<?php
class b2c_mdl_analysis_advance extends b2c_mdl_member_advance{
    public function table_name($real=false){
        $table_name = 'member_advance';
        if($real){
            return kernel::database()->prefix.'b2c_'.$table_name;
        }else{
            return $table_name;
        }
    }

    public function get_money($filter=null){
        //存入金额,支出金额
        $sql = 'SELECT sum(import_money) as import_money,sum(explode_money) as explode_money FROM '.
            $this->table_name(true).' WHERE '.
            'mtime >='.$filter['time_from'].' and mtime <='.intval($filter['time_to']);
        $row = $this->db->select($sql);
        return $row[0];
    }

    public function get_shop_advance(){
        //商店余额
        $sql = 'SELECT shop_advance FROM '.$this->table_name(true).
            ' ORDER BY log_id DESC';
        $row = $this->db->selectLimit($sql,$limit=1,$offset=0);
        return $row[0]['shop_advance'];
    }

    public function get_member_num(){
        //使用人数
        $sql = 'SELECT count(*) as _count FROM (SELECT member_id FROM '.
            $this->table_name(true).' Group By member_id) AS tb';
        $row = $this->db->select($sql);
        return intval($row[0]['_count']);
    }

    public function searchOptions(){
        $columns = array();
        foreach($this->_columns() as $k=>$v){
            if(isset($v['searchtype']) && $v['searchtype']){
                $columns[$k] = $v['label'];
            }
        }

        $ext_columns = array(
            'member_name'=>$this->app->_('用户名'),
        );
        
        return array_merge($columns, $ext_columns);
    }

    public function _filter($filter,$tableAlias=null,$baseWhere=null){
        $where=array(1);
        if(isset($filter['time_from']) && $filter['time_from']){
            $where[] = ' mtime >='.strtotime($filter['time_from']);
        }
        if(isset($filter['time_to']) && $filter['time_to']){
            $where[] = ' mtime <'.(strtotime($filter['time_to'])+86400);
        }
        if(array_key_exists('member_name', $filter)){
            if($filter['member_name'] !== ''){
                $aId = array(0.1);
                foreach($this->db->select('SELECT account_id FROM '.kernel::database()->prefix.'pam_account WHERE login_name = \''.addslashes($filter['member_name']).'\'') as $rows){
                    $aId[] = $rows['account_id'];
                }
                $where[] = 'member_id IN ('.implode(',', $aId).')';
            }
            unset($filter['member_name']);
        }

        return parent::_filter($filter).' and '.implode($where,' AND ');
    }
}
