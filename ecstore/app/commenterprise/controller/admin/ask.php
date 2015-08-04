<?php

 
class commenterprise_ctl_admin_ask extends desktop_controller{

    function __construct($app){
        $this->app= $app;
    }

    function checkAskdelete(){
        $type_id = $_POST['type_id'];
        $db = &kernel::database();
        $row = $db->select("select comment_id from sdb_b2c_member_comments where object_type='ask' and for_comment_id='0' and gask_type=".$type_id);
        if($row) echo "true";
        else echo "false";
    }
}