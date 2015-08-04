<?php

 
/**
 * 定时执行的任务方法列表
 * 
 * @version 0.1
 * @package ectools.lib.misc
 */
class ectools_misc_task implements base_interface_task{

    function rule() {
	return '0 */1 * * *';
    }

    function exec() {
        kernel::single('ectools_analysis_task')->analysis_hour();
        kernel::single('ectools_analysis_task')->analysis_day();
    }

    function description() {
	return 'ectools分析统计';
    }
}
