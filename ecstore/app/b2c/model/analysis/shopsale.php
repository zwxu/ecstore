<?php
class b2c_mdl_analysis_shopsale extends dbeav_model{
    public function get_order($filter=null){
        //订单成交量、订单成交额
        $sql = 'SELECT count(1) as saleTimes,sum(cost_item) as salePrice FROM '.
            kernel::database()->prefix.'b2c_orders where disabled=\'false\' and '.$this->_filter($filter);

        $row = $this->db->select($sql);
        return $row[0];
    }

    public function get_reship_num($filter=null){
        //商品退换量
        $sql = 'SELECT sum(T.number) as reship_num FROM '.
            kernel::database()->prefix.'b2c_reship_items as T LEFT JOIN '.
            kernel::database()->prefix.'b2c_reship as R ON T.reship_id=R.reship_id '.
            'where R.status=\'succ\' and R.t_begin >='.intval($filter['time_from']).' and R.t_begin <='.intval($filter['time_to']);
        $row = $this->db->select($sql);
        return $row[0]['reship_num'];
    }

    public function get_sale_num($filter=null){
        //商品销售总量
        $sql = 'SELECT sum(I.nums) as sale_num FROM '.
            kernel::database()->prefix.'b2c_orders as O LEFT JOIN '.
            kernel::database()->prefix.'b2c_order_items as I ON O.order_id=I.order_id WHERE '.
            'O.disabled="false" and '.$this->_filter($filter);
        $row = $this->db->select($sql);
        return $row[0]['sale_num'];
    }

    public function count($filter=null){
        $filter['time_from'] = strtotime(sprintf('%s 00:00:00', $filter['time_from']));
        $filter['time_to'] = strtotime(sprintf('%s 23:59:59', $filter['time_to']));
        $date_range = array();
        for($i=$filter['time_from']; $i<=$filter['time_to']; $i+=86400){
            $date_range[] = date("Y-m-d", $i);
        }
        return count($date_range);
    }

    public function getlist($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $filter['time_from'] = strtotime(sprintf('%s 00:00:00', $filter['time_from']));
        $filter['time_to'] = strtotime(sprintf('%s 23:59:59', $filter['time_to']));
        $date_range = array();
        for($i=$filter['time_from']; $i<=$filter['time_to']; $i+=86400){
            $date_range[] = date("Y-m-d", $i);
        }
        if($orderType == 'time desc'){
            $date_range = array_reverse($date_range);
        }
        if($limit > 0){
            $date_range = array_slice($date_range, $offset, $limit);  
        }
        $analysis_info = app::get('ectools')->model('analysis')->select()->columns('*')->where('service = ?', 'b2c_analysis_shopsale')->instance()->fetch_row();
        if($analysis_info){
            $obj = app::get('ectools')->model('analysis_logs')->select()->columns('*')->where('analysis_id = ?', $analysis_info['id']);
            $obj->where('time >= ?', $filter['time_from']);
            $obj->where('time <= ?', $filter['time_to']);
            if(isset($this->_params['type']))   $obj->where('type = ?', $params['type']);
            $rows = $obj->where('flag = ?', 0)->instance()->fetch_all();
            foreach($rows AS $row){
                $date = date('Y-m-d', $row['time']);
                $tmp[$date][$row['target']] = $row['value'];
            }
        }
        foreach($date_range AS $k=>$date){
            $data[$k] = array(
                    'time' => $date,
                    'saleTimes'=>($tmp[$date][1])?$tmp[$date][1]:0,
                    'salePrice'=>($tmp[$date][2])?floatval($tmp[$date][2]):0,
                    'refund_num'=>($tmp[$date][3])?$tmp[$date][3]:0,
                    'refund_ratio'=>($tmp[$date][4])?$tmp[$date][4]:0,
               );
        }
        $this->tidy_data($data, $cols);
        return $data;
    }

    public function _filter($filter,$tableAlias=null,$baseWhere=null){
        $where = array(1);
        if(isset($filter['time_from']) && $filter['time_from']){
            $where[] = ' createtime >='.$filter['time_from'];
        }
        if(isset($filter['time_to']) && $filter['time_to']){
            $where[] = ' createtime <'.$filter['time_to'];
        }
        if(isset($filter['ship_status']) && $filter['ship_status']){
            $where[] = ' ship_status =\''.$filter['ship_status'].'\'';
        }
        if(isset($filter['pay_status']) && $filter['pay_status']){
            $where[] = ' pay_status =\''.$filter['pay_status'].'\'';
        }

        return implode($where,' AND ');
    }

    public function get_schema(){
        $schema = array (
            'columns' => array (
                'time' => array (
                    'type' => 'varchar(200)',
                    'pkey' => true,
                    'label' => app::get('b2c')->_('日期'),
                    'width' => 130,
                    'editable' => false,
                    'in_list' => true,
                    'default_in_list' => true,
                    'realtype' => 'mediumint(8) unsigned',
                ),
                'saleTimes' => array (
                    'type' => 'number',
                    'label' => app::get('b2c')->_('订单成交量'),
                    'width' => 75,
                    'editable' => true,
                    'filtertype' => 'normal',
                    'filterdefault' => 'true',
                    'in_list' => true,
                    'is_title' => true,
                    'default_in_list' => true,
                    'realtype' => 'varchar(50)',
                    'orderby' => false,
                ),
                'salePrice' => array (
                    'type' => 'money',
                    'default' => 0,
                    'required' => true,
                    'label' => app::get('b2c')->_('订单成交额'),
                    'width' => 110,
                    'editable' => false,
                    'filtertype' => 'number',
                    'in_list' => true,
                    'default_in_list' => true,
                    'realtype' => 'mediumint(8) unsigned',
                    'orderby' => false,
                ),
                'refund_num' => array (
                    'type' => 'number',
                    'default' => 0,
                    'label' => app::get('b2c')->_('商品退换量'),
                    'width' => 110,
                    'editable' => false,
                    'hidden' => true,
                    'in_list' => true,
                    'default_in_list' => true,
                    'realtype' => 'mediumint(8) unsigned',
                    'orderby' => false,
                ),
                'refund_ratio' => array (
                    'type' => 'number',
                    'default' => 0,
                    'label' => app::get('b2c')->_('商品退换率'),
                    'width' => 110,
                    'editable' => false,
                    'hidden' => true,
                    'in_list' => true,
                    'default_in_list' => true,
                    'realtype' => 'mediumint(8) unsigned',
                    'orderby' => false,
                ),
            ),
            'idColumn' => 'time',
            'in_list' => array (
                0 => 'time',
                1 => 'saleTimes',
                2 => 'salePrice',
                3 => 'refund_num',
                4 => 'refund_ratio',
            ),
            'default_in_list' => array (
                0 => 'time',
                1 => 'saleTimes',
                2 => 'salePrice',
                3 => 'refund_num',
                4 => 'refund_ratio',
            ),
        );
        return $schema;
    }
}
