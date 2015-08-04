<?php
 
class business_finder_theme{    
    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=business&ctl=admin_theme&act=save_page&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['theme_id'].'" target="dialog::{title:\''.app::get('b2c')->_('编辑模版信息').'\', width:460, height:400}">'.app::get('business')->_('编辑').'</a>';
        
    }  
}
