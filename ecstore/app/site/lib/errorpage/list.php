<?php 

class site_errorpage_list
{
    
    
    
    public function getList($key='') {
        $arr_page_list = array();
        foreach( kernel::servicelist("site_display_errorpage.conf") as $object ) {
            if( !method_exists($object,'init_conf') ) continue;
            $arr = $object->init_conf();
            if( $key ) {
            	foreach( $arr as $row )
                	if( $row['key']==$key ) return $row;
            } else {
                $arr_page_list = array_merge( (array)$arr,$arr_page_list );
            }
        }
        return $arr_page_list;
    }
}