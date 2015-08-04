<?php



/**
 * brand 模板
 */
class b2c_mdl_brand extends dbeav_model{
    var $defaultOrder = array('ordernum',' DESC');
    var $has_many = array(
        'gtype' => 'type_brand:replace',
    );

    function __construct($app){
        parent::__construct($app);
        $this->use_meta();
    }


    function getBrandTypes($brandid){
		$brandid = intval($brandid);
        return $this->db->select('SELECT t.* FROM sdb_b2c_goods_type t LEFT JOIN sdb_b2c_type_brand b ON t.type_id = b.type_id
                WHERE brand_id = '.$brandid);
    }

    function getBidByType($typeid){
        return $this->db->select('SELECT brand_id FROM sdb_b2c_type_brand  WHERE type_id = '.$typeid);
    }

    function getDefinedType(){
        $oType = &$this->app->model('goods_type');
        $aType = $oType->getList('type_id,name,setting,is_def',null,-1,-1);
        foreach($aType as $row){
            if($row['is_def'] == 'true'){
                $brandType['default'] = $row;
            }else{
//                $row['setting'] = unserialize($row['setting']);
                if($row['setting']['use_brand']){
                    $brandType['custom'][] = $row;
                }
            }
        }
        return $brandType;
    }

    function brand_meta_register(){
        $col = array(
            'seo_info' => array(
                  'type' => 'serialize',
                  'label' => app::get('b2c')->_('seo设置'),
                  'width' => 110,
                  'editable' => false,
             ),
        );
        $this->meta_register($col);
    }

    function save( &$data,$mustUpdate = null ){
        $rs = parent::save($data,$mustUpdate);
        $this->brand2json();
        return $rs;
    }

    function brand2json($return=false){
        @set_time_limit(600);
        $contents=$this->db->select('SELECT brand_id,brand_name,brand_url,ordernum,brand_logo FROM sdb_b2c_brand WHERE disabled = \'false\' order by ordernum desc');
        if($return){
            base_kvstore::instance('b2c_goods')->store('goods_brand.data',$contents);
            return $contents;
        }else{
            return base_kvstore::instance('b2c_goods')->store('goods_brand.data',$contents);
        }
    }

    function getAll(){
        if(base_kvstore::instance('b2c_goods')->fetch('goods_brand.data', $contents) !== false){

            if(!is_array($contents)){
                if(($result=json_decode($contents,true))){
                    return json_decode($contents,true);
                }else{
                    return $this->brand2json(true);
                }
            }else{
                    return $contents;
            }
        }else{
            return $this->brand2json(true);
        }
    }

    function delete($filter){
        $rs =  parent::delete($filter);
        $this->brand2json();
        return $rs;
    }

    function pre_recycle($rows){
    	$oGoods = &$this->app->model('goods');
    	if(is_array($rows)){
	    	foreach($rows as $bk=>$bv){
				$cbrand = $oGoods->count(array('brand_id'=>$bv['brand_id']));
				if($cbrand >0){
	                 $this->recycle_msg = app::get('desktop')->_('该品牌下有商品');
	                 return false;
				}
	    	}
    	}
        return true;
    }

    function getBrandByCatId($cat_id,$limit=null){
        if(!is_array($cat_id)){
            $sql = 'SELECT b.* FROM sdb_b2c_brand as b left join sdb_b2c_type_brand as t on t.brand_id=b.brand_id left join sdb_b2c_goods_cat as c on c.type_id=t.type_id  WHERE c.cat_id='.intval($cat_id).' ORDER BY b.ordernum ASC';
        }else{
            $sql = 'SELECT b.* FROM sdb_b2c_brand as b left join sdb_b2c_type_brand as t on t.brand_id=b.brand_id left join sdb_b2c_goods_cat as c on c.type_id=t.type_id  WHERE c.cat_id in ('.implode(',',$cat_id).') ORDER BY b.ordernum ASC';
        }

        if($limit){
            $sql = $sql.' limit 0,'.$limit;
        }
        return $this->db->select($sql);
    }

    function getBrandsByTypeId($typeId){
        $typeId = intval($typeId);
        return $this->db->select('SELECT b.* FROM sdb_b2c_brand as b  LEFT JOIN sdb_b2c_type_brand as t ON  t.brand_id = b.brand_id
                WHERE t.type_id = '.$typeId);
    }


}
