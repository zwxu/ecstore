<?php

 
class desktop_finder_magicvars{
    var $column_control = '编辑';
    function column_control($row){
        return '<a href="index.php?app=desktop&ctl=magicvars&act=edit&p[0]='.$row['var_name'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'].'" target="_blank">'.app::get('desktop')->_('编辑').'</a>';
    }

}
