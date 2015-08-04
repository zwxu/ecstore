<?php

 
class desktop_task{
    
    function install_options(){
        return array(
                'admin_uname'=>array('type'=>'text','vtype'=>'required','required'=>true,'title'=>'用户名','default'=>'admin'),
                'admin_password'=>array('type'=>'password','vtype'=>'required','required'=>true,'title'=>'密码'),
                'admin_password_re'=>array('type'=>'password','vtype'=>'required','vtype'=>'samePas','required'=>true,'title'=>'重复密码'),  
            );
    }
    
    function checkenv($options){
        if($options['admin_password']!=$options['admin_password_re']){
            echo "Error: 两次密码不一致\n";
            return false;    
        }
        if(empty($options['admin_password'])){
            echo "Error: 密码不能为空\n";
            return false;    
        }
        return true;
    }

    function post_install($options)
    {
        kernel::log('Create admin account');
        //设置用户体系，前后台互不相干
        pam_account::register_account_type('desktop','shopadmin','后台管理系统');
        
        
        //todo: 封装成更简单的函数
        $use_pass_data['login_name'] = $options['admin_uname'];
        $use_pass_data['createtime'] = time();
        $password = pam_encrypt::get_encrypted_password($options['admin_password'],pam_account::get_account_type('desktop'),$use_pass_data);
        $account = array(
            'pam_account'=>array(
                'login_name'=>$options['admin_uname'],
                'login_password'=>$password,
                'account_type'=>'shopadmin',
                'createtime'=>$use_pass_data['createtime'],
                ),
            'name'=>$options['admin_uname'],
            'super'=>1,
            'status'=>1
            );

        app::get('desktop')->model('users')->save($account);
    }

    function post_uninstall(){
        pam_account::unregister_account_type('desktop');
    }
}
