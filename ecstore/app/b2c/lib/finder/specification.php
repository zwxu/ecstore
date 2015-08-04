<?php

 
class b2c_finder_specification{

    var $column_control = '规格操作';
    function column_control($row){
        return '<a href=\'index.php?app=b2c&ctl=admin_specification&act=edit&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['spec_id'].'\'" target="dialog::{title:\''.app::get('b2c')->_('编辑规格').'\', width:800, height:420}">'.app::get('b2c')->_('编辑').'</a>';
    }

}
