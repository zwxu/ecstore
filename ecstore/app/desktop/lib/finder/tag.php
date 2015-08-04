<?php

 
class desktop_finder_tag{
    var $column_control = '编辑';
    function column_control($row){
       return '<a target="dialog::{title:\''.app::get('desktop')->_('链接编辑').'\', width:400, height:400}" href="index.php?app='.$_GET['app'].'&ctl='.$_GET['ctl'].'&act=tag_edit&type='.$_GET['type'].'&finder_id='.$_GET['_finder']['finder_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&_finder_name='.$_GET['_finder']['finder_id'].'&p[0]='.$row['tag_id'].'">'.app::get('desktop')->_('编辑').'</a>';
    }

}
