<?php 
/**
 * @package default
 * @author kxgsy163@163.com
 */
class proregister_promotion_getscore
{
    
    function __construct( &$app )
    {
        $this->app = $app;
    }
    
    public function promotion( $member_id,$point ) {
        if( !$this->get_status() ) return true;
        $app = app::get('b2c');
        $reason_type = 'register_score';
        $app->model('member_point')->change_point($member_id,$point,$errMsg,$reason_type,2,$member_id,$member_id);
    }
    
    public function get_status() {
        if(app::get('b2c')->getConf('site.get_policy.method')==1) return false;
        return true;
    }
}