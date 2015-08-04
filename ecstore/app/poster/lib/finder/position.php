<?php
/*
 * Created on 2011-12-16
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class poster_finder_position{
    var $column_edit='操作';
    function column_edit($row){
        return '<a href="index.php?app=poster&ctl=admin_position&act=showNew&id='.$row['position_id'].'&finder_id='.$_GET['_finder']['finder_id'].'" target="_blank">编辑</a>';
    }
}

