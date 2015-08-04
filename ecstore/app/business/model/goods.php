<?php
class business_mdl_goods extends b2c_mdl_goods{
    function __construct($app){
        parent::__construct(app::get('b2c'));
    }
    
    function getMapTree($ss=0, $str='└',$cat_list){
        $var_ss = $ss;
        $var_str = $str;
        $objCat = app::get('b2c')->model('goods_cat');
        $retCat = $objCat->map($this->getCatTree($cat_list),$ss,$str,$no,$num);
        return $retCat;
    }
    
    function getCatTree($cat_list){
        $sql = 'SELECT o.cat_name AS text,o.cat_id AS id,o.parent_id AS pid,o.p_order,o.cat_path,
                    is_leaf,o.type_id as type,o.child_count,t.name as type_name FROM sdb_b2c_goods_cat o
                    LEFT JOIN sdb_b2c_goods_type t on t.type_id=o.type_id ';
        if(is_array($cat_list) && count($cat_list)>0){
            $sql .= ' WHERE o.cat_id in ('.implode(',', $cat_list).') ';
        }
        $sql .= 'ORDER BY o.p_order,o.cat_id';
        return $this->db->select($sql);
    }
    
    function getBrandTree($type_list){
        $sql = 'SELECT Distinct o.* FROM sdb_b2c_brand o JOIN sdb_b2c_type_brand b on o.brand_id=b.brand_id JOIN sdb_business_brand as bb on o.brand_id=bb.brand_id and bb.status=\'1\' and bb.type=\'1\' WHERE 1=1 ';
        if(is_array($type_list) && count($type_list)>0){
            $sql .= ' AND b.type_id in ('.implode(',', $type_list).') ';
        }
        $sql .= 'ORDER BY o.ordernum,o.brand_id';
        return $this->db->select($sql);
    }
    
    function getCats($cat_id){
        $objCat = app::get('b2c')->model('goods_cat');
        if(count($cat_id) == 1 && $cat_id[0] == 0){
            $aCat = $objCat->getList('cat_id',array('disabled'=>'false'),0,-1);
        }else{
            $aCat = $objCat->getList('cat_path,cat_id',array('cat_id'=>$cat_id));
            $pathplus='';
            if(count($aCat)){
                foreach($aCat as $v){
                    $pathplus.=' cat_path LIKE \''.($v['cat_path']).$v['cat_id'].',%\' OR';
                }
                $aCat = $this->db->select('SELECT cat_id FROM sdb_b2c_goods_cat WHERE '.$pathplus.' cat_id in (\''.implode("','",$cat_id).'\')');
            }
        }
        $aCatid = array();
        foreach((array)$aCat as $rows){
            $aCatid['cat_id'][] = $rows['cat_id'];
        }
        return $aCatid;
    }
    
    function getRegions($member_id){
        if(!$member_id) return;
        $sql = " SELECT a.store_id,a.store_region FROM sdb_business_storemanger AS a JOIN sdb_business_storemember AS b ";
        $sql .= " ON a.store_id=b.store_id AND b.member_id=".intval($member_id)." ";
        $sql .= " WHERE 1=1 ";
        foreach($this->db->select($sql) as $rows){
            $aRegion[$rows['store_id']] = $rows['store_region'];
        }
        return $aRegion;
    }
    
    function get_cat_list($cat_id){
        $aCat = $this->getCats($cat_id);
        return $this->getMapTree(0, '└', $aCat['cat_id']);
    }
    
    function getCustomCatTree($cat_list){
        $sql = 'SELECT o.cat_name AS text,o.custom_cat_id AS id,o.parent_id AS pid,o.p_order,o.cat_path,
                    is_leaf,o.type_id as type,o.child_count FROM sdb_business_goods_cat o ';
        if(is_array($cat_list) && count($cat_list)>0){
            $sql .= ' WHERE o.custom_cat_id in ('.implode(',', $cat_list).') ';
        }
        $sql .= 'ORDER BY o.p_order,o.custom_cat_id';
        return $this->db->select($sql);
        }
    
    function get_custom_cat_list($store_id){
        $objCat = app::get('business')->model('goods_cat');
        $cat_list = array();
        foreach($objCat->getList('custom_cat_id',array('store_id'=>$store_id,'disabled'=>'false')) as $rows){
            $cat_list[] = $rows['custom_cat_id'];
        }
        $objCat = app::get('b2c')->model('goods_cat');
        $retCat = $objCat->map($this->getCustomCatTree($cat_list),0,'└',$no,$num);
        return $retCat;
    }
    
    function get_subcat_list($cid, $pid=0){
        $filter = array('disabled'=>'false', 'parent_id'=>$pid);
        if(!is_array($cid)) $cid = (array)$cid;
        if(count($cid) == 1 && $cid[0] == 0){
        }else{
            $filter['cat_id'] = $cid;
        }
        $list = app::get('b2c')->model('goods_cat')->getList('*',$filter,0,-1);
        return $list;
    }
    
