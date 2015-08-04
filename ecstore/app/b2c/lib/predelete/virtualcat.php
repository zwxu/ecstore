<?php

 
class b2c_predelete_virtualcat{

    public function pre_delete($virtualcat_id) {               
        foreach( kernel::servicelist("b2c_pre_delete_virtualcat") as $object ) {
            if(is_object($object)){
                if( !method_exists($object,'pre_delete') ) continue;
                $arr = $object->pre_delete($virtualcat_id);
                if(!$arr[0]){
                    return $arr; 
                }
            }
        }

        return $arr;
    }
   
}
