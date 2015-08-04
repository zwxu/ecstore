<?php 
/**
 * @package default
 * @author kxgsy163@163.com
 */
class proregister_promotion_getadvance
{
    
    function __construct( &$app )
    {
        $this->app = $app;
    }
    
    public function promotion( $member_id,$money ) {
    	$app = app::get('b2c');
        $message = '注册送预存款';
        $app->model('member_advance')->add($member_id,$money,$message,$errMsg);
    }
}