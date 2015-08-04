<?php
/*
 * Created on 2011-12-23
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class poster_mdl_poster extends dbeav_model{
    public function modifier_poster_endtime($row){
         if(!$row){
            $row = "9999-12-31 59:59:00";
         }else{
            $row=date('Y-m-d H:i:s',$row);
        }
         return $row;
     }

     function get_poster_type(){
        $db = $this->get_schema();

        return $db['columns']['poster_type']['type'];
     }
 }

