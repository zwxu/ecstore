<?php
class business_mdl_comment_goods_point extends b2c_mdl_comment_goods_point{
    function __construct(&$app){
        $this->app_current = $app;
        $this->app_b2c = app::get('b2c');
        parent::__construct($this->app_b2c);
    }
    
    function get_business_point_pass($store_id=null){
        if(!$store_id) return null;
        $sql = " SELECT t.*,sum(p.goods_point+0) as point ";
        $sql .= " FROM sdb_b2c_comment_goods_type AS t LEFT JOIN sdb_b2c_comment_goods_point AS p ON p.type_id=t.type_id ";
        $sql .= " JOIN sdb_b2c_member_comments AS c ON c.comment_id=p.comment_id AND c.type_id=p.goods_id ";
        $sql .= " AND c.store_id=".intval($store_id)." AND c.for_comment_id=0 AND c.comments_type='1' ";
        $sql .= " JOIN sdb_b2c_goods AS g ON c.type_id=g.goods_id AND g.marketable='true' AND c.store_id=g.store_id";
        $sql .= " GROUP BY t.type_id ";
        $objType = $this->app_b2c->model('comment_goods_type');
        $aPoint = array();
        foreach($objType->db->select($sql) as $rows){
            $aPoint[$rows['type_id']] = $rows;
        }
        $sql = " SELECT count(c.comment_id) as _count FROM sdb_b2c_member_comments AS c ";
        $sql .= " JOIN sdb_b2c_goods AS g ON c.type_id=g.goods_id AND g.marketable='true' AND c.store_id=g.store_id ";
        $sql .= " WHERE c.store_id=".intval($store_id)." AND c.for_comment_id=0 AND c.comments_type='1' ";
        $aCount = $objType->db->select($sql);
        $row = $objType->getList('*');
        foreach((array)$row as $val){
            $total = intval($aCount[0]['_count']);
            $num = $aPoint[$val['type_id']]['point'];
            if($total == 0 || $num==0) $data['avg_num'] = 0;
            else $data['avg_num'] =  number_format((float)$num/$total,1);
            $data['total'] = $total;
            $data['type_name'] = $val['name'];
            $data['avg'] = $this->star_class($data['avg_num']);
            $aData[] = $data;
        }
        return $aData;    
    }
    
    function get_business_point($store_id=null){
        if(!$store_id) return null;
        $sql = " select t.*,ifnull(p.avg_point,'') as avg_num,ifnull(p.avg_percent,'') as percent,q.pepo as total from sdb_b2c_comment_goods_type as t ".
            " left join sdb_business_comment_stores_point as p on p.type_id=t.type_id and p.store_id=".intval($store_id).
            " left join (select count(store_id) as pepo,type_id from sdb_business_comment_orders_point where store_id=".intval($store_id)." group by type_id) as q on p.type_id=q.type_id ";
        $aData = array();
        foreach($this->db->select($sql) as $rows){
            $rows['avg'] = $this->star_class($rows['avg_num']);
            $aData[] = $rows;
        }
        return $aData;
    }
    
    function get_single_point($goods_id=null){
        if(!$goods_id) return null;
        //$type_id = $this->totalType();
        $_singlepoint = $this->get_type_point(0,$goods_id);
        $_singlepoint['avg_num'] = $_singlepoint['avg'];
        if(!$_singlepoint) return null;
        else{
            $_singlepoint['avg'] = $this->star_class($_singlepoint['avg']);
            return $_singlepoint;
        }
    }
    
    function get_comment_point($comment_id=null){
        if(!$comment_id) return null;
        //$type_id = $this->totalType();
        $row = $this->getList('goods_point',array('comment_id' => $comment_id,'type_id' => 0));
        if($row) return $this->star_class($row[0]['goods_point']);
        return null;
    }
}