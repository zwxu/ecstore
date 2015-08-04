<?php

 
class desktop_finder_favstar{
    function run($task,$ctl){
        $user_id = $ctl->user->user_id;
        $finder_model = (key($task));
        $rows = current($task);
        $old_fav_rows = app::get('desktop')->getConf('favstar.'.$finder_model.'.'.$user_id);
        $old_fav_rows = (array)$old_fav_rows;
        $fav_rows = array_merge($old_fav_rows,$rows);
        app::get('desktop')->setConf('favstar.'.$finder_model.'.'.$user_id,$fav_rows);

    }
}
