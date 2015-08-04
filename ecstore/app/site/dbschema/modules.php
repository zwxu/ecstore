<?php

$db['modules']=array (
    'columns' =>
    array (
        'id' =>
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
        ),
        'app' =>
        array (
            'type' => 'varchar(50)',
            'default' => '',
            'required' => true,
            'label' => app::get('site')->_('程序目录'),
            'width'=>80,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'ctl' =>
        array (
            'type' => 'varchar(50)',
            'default' => '',
            'required' => true,
            'label' => app::get('site')->_('控制器'),
            'width'=>80,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'path' => 
        array (
            'type' => 'varchar(50)',
            'default' => '',
            'required' => true,
            'label' => app::get('site')->_('路径标识'),
            'width'=>80,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'extension' => 
        array (
            'type' => 'varchar(10)',
            'default' => '',
            'required' => true,
            'label' => app::get('site')->_('扩展名'),
            'width'=>50,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'title' =>
        array (
            'type' => 'varchar(50)',
            'default' => '',
            'required' => true,
            'label' => app::get('site')->_('名称'),
            'width' => 100,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'allow_menus'=>
        array (
            'type' => 'varchar(255)',
            'default' => '',
            'required' => true,
            'label' => app::get('site')->_('允许菜单'),
            'width' => 200,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'is_native'=>
        array (
            'type' => 'bool',
            'required' => true,
            'default'=>'false',
            'label'=>app::get('site')->_('原生模块'),
            'width'=>80,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'enable' =>
        array (
            'type' => 'bool',
            'required' => true,
            'default'=>'false',
            'label'=>app::get('site')->_('启用'),
            'width'=>80,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'update_modified' => 
        array (
          'type' => 'time',
          'editable' => false,
        ),
    ),
);
