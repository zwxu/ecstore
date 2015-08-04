<?php

 
class b2c_finder_member_lv{    
    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=b2c&ctl=admin_member_lv&act=addnew&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['member_lv_id'].'" target="dialog::{title:\''.app::get('b2c')->_('编辑会员等级').'\', width:680, height:250}">'.app::get('b2c')->_('编辑').'</a>';
        
    }  
}
