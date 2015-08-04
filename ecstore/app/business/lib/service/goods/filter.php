<?php
class business_service_goods_filter{
    public function __construct($app){
        $this->app=$app;
    }    
    function extend_filter(&$filter){
        //数据级权限控制。
        $cat=kernel::single('desktop_user')->get_user_cat();
        if($cat!==false && !empty($cat['allCat'])){
            $cat_id=$cat['allCat'];
            $hasStore=array_filter(array_keys($filter),array($this, 'idFilter'));
            if(empty($hasStore)){
                    $filter['cat_id']=$cat_id;
            }else{
                $key=$hasStore[0];
                if(!is_array($filter[$key])){
                   $filter[$key]=array($filter[$key]);
                }
                foreach($filter[$key] as $fk=>$fv){
                  if(!in_array($fv,$cat_id)){
                      unset($filter[$key][$fv]);
                  }
                }
                if(empty($filter[$key])){
                   $filter['cat_id']=$cat_id;
                }
            }
        }
        
        //end
    }
    function idFilter($value){
          return strpos($value,"cat_id")===false? false:true;
    }
}
