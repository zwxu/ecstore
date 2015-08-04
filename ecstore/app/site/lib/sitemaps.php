<?php


class site_sitemaps {
    
    
    
    
    public function create() {
        
        $o_sitemaps = base_kvstore::instance('site_sitemaps');
        $pageLimit = 1000;
        $arr = array();
        $count_sitemaps = 0;
        foreach( kernel::servicelist("site_maps") as $object ) {
           if ( !method_exists( $object,'get_arr_maps' ) ) continue;
           $arr = array_merge( $arr, (array)$object->get_arr_maps() );
           foreach( $arr as &$row) {
               $row['changefreq'] = 'daily';
               $row['time'] = time();
           }
           while(1) {
               if( count( $arr ) >= $pageLimit ) {
                   $catalog[] = true;
                   $o_sitemaps->store( count($catalog) , array_splice( $arr, 0, $pageLimit ) );
                   $count_sitemaps++; //sitemaps 总数
               } else  {
                   break;
               }
           }
        }
        
        $catalog[] = true;

        $o_sitemaps->store( count($catalog) , array_splice( $arr, 0, $pageLimit ) );
        $count_sitemaps++; //sitemaps 总数
        
        $count_sitemaps_old = null;
        $o_sitemaps->fetch( 'count', $count_sitemaps_old );
        if( $count_sitemaps_old > $count_sitemaps ) {
            for( $i=$count_sitemaps; $i<=$count_sitemaps_old; $i++ ) {
                $o_sitemaps->delete( $i );
            }
        }
        
        $o_sitemaps->store( 'count', $count_sitemaps );
    }
    
    
    
}