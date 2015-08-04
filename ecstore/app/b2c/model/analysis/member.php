<?php
class b2c_mdl_analysis_member extends dbeav_model{
    function count($filter=null){
        $sql = 'SELECT count(*) as _count FROM (SELECT login_name FROM '.
            kernel::database()->prefix.'b2c_orders as O LEFT JOIN '.
            kernel::database()->prefix.'pam_account as M on O.member_id=M.account_id 
        where (O.payed>0) and '.$this->_filter($filter).' Group By login_name) as tb';
        $row = $this->db->select($sql);
        return intval($row[0]['_count']);
    }

    public function getlist($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $sql = 'SELECT 1 as rownum,login_name,count(1) as saleTimes,sum(O.payed) as salePrice FROM '.
            kernel::database()->prefix.'b2c_orders as O LEFT JOIN '.
            kernel::database()->prefix.'pam_account as M on O.member_id=M.account_id 
        where (O.payed>0) and '.$this->_filter($filter).' Group By login_name';
        if($orderType)$sql.=' ORDER BY '.(is_array($orderType)?implode($orderType,' '):$orderType);

        $rows = $this->db->selectLimit($sql,$limit,$offset);
        $this->tidy_data($rows, $cols);
        foreach($rows as $key=>$val){
            $rows[$key]['rownum'] = (string)($offset+$key+1);
        }
        return $rows;
    }

    public function _filter($filter,$tableAlias=null,$baseWhere=null){
        $where = array(1);
        if(isset($filter['time_from']) && $filter['time_from']){
            $where[] = ' O.createtime >='.strtotime($filter['time_from']);
        }
        if(isset($filter['time_to']) && $filter['time_to']){
            $where[] = ' O.createtime <'.(strtotime($filter['time_to'])+86400);
        }
        if(isset($filter['login_name']) && $filter['login_name']){
            $where[] = ' login_name LIKE \'%'.$filter['login_name'].'%\'';
        }
        return implode($where,' AND ');
    }

    public function get_schema(){
        $schema = array (
            'columns' => array (
                'rownum' => array (
                    'type' => 'number',
                    'default' => 0,
                    'label' => app::get('b2c')->_('排名'),
                    'width' => 110,
                    'orderby' => false,
                    'editable' => false,
                    'hidden' => true,
                    'in_list' => true,
                    'default_in_list' => true,
                    'realtype' => 'mediumint(8) unsigned',
                ),
                'login_name' => array (
                    'type' => 'varchar(200)',
                    'pkey' => true,
                    'sdfpath' => 'pam_account/account_id',
                    'label' => app::get('b2c')->_('会员名'),
                    'width' => 210,
                    'searchtype' => 'has',
                    'editable' => false,
                    'in_list' => true,
                    'default_in_list' => true,
                    'realtype' => 'mediumint(8) unsigned',
                ),
                'saleTimes' => array (
                    'type' => 'number',
                    'label' => app::get('b2c')->_('订单量'),
                    'width' => 75,
                    'sdfpath' => 'contact/name',
                    'editable' => true,
                    'filtertype' => 'normal',
                    'filterdefault' => 'true',
                    'in_list' => true,
                    'is_title' => true,
                    'default_in_list' => true,
                    'realtype' => 'varchar(50)',
                ),
                'salePrice' => array (
                    'type' => 'money',
                    'default' => 0,
                    'required' => true,
                    'sdfpath' => 'score/total',
                    'label' => app::get('b2c')->_('订单额'),
                    'width' => 110,
                    'editable' => false,
                    'filtertype' => 'number',
                    'in_list' => true,
                    'default_in_list' => true,
                    'realtype' => 'mediumint(8) unsigned',
                ),
            ),
            'idColumn' => 'login_name',
            'in_list' => array (
                0 => 'rownum',
                1 => 'login_name',
                2 => 'saleTimes',
                3 => 'salePrice',
            ),
            'default_in_list' => array (
                0 => 'login_name',
                1 => 'saleTimes',
                2 => 'salePrice',
            ),
        );
        return $schema;
    }
}
