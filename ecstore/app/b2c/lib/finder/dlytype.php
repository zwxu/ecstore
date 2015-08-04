<?php

 
class b2c_finder_dlytype{

    var $column_control = '编辑';
    function column_control($row){
        return '<a href="index.php?app=b2c&ctl=admin_dlytype&act=showEdit&p[0]='.$row['dt_id'].'&finder_id=' . $_GET['_finder']['finder_id'] . '" target="_blank">'.app::get('b2c')->_('编辑').'</a>';
    }

}
