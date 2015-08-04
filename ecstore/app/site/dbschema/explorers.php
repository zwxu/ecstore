<?php

$db['explorers']=array (
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
            'required' => true,
            'default' => '',
            'label' => app::get('site')->_('程序目录'),
            'width'=>80,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'title' =>
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'label'=>app::get('site')->_('名称'),
            'width'=>120,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'path' =>
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'label'=>app::get('site')->_('目录'),
            'width'=>120,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
    ),
);
