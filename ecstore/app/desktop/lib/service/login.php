<?php
class desktop_service_login{
    
    function __construct($app){
        $this->app = $app;
    }
    function listener_login($params){
        $account_type = pam_account::get_account_type('desktop');
        if($account_type === $params['type'] && $params['member_id'])
        {
            $users = app::get('desktop')->model('users') ;
            if($row = $users->getList('*',array('user_id'=>$params['member_id'])))
            {
                $sdf['lastlogin'] = time();
                $sdf['logincount'] = $row[0]['logincount']+1;
                $users->update($sdf,array('user_id'=>$params['member_id']));
            }
        }
    }
}
?>