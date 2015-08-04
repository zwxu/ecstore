<?php

class business_mdl_brand extends dbeav_model{
    var $has_tag = true;

    public function __construct($app){
        parent::__construct($app);
        $this->use_meta();
    }

    //根据store_id获得store_name
    function getStoreNameBySid($store_id){

        $ostoremanger = app::get('business')->model('storemanger');


        $store_name = $ostoremanger->getList('store_name',array('store_id'=>$store_id));
        $store_name = $store_name[0]['store_name'];
        
        return $store_name;
    
    }
    
    //根据member_id获得store_id
    function getStoreIdByMid($member_id){

        $ostoremember = app::get('business')->model('storemember');

        $store_id = $ostoremember->getList('store_id',array('member_id'=>$member_id));
        $store_id = $store_id[0]['store_id'];
        
        return $store_id;
    
    }
     
     //根据store_id获得store_region
	 function getStoreregion($store_id){

        $ostoremanger = app::get('business')->model('storemanger');

        $store_region = $ostoremanger->getList('store_region',array('store_id'=>$store_id));
        $store_region = $store_region[0]['store_region'];
        $store_region = explode(',',$store_region);

		foreach($store_region as $k=>$v){
		    if(!$v){
			   unset($store_region[$k]);
			}
		}
        return $store_region;
    
    }

	//根据商家经营范围获得关联品牌
	function getBrndsByCtd($store_cat,$type_id){

		$otype_brands = app::get('b2c')->model('type_brand');

		$type_id = $this->get_type_ids($store_cat,$type_id);
     
        $brand_ids = array();
		foreach($type_id as $k=>$v){
			if($v){
			  $brand_id = $otype_brands->getList('brand_id',array('type_id'=>$v));
			  $brand_ids = array_merge($brand_id,$brand_ids);
			}
		}
		
		foreach($brand_ids as $v){
		    $brands[] = $v['brand_id'];
		}

		$brands = array_unique($brands);

		return  $brands;
		
	}

	   //获取分类下的type_id
    public function get_type_ids($cat_id,$type_id){
         
          $mdl_goodsCat = app::get('b2c')->model('goods_cat');
          $child_type_cat=array();
		  $parent_id_arr=array();
		  $child_type_ids=array();
		  $child_type_cat=$mdl_goodsCat->getList('type_id,cat_id,parent_id'); 
          $this->get_child_type_ids($child_type_cat,$parent_id_arr,$cat_id);

          foreach ($parent_id_arr as $key => $value) {
               $child_type_ids[]=$value['type_id'];
          }
          $child_type_ids=array_merge($child_type_ids,array($type_id));
          $child_type_ids=array_unique($child_type_ids);
          return $child_type_ids;
    }


    /*递归取得所有子类下的type_ids */
    public function get_child_type_ids(&$child_type_cat,&$parent_id_arr,$parent_id){
          $find_count=0;
          if(count($parent_id)>0){
             foreach ($parent_id as $k => $v) {
                foreach ($child_type_cat as $key => $value) {
                       if($value['parent_id']==$v){
                          $parent_id_arr[]=$value;
                          unset($child_type_cat[$key]);
                          $find_count++;
                      }else{

                      }
                }
            }
            unset($parent_id);
            if($find_count==0){
                return;
            }else{
                foreach ($parent_id_arr as $key => $value) {
                    $parent_id[]=$value['cat_id'];
                }
                $parent_id=array_unique($parent_id);
            }
          }
          $this->get_child_type_ids($child_type_cat,$parent_id_arr,$parent_id);


    }


}
