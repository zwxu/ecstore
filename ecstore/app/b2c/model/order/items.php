<?php

 

class b2c_mdl_order_items extends dbeav_model{
    /** 
     * ��дgetList����
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        $arr_list = parent::getList($cols,$filter,$offset,$limit,$orderType);
        $obj_extends_order_service = kernel::servicelist('b2c.api_order_extends_actions');
        if ($obj_extends_order_service)
        {
            foreach ($obj_extends_order_service as $obj)
                $obj->extend_item_list($arr_list);
        }
        
        return $arr_list;
    }
    
    /**
     * 重写订单导出方法
     * @param array $data
     * @param array $filter
     * @param int $offset
     * @param int $exportType
     */
    public function fgetlist_csv( &$data,$filter,$offset,$exportType =1 ){
        $limit = 100;
        if(!$data['title']){
            $data['title'] = '"订单ID","商品编号","商品名称","购买量","原始单价","实际单价","总金额","分类","抽成比率","积分"';
        }
		$where = $this->_filter($filter);
		$where = str_replace('`sdb_b2c_order_items`', 'oi', $where);
        $sql = "
select oi.order_id,g.bn,g.name,oi.nums,g.mktprice,oi.price,oi.amount,gc.cat_name,gc.profit_point,oi.score from sdb_b2c_order_items oi
left join sdb_b2c_orders o on oi.order_id=o.order_id  
left join sdb_b2c_goods g on oi.goods_id=g.goods_id 
left join sdb_b2c_goods_cat gc on g.cat_id=gc.cat_id
where {$where}
        ";
        if(!$list = $this->db->selectLimit($sql,$limit,$offset*$limit))return false;
        
        // $data['contents'] = array();
        foreach( $list as $line => $row ){
            $rowVal = array();
            foreach( $row as $col => $val ){

                if(strlen($val) > 8 && eregi("^[0-9]+$",$val)){
                    $val .= "\r";
                }
                $rowVal[] = addslashes(  $val  );
            }
            $data['contents'][] = '"'.implode('","',$rowVal).'"';
        }
        return true;

    }    
}
