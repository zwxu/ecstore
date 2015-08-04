<?php
class package_mdl_attendactivity extends dbeav_model{
    function __construct($app){
        parent::__construct($app);
        $this->use_meta();
    }
  
    function save(&$data,$mustUpdate = null){
        $rs = parent::save($data,$mustUpdate);
        return $rs;
    }

    public function freez( $id,$quantity ){
        if( !$id || !$quantity ) return false;
        $arr = $this->dump( $id );
        $freez = $arr['freez'];
        $arr = array('freez'=>$freez+$quantity,'id'=>$id);
        return $this->save( $arr );
    }
    
    public function unfreez( $id,$quantity ){
        if( !$id || !$quantity ) return false;
        $arr = $this->dump( $id );
        $freez = $arr['freez'];
        $arr = array('freez'=>max(($freez-$quantity),0),'id'=>$id);
        return $this->save( $arr );
    }
    
    public function check_freez( $id,$quantity ){
        if( !$id || !$quantity ) return false;
        $arr = $this->dump( $id );
        $freez = $arr['freez'];
        $store = $arr['store'];
        if ($freez + $quantity > $store)
        return false;
        return true;
    }
    
    public function searchOptions(){
        $arr = parent::searchOptions();
        $arr = array_merge($arr,array(
                'goods_bn'=>app::get('b2c')->_('商品货号'),
                'goods_name'=>app::get('b2c')->_('商品名称'),
                'activity_name'=>app::get('b2c')->_('活动名称'),
                'store_name'=>app::get('b2c')->_('店铺名称'),
            ));

        return $arr;
    }
    
    public function _filter($filter,$tableAlias=null,$baseWhere=null){
        $filter_goods = array();
        $filter_products = array();
        if($filter['goods_bn']){
            $filter_goods['bn|has'] = $filter_products['bn|has'] = $filter['goods_bn'];
        }
        if($filter['goods_name']){
            $filter_goods['name|has'] = $filter['goods_name'];
        }
        $aGoods = array();
        if(!empty($filter_goods))$aGoods = array_merge($aGoods,(array)app::get('b2c')->model('goods')->getList('goods_id',$filter_goods,0,-1));
        if(!empty($filter_products))$aGoods = array_merge($aGoods,(array)app::get('b2c')->model('products')->getList('goods_id',$filter_products,0,-1));
        $aActivity = array();
        if($filter['activity_name']){
            $aActivity = app::get('package')->model('activity')->getList('act_id',array('name|has'=>$filter['activity_name']),0,-1);
        }
        $aStore = array();
        if($filter['store_name']){
            $aStore = app::get('business')->model('storemanger')->getList('store_id',array('store_name|has'=>$filter['store_name']),0,-1);
        }
        if(!empty($aGoods) || !empty($aActivity) || !empty($aStore)){
            $goods_id = array();
            foreach((array)$aGoods as $items){
                //$goods_id[] = " gid like '%,{$items['goods_id']},%' ";
                $goods_id[] = $items['goods_id'];
            }
            $act_id = array();
            foreach((array)$aActivity as $items){
                $act_id[] = $items['act_id'];
            }
            $store_id = array();
            foreach((array)$aStore as $items){
                $store_id[] = $items['store_id'];
            }
            //$sql = "select id from sdb_package_attendactivity where ".(empty($goods_id)?'1=0':implode('or',$goods_id))";
            $sql = "select distinct a.id  from sdb_package_attendactivity as a ";
            if(!empty($goods_id)) $sql .= " left join sdb_b2c_goods as g on g.goods_id in (".implode(',',$goods_id).") and LOCATE(concat(',',convert(g.goods_id,char),','),a.gid)>0 ";
            $sql .= " where 1=1 ";
            if(!empty($act_id)) $sql .= " and a.aid in (".implode(',',$act_id).") ";
            if(!empty($store_id)) $sql .= " and a.store_id in (".implode(',',$store_id).") ";
            $sql .= " group by a.id";
            $attend_id = array();
            foreach((array)$this->db->select($sql) as $items){
                $attend_id[] = $items['id'];
            }
            if(!empty($attend_id)) $filter['id'] = array_merge((array)$filter['id'],$attend_id);
        }elseif($filter['goods_bn'] || $filter['goods_name'] || $filter['activity_name'] || $filter['store_name']){
            $filter['id'] = 0;
        }
        unset($filter['goods_bn'],$filter['goods_name'],$filter['activity_name'],$filter['store_name']);
        $filter = parent::_filter($filter);
        return $filter;
    }
}