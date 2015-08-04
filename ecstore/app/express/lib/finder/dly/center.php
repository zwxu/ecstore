<?php

 

class express_finder_dly_center
{
    var $column_editbutton = '操作';
    
    public function column_editbutton($row)
    {
        return '<a href="index.php?app=express&ctl=admin_delivery_center&act=showEdit&p[0]='.$row['dly_center_id'].'&finder_id='.$_GET['_finder']['finder_id'].'" target="dialog::{title:\''.app::get('express')->_('编辑').'\',width:.7,height:.8}">'.app::get('express')->_('编辑').'</a>';
    }
    
    public function column_editbutton_width()
    {
        return 161;
    }
}