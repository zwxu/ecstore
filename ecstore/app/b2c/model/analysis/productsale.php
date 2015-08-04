<?php
class b2c_mdl_analysis_productsale extends dbeav_model{
    public function count($filter=null){
        $sql = 'SELECT count(*) as _count FROM (SELECT I.goods_id FROM '.
            kernel::database()->prefix.'b2c_orders as O LEFT JOIN '.
            kernel::database()->prefix.'b2c_order_items as I ON O.order_id=I.order_id LEFT JOIN '.
            kernel::database()->prefix.'b2c_goods as G ON G.goods_id=I.goods_id WHERE '.
            'O.disabled=\'false\' and O.pay_status!=\'0\' and '.$this->_filter($filter).' Group By I.goods_id) as tb';
        $row = $this->db->select($sql);
        return intval($row[0]['_count']);
    }

    public function getlist($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $sql = 'SELECT I.goods_id as rownum,G.name as pname,G.bn as bn,sum(I.nums) as saleTimes,sum(I.amount) as salePrice,I.goods_id FROM '.
            kernel::database()->prefix.'b2c_orders as O LEFT JOIN '.
            kernel::database()->prefix.'b2c_order_items as I ON O.order_id=I.order_id LEFT JOIN '.
            kernel::database()->prefix.'b2c_goods as G ON G.goods_id=I.goods_id WHERE '.
            'O.disabled=\'false\' and O.pay_status!=\'0\' and G.goods_id!=\'0\' and '.$this->_filter($filter).' Group By I.goods_id';

        if($orderType)$sql.=' ORDER BY '.(is_array($orderType)?implode($orderType,' '):$orderType);

        $rows = $this->db->selectLimit($sql,$limit,$offset);
        $this->tidy_data($rows, $cols);
        foreach($rows as $key=>$val){
            $rows[$key]['rownum'] = (string)($offset+$key+1);
            $sql = 'SELECT sum(number) as refund_num FROM '.
                kernel::database()->prefix.'b2c_reship_items as I LEFT JOIN '.
                kernel::database()->prefix.'b2c_products as P ON P.product_id=I.product_id '.
                'WHERE P.goods_id='.$val['rownum'];
            $row = $this->db->select($sql);
            $rows[$key]['refund_num'] = intval($row[0]['refund_num']);
            $rows[$key]['refund_ratio'] = isset($val['saleTimes'])?number_format($rows[$key]['refund_num']/$val['saleTimes'],2):0;
            $image = $this->app->model('goods')->dump($val['rownum'],'image_default_id,udfimg,thumbnail_pic');
            $rows[$key]['image_default_id'] = $image['image_default_id'];
            $rows[$key]['udfimg'] = $image['udfimg'];
            $rows[$key]['thumbnail_pic'] = $image['thumbnail_pic'];
            
        }
        return $rows;
    }

    public function get_reship_list($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        if(isset($filter['time_from']) && $filter['time_from']){
            $filter['time_from'] = strtotime($filter['time_from']);
            $filter['time_to'] = strtotime($filter['time_to'])+86400;
        }
        $sql = 'SELECT product_id,I.product_name as pname,sum(I.number) as saleTimes FROM '.
            kernel::database()->prefix.'b2c_reship as R LEFT JOIN '.
            kernel::database()->prefix.'b2c_reship_items as I ON R.reship_id=I.reship_id WHERE '.
            'R.disabled="false" and R.t_begin>='.intval($filter['time_from']).' and R.t_begin<'.intval($filter['time_to']).' Group By I.product_id';

        if($orderType)$sql.=' ORDER BY '.(is_array($orderType)?implode($orderType,' '):$orderType);

        $rows = $this->db->selectLimit($sql,$limit,$offset);
        $this->tidy_data($rows, $cols);
        foreach($rows as $key=>$val){
            $goods = $this->app->model('products')->dump($val['product_id'],'goods_id');
            $image = $this->app->model('goods')->dump($goods['goods_id'],'image_default_id,udfimg,thumbnail_pic');
            $rows[$key]['image_default_id'] = $image['image_default_id'];
            $rows[$key]['udfimg'] = $image['udfimg'];
            $rows[$key]['thumbnail_pic'] = $image['thumbnail_pic'];
            
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
        if(isset($filter['pname']) && $filter['pname']){
            $where[] = ' G.name LIKE \'%'.$filter['pname'].'%\'';
        }
        if(isset($filter['bn']) && $filter['bn']){
            $where[] = ' G.bn LIKE \''.$filter['bn'].'%\'';
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
                'pname' => array (
                    'type' => 'varchar(200)',
                    'pkey' => true,
                    'sdfpath' => 'pam_account/account_id',
                    'label' => app::get('b2c')->_('商品名称'),
                    'width' => 210,
                    'searchtype' => 'has',
                    'editable' => false,
                    'in_list' => true,
                    'default_in_list' => true,
                    'realtype' => 'mediumint(8) unsigned',
                ),
                'bn' => array (
                    'required' => true,
                    'default' => 0,
                    'label' => app::get('b2c')->_('商品编号'),
                    'sdfpath' => 'member_lv/member_group_id',
                    'width' => 120,
                    'searchtype' => 'has',
                    'type' => 'varchar(200)',
                    'editable' => true,
                    'filtertype' => 'bool',
                    'filterdefault' => 'true',
                    'in_list' => true,
                    'default_in_list' => true,
                    'realtype' => 'mediumint(8) unsigned',
                ),
                'saleTimes' => array (
                    'type' => 'number',
                    'label' => app::get('b2c')->_('销售量'),
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
                    'label' => app::get('b2c')->_('销售额'),
                    'width' => 110,
                    'editable' => false,
                    'filtertype' => 'number',
                    'in_list' => true,
                    'default_in_list' => true,
                    'realtype' => 'mediumint(8) unsigned',
                ),
                'refund_num' => array (
                    'type' => 'varchar(200)',
                    'sdfpath' => 'profile/gender',
                    'default' => 1,
                    'required' => true,
                    'label' => app::get('b2c')->_('退换货量'),
                    'orderby' => false,
                    'width' => 110,
                    'editable' => true,
                    'filtertype' => 'yes',
                    'in_list' => true,
                    'default_in_list' => true,
                    'realtype' => 'enum(\'0\',\'1\')',
                ),
                'refund_ratio' => array (
                    'label' => app::get('b2c')->_('退换货率'),
                    'width' => 110,
                    'type' => 'varchar(200)',
                    'orderby' => false,
                    'editable' => false,
                    'filtertype' => 'time',
                    'filterdefault' => true,
                    'in_list' => true,
                    'default_in_list' => true,
                    'realtype' => 'int(10) unsigned',
                ),
            ),
            'idColumn' => 'pname',
            'in_list' => array (
                0 => 'rownum',
                1 => 'pname',
                2 => 'bn',
                3 => 'saleTimes',
                4 => 'salePrice',
                5 => 'refund_num',
                6 => 'refund_ratio',
            ),
            'default_in_list' => array (
                0 => 'rownum',
                1 => 'pname',
                2 => 'bn',
                3 => 'saleTimes',
                4 => 'salePrice',
                5 => 'refund_num',
                6 => 'refund_ratio',
            ),
        );
        return $schema;
    }
}
