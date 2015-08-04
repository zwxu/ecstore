<?php

 
class b2c_finder_brand{

    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=b2c&ctl=admin_brand&act=edit&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['brand_id'].'" target="_blank">'.app::get('b2c')->_('编辑').'</a>';
    }
    
}
