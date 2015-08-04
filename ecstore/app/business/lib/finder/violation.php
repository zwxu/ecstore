<?php
class business_finder_violation{
   
    function __construct($app){
        $this->app = $app;
        $this->violationcat = &$this->app-> model('violationcat');
    }

	var $column_control = '操作';
    var $column_control_width = 100;
 	function column_control($row){
        return '<a href="index.php?app=business&ctl=admin_violation&act=edit&violation_id='.$row['violation_id'].'&finder_id='.$_GET['_finder']['finder_id'].'"  target="blank">'.app::get('business')->_('编辑').'</a>';
    }

    var $column_violationcat = '违规类型';
    function column_violationcat($row){
        if($row['cat_id']){
                $violationcatname = $this ->violationcat -> getList('cat_name', array('cat_id' => $row['cat_id']));
                $violationcat = $violationcatname['0']['cat_name'];
        }
        return  $violationcat;

    }

}