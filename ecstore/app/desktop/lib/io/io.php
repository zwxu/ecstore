<?php

 
class desktop_io_io{

    function getTitle(&$cols){
        $title = array();
        foreach( $cols as $col => $val ){
            if( !$val['deny_export'] )
                $title[$col] = $val['label'].'('.$col.')';
        }
        return $title;
    }

}
