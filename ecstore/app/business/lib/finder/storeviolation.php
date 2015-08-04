<?php
class business_finder_storeviolation{
   
    function __construct($app){
        $this->app = $app;
        $this->violationcat = &$this->app-> model('storeviolation');
    }

    var $column_violationcat = '违规分类';
    function column_violationcat($row){
        if($row['cat_id']){
                $violationcatname = $this ->violationcat ->getparentcat($row['cat_id']);
                if($violationcatname){
                    $violationcat = $violationcatname['0']['cat_name'];
                }
        }
        return  $violationcat;

    }



}