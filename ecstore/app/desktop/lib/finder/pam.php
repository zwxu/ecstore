<?php

 
class desktop_finder_pam{
    var $column_control = '配置';
     function __construct($app){
        $this->app= $app;
    }
    function column_control($row){
        $render = $this->app->render();
        $render->pagedata['passport_id'] = $row['passport_id'];
        return $render->fetch('href.html');
        #return '<input type="button" onclick="new Dialog(\'index.php?app=desktop&ctl=pam&act=setting&finder_id=<{$name}>&p[0]='.$row['passport_id'].'\')" value="配置">';
    }

}
