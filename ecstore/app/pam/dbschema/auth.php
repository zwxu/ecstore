<?php

$db['auth'] = array( 
    'columns'=>array(
        'auth_id'=>array('type'=>'number','pkey'=>true,'extra' => 'auto_increment',),
        'account_id'=>array('type'=>'table:account'), 
        'module_uid'=>array('type'=>'varchar(50)'),
        'module'=>array('type'=>'varchar(50)'),
        'data'=>array('type'=>'text'),
        /* 添加has_bind字段:是否绑定账号('N':未绑定,'Y':已绑定)  */
        'has_bind' => array(
            'type' => array(
                'Y' => app::get('pam')->_('已绑定'),
                'N' => app::get('pam')->_('未绑定'),
            ),
            'requierd' => true,
            'default' => 'N',
        ),
       
    ),
    'index' => array (
        'account_id' => array ('columns' => array ('module','account_id'),'prefix' => 'UNIQUE'),
        'module_uid' => array ('columns' => array ('module','module_uid'),'prefix' => 'UNIQUE'),
    ),
);
