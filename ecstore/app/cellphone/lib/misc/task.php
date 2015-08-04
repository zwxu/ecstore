<?php
  
class cellphone_misc_task implements base_interface_task{

    function rule() {
        return '*/1 * * * *';
    }

    function exec() {
        cellphone_misc_exec::delete_expire_data();
    }

    function description() {
        return '手机活动同步';
    }
}