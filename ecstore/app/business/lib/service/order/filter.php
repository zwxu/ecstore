<?php
class business_service_order_filter{
    public function __construct($app){
        $this->app=$app;
    }    
    function extend_filter(&$filter){
    
        //后台数据权限过滤
        $cat=kernel::single('desktop_user')->get_user_cat();
        
        $obj_store = $this->app->model('storemanger');
        $store_id=array();
        if($cat!==false && !empty($cat['topCat'])){
            
            $store_filter=array();
            if (isset($filter) && $filter && is_array($filter) && array_key_exists('storemanger_store_name', $filter))
            {            
                $store_filter = array(
                    'store_name|has'=>$filter['storemanger_store_name'],
                );            
                unset($filter['storemanger_store_name']);
            }
            $row_store = $obj_store->getList('store_id',$store_filter);
            foreach($row_store as $v){
                    $store_id[]=$v['store_id'];
            }
        }else{
            if (isset($filter) && $filter && is_array($filter) && array_key_exists('storemanger_store_name', $filter))
            {            
                $store_filter = array(
                    'store_name|has'=>$filter['storemanger_store_name'],
                );            
                unset($filter['storemanger_store_name']);
                $row_store = $obj_store->getList('store_id',$store_filter);
                foreach($row_store as $v){
                    $store_id[]=$v['store_id'];
                }
            }
        }
        if(!empty($store_id)){
            $hasStore=array_filter(array_keys($filter),array($this, 'idFilter'));
            if(empty($hasStore)){
                    $filter['store_id']=$store_id;
            }else{
                $key=$hasStore[0];
                if(!is_array($filter[$key])){
                   $filter[$key]=array($filter[$key]);
                }
                foreach($filter[$key] as $fk=>$fv){
                  if(!in_array($fv,$store_id)){
                      unset($filter[$key][$fv]);
                  }
                }
                if(empty($filter[$key])){
                   $filter['store_id']=$store_id;
                }
            }
        }
        //end
    }
    function idFilter($value){
          return strpos($value,"store_id")===false? false:true;
      }
}
