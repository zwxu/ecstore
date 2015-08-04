<?php
class b2c_mdl_analysis_sale extends dbeav_model{
    public function get_pay_money($filter=null){
        //收款额
        $sql = 'SELECT sum(P.money) as amount FROM '.
            kernel::database()->prefix.'ectools_order_bills as B LEFT JOIN '.
            kernel::database()->prefix.'ectools_payments as P ON B.bill_id=P.payment_id '.
            'where pay_object=\'order\' and bill_type=\'payments\' and P.t_payed >='.intval($filter['time_from']).' and P.t_payed <='.intval($filter['time_to']).' and P.status=\'succ\'';
        $row = $this->db->select($sql);
        return $row[0]['amount'];
    }

    public function get_refund_money($filter=null){
        //退款额
        $sql = 'SELECT sum(R.money) as amount FROM '.
            kernel::database()->prefix.'ectools_order_bills as B LEFT JOIN '.
            kernel::database()->prefix.'ectools_refunds as R ON B.bill_id=R.refund_id '.
            'where pay_object=\'order\' and bill_type=\'refunds\' and R.t_payed >='.intval($filter['time_from']).' and R.t_payed <='.intval($filter['time_to']).' and R.status=\'succ\'';
        $row = $this->db->select($sql);
        return $row[0]['amount'];
    }

    public function searchOptions(){
        $columns = array();
        foreach($this->_columns() as $k=>$v){
            if(isset($v['searchtype']) && $v['searchtype']){
                $columns[$k] = $v['label'];
            }
        }

        $ext_columns = array(
            'order_id'=>$this->app->_('订单号'),
            'payment_id'=>$this->app->_('单号'),
        );
        
        return array_merge($columns, $ext_columns);
    }

    public function count($filter=null){
        if(isset($filter['time_from']) && $filter['time_from']){
            $filter['time_from'] = strtotime($filter['time_from']);
            $filter['time_to'] = (strtotime($filter['time_to'])+86400);
        }
        $sql = 'SELECT count(*) as _count FROM '.
            kernel::database()->prefix.'ectools_order_bills as B LEFT JOIN '.
            kernel::database()->prefix.'ectools_payments as P ON B.bill_id=P.payment_id LEFT JOIN '.
            kernel::database()->prefix.'ectools_refunds as R ON B.bill_id=R.refund_id 
        where pay_object=\'order\' and ((P.t_payed >='.$filter['time_from'].' and P.t_payed <='.$filter['time_to'].') or (R.t_payed >='.$filter['time_from'].' and R.t_payed <='.$filter['time_to'].'))'.$this->_filter_count($filter);
        $row = $this->db->select($sql);
        return intval($row[0]['_count']);
    }

    public function getlist($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        if(isset($filter['time_from']) && $filter['time_from']){
            $filter['time_from'] = strtotime($filter['time_from']);
            $filter['time_to'] = (strtotime($filter['time_to'])+86400);
        }
        $sql = '(SELECT rel_id,bill_type,bill_id,P.t_payed as order_time,P.money as order_amount,0 as profit FROM '.
            kernel::database()->prefix.'ectools_order_bills as B LEFT JOIN '.
            kernel::database()->prefix.'ectools_payments AS P ON B.bill_id=P.payment_id '.
            'WHERE bill_type=\'payments\' and pay_object=\'order\' and P.t_payed >='.$filter['time_from'].' and P.t_payed <='.$filter['time_to'].$this->_filter($filter).') '.
            'UNION ALL (SELECT rel_id,bill_type,bill_id,R.t_payed as order_time,R.money as order_amount,R.profit FROM '.
            kernel::database()->prefix.'ectools_order_bills as B LEFT JOIN '.
            kernel::database()->prefix.'ectools_refunds AS R ON B.bill_id=R.refund_id '.
            'WHERE (bill_type=\'refunds\' or bill_type=\'blances\') and pay_object=\'order\' and R.t_payed >='.$filter['time_from'].' and R.t_payed <='.$filter['time_to'].$this->_filter($filter).')';
        if($orderType)$sql.=' ORDER BY '.(is_array($orderType)?implode($orderType,' '):$orderType);

        $rows = $this->db->selectLimit($sql,$limit,$offset);
        $this->tidy_data($rows, $cols);
        return $rows;
    }

