<?php
class business_service_store_member_filter{
    public function __construct($app){
        $this->app=$app;
    }    
    function extend_filter(&$filter){
        $cat=kernel::single('desktop_user')->get_user_cat();
        if($cat!==false && !empty($cat['topCat'])){  
            $hasStore=array_filter(array_keys($filter),array($this, 'idFilter'));          
            $obj_storemanger=$this->app->model('storemanger');
            $store_id=$obj_storemanger->getList('store_id');
            $id=array();
            foreach($store_id as $v){
               $id[]=$v['store_id'];
            }                
            if(empty($hasStore)){
                $filter['store_id']=$id;
            }else{
                $key=$hasStore[0];
               if(!is_array($filter[$key])){
                   $filter[$key]=array($filter[$key]);
               }
               foreach($filter[$key] as $fk=>$fv){
                  if(!in_array($fv,$id)){
                      unset($filter[$key][$fv]);
                  }
               }
               if(empty($filter[$key])){
                   $filter['store_id']=$id;
               }
            }
        }     
        //end
    }
    function idFilter($value){
          return strpos($value,"store_id")===false? false:true;
    }
}
