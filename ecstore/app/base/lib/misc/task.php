<?php

 
class base_misc_task implements base_interface_task
{
    function rule(){
	return '0 0 */1 * *';
    }
    function exec(){
        base_kvstore::delete_expire_data();
    }

    function description()
    {
        return '删除过期数据';
    }
}
