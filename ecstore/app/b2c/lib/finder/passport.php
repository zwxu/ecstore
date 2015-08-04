<?php

 
class b2c_finder_passport{
    var $column_control = '配置';
    function column_control($row){
        return '<input type="button" onclick="new Dialog(\'index.php?app=b2c&ctl=admin_passport&act=setting&p[0]='.$row['passport_id'].'\')" value='.app::get('b2c')->_("配置").'>';
    }

}