    public function _filter($filter,$tableAlias=null,$baseWhere=null){
        if(isset($filter['payment_id']) && $filter['payment_id']){
            $filter_sql = ' and bill_id LIKE \''.$filter['payment_id'].'%\'';
        }elseif(isset($filter['order_id']) && $filter['order_id']){
            $filter_sql = ' and rel_id LIKE \''.$filter['order_id'].'%\'';
        }elseif(isset($filter['status']) && $filter['status']){
            $filter_sql = ' and status = \''.$filter['status'].'\'';
        }else{
            $filter_sql = '';
        }
        return $filter_sql;
    }

    public function _filter_count($filter,$tableAlias=null,$baseWhere=null){
        if(isset($filter['payment_id']) && $filter['payment_id']){
            $filter_sql = ' and bill_id LIKE \''.$filter['payment_id'].'%\'';
        }elseif(isset($filter['order_id']) && $filter['order_id']){
            $filter_sql = ' and rel_id LIKE \''.$filter['order_id'].'%\'';
        }elseif(isset($filter['status']) && $filter['status']){
            $filter_sql = ' and (P.status = \''.$filter['status'].'\' or R.status = \''.$filter['status'].'\')';
        }else{
            $filter_sql = '';
        }
        return $filter_sql;
    }

    public function get_schema(){
        $schema = array (
            'columns' => array (
                'rel_id' => array (
                    'type' => 'bigint unsigned',
                    'required' => true,
                    'label' => app::get('b2c')->_( '订单号'),
                    'width' => 120,
                    'pkey' => true,
                    'default' => 0,
                    'editable' => false,
                    'realtype' => 'mediumint(8) unsigned',
                ),
                'bill_type' => array (
                    'type' => 
                    array (
                        'payments' =>  app::get('ectools')->_('付款单'),
                        'refunds' =>  app::get('ectools')->_('退款单'),
                        'blances' =>  app::get('ectools')->_('结算单'),
                    ),
                    'default' => 'payments',
                    'required' => true,
                    'label' => app::get('b2c')->_( '单据类型'),
                    'width' => 75,
                    'editable' => false,
                    'filtertype' => 'yes',
                    'filterdefault' => true,
                    'in_list' => true,
                ),
                'bill_id' => array (
                    'type' => 'varchar(20)',
                    'pkey' => true,
                    'required' => true,
                    'label' =>  app::get('b2c')->_('单号'),
                    'width' => 110,
                    'editable' => false,
                    'filtertype' => 'yes',
                    'filterdefault' => true,
                    'in_list' => true,
                    'default_in_list' => true,
                ),
                'order_time' => array (
                    'type' => 'time',
                    'label' => app::get('b2c')->_('时间'),
                    'width' => 130,
                    'editable' => false,
                    'in_list' => true,
                ),
                'order_amount' => array (
                    'type' => 'money',
                    'default' => '0',
                    'required' => true,
                    'label' =>app::get('b2c')->_('金额'),
                    'width' => 75,
                    'editable' => false,
                    'in_list' => true,
                    'default_in_list' => true,
                ),
                'profit' => array (
                    'type' => 'money',
                    'default' => '0',
                    'label' =>app::get('b2c')->_('抽成金额'),
                    'width' => 75,
                    'editable' => false,
                    'in_list' => true,
                    'default_in_list' => true,
                ),
            ),
            'idColumn' => 'rel_id',
            'in_list' => array (
                0 => 'rel_id',
                1 => 'bill_type',
                2 => 'bill_id',
                3 => 'order_time',
                4 => 'order_amount',
                5 => 'profit',
            ),
            'default_in_list' => array (
                0 => 'rel_id',
                1 => 'bill_type',
                2 => 'bill_id',
                3 => 'order_time',
                4 => 'order_amount',
                5 => 'profit',
            ),
        );
        return $schema;
    }
}