    function set_custom_cat($data){
        $sql = "insert into ".app::get('business')->model('goods_cat_conn')->table_name(true)." (goods_id,cat_id) value ";
        $temp = array();
        if(count($data)>0){
            foreach($data as $items){
                $temp[] = "({$items['goods_id']},{$items['cat_id']})";
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
    
    function get_order_info($order, $member){
        if(empty($order) || count($order)<0){
            return ;
        }
        $sql = " SELECT Distinct o.order_id,o.store_id,g.goods_id,g.bn ";
        $sql .= " FROM sdb_b2c_orders AS o LEFT JOIN sdb_b2c_order_items AS i ON o.order_id=i.order_id ";
        $sql .= " JOIN sdb_b2c_goods AS g ON i.goods_id=g.goods_id ";
        $sql .= " WHERE o.order_id in ('".implode("','",$order)."') AND member_id = ".intval($member);
        return $this->db->select($sql);
    }
    
    function updateOrderRank($order_id, $item, $num=1){
        switch($item){
            case "discuss":
            $item = "comments_count";
            break;
        }
        return $this->db->exec("UPDATE sdb_b2c_orders SET ".$item." = ".$item."+".intval($num)." WHERE order_id = '".$order_id."' ");
    }
    
    function get_comment_goods($gids, $order_id){
        if(!$gids || !$order_id) return;
        $sql = " SELECT g.goods_id,g.name,g.thumbnail_pic,g.udfimg,g.image_default_id,c.comment_id,c.comment,c.time ";
        $sql .= " FROM sdb_b2c_goods AS g JOIN sdb_b2c_member_comments AS c ON g.goods_id=c.type_id AND c.order_id='".$order_id."' AND c.comments_type='1' ";
        $sql .= " WHERE g.goods_id in ('".implode("','", $gids)."') ";
        return $this->db->select($sql);
    }
    
    
    var $ioSchema = array(
        'csv' => array(
            'bn:商品编号'=> 'bn',
            'ibn:规格货号' => array('bn','product'),
            'col:品牌' => 'brand/brand_id',
            'keywords:商品关键字' => 'keywords',
            'col:市场价' => array('price/mktprice/price','product'),
            'col:成本价' => array('price/cost/price','product'),
            'col:销售价' => array('price/price/price','product'),
            'col:商品名称' => 'name',
            'col:上架' => 'status',
            'col:规格' => 'spec',
            'col:商品简介' => 'brief',
            'col:运费' => 'freight_bear',
            'col:详细介绍' => 'description',
            'col:重量' => array('weight','product'),
            'col:单位' => 'unit',
            'col:库存' => array( 'store','product' )
        )
    );

    function io_title( $filter,$ioType='csv' ){
        $title = array();
        switch( $ioType ){
            case 'csv':
            default:
                $oGtype = $this->app->model('goods_type');
                if( $this->csvExportGtype[$filter['type_id']] )
                    $gType = $this->csvExportGtype[$filter['type_id']];
                   
                else
                    $gType = $oGtype->dump($filter['type_id'],'*');
                $this->oSchema['csv'][$filter['type_id']] = array(
                    app::get('b2c')->_('bn:商品编号') => 'bn',
                    app::get('b2c')->_('ibn:规格货号') => array('bn','product'),
                    app::get('b2c')->_('col:品牌') => 'brand/brand_name',
                    app::get('b2c')->_('keywords:商品关键字') => 'keywords',
                    app::get('b2c')->_('col:市场价') => array('price/mktprice/price','product'),
                    app::get('b2c')->_('col:成本价') => array('price/cost/price','product'),
                    app::get('b2c')->_('col:销售价') => array('price/price/price','product'),
                    app::get('b2c')->_('col:商品名称') => 'name',
                    app::get('b2c')->_('col:上架') => 'status',
                    app::get('b2c')->_('col:运费') => 'freight_bear',
                    app::get('b2c')->_('col:规格') => 'spec',
                    app::get('b2c')->_('col:库存') => 'store'
                );
                $oMlv = $this->app->model('member_lv');
                foreach( $oMlv->getList() as $mlv ){
                    $this->oSchema['csv'][$filter['type_id']]['price:'.$mlv['name']] = 'price/member_lv_price/'.$mlv['member_lv_id'].'/price';
                }
                $this->oSchema['csv'][$filter['type_id']] = array_merge(
                    $this->oSchema['csv'][$filter['type_id']],
                    array(
                        app::get('b2c')->_('col:商品简介') => 'brief',
                        app::get('b2c')->_('col:详细介绍') => 'description',
                        app::get('b2c')->_('col:重量') => 'weight',
                        app::get('b2c')->_('col:单位') => 'unit',
                    )
                );
                foreach( (array)$gType['props'] as $propsK => $props ){
                    $this->oSchema['csv'][$filter['type_id']]['props:'.$props['name']] = 'props/p_'.$propsK;
                }
                /*foreach( (array)$gType['params'] as $paramGroup => $paramItem ){
                    foreach( (array)$paramItem as $paramName => $paramValue ){
                        $this->oSchema['csv'][$filter['type_id']]['params:'.$paramGroup.'->'.$paramName] = 'params/'.$paramGroup.'/'.$paramName;
                    }
                }*/
                break;
        }
        $this->ioTitle['csv'][$filter['type_id']] = array_keys($this->oSchema['csv'][$filter['type_id']]);
        return $this->ioTitle['csv'][$filter['type_id']];
    }
    
    
    
    
    
    
}