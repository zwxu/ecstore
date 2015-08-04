<?php

 /**
 * 定时执行的类
 */
class recommended_misc_task implements base_interface_task{
	
    function rule() {
	return '0 0 1 */1 *';
    }

    function exec() {
	$this->auto_update();
    }

    function description() {
	return '更新设置的数据';
    }

    /**
	* 更新设置的数据
	* @access private
	*/
    private function auto_update(){
    	kernel::single( 'recommended_data_operaction' )->update();
		kernel::single( 'recommended_data_operaction' )->move();
    }
}