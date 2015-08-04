<?php

 
class b2c_misc_task implements base_interface_task{

    function rule() {
	return '0 0 */1 * *';
    }

    function exec() {
	$this->clear_cart_objects();
    }

    function description() {
	return '删除cart_objects表拉圾数据';
    }
    
    /*
     * 删除cart_objects表垃圾数据 一周以前针对于非登录用户
     */
    private function clear_cart_objects() 
    {
        $time = strtotime('-7 days');
        $sql = "DELETE FROM sdb_b2c_cart_objects WHERE member_id='-1' AND time<=$time";
        app::get('b2c')->model('cart_objects')->db->exec( $sql );
    }//End Function 
    
    
    

}
