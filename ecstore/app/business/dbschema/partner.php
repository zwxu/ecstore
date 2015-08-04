<?php 

 
$db['partner']=array ( 
    'columns' =>
    array (
        'link_id' =>
        array (
            'type' => 'number',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
        ),
        'store_id' =>
        array (
          'type' => 'number',
          'label' => app::get('b2c')->_('店铺id'),
          'width' => 150,
          'comment' => app::get('b2c')->_('店铺id'),
          'editable' => false,
          'in_list' => false,
          'default_in_list' => false,
        ),
        'link_name' =>
        array (
            'type' => 'varchar(128)',
            'required' => true,
            'default' => '',
            'label'=>app::get('site')->_('链接名称'),
            'width'=>100,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'href' =>
        array (
            'type' => 'varchar(255)',
            'required' => true,
            'default' => '',
            'label'=>app::get('site')->_('链接地址'),
            'width'=>180,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'image_url' =>
        array (
            'type' => 'varchar(255)',
            'label'=>app::get('site')->_('图片地址'),
            'width'=>120,
            'default_in_list'=>false,
            'in_list'=>false,
        ),
        'orderlist' =>
        array (
            'type' => 'number',
            'default' => 0,   
            'label'=>app::get('site')->_('排序'),
            'required' => true,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'hidden' =>
        array (
            'type' => array('true'=>app::get('site')->_('是'), 'false'=>app::get('site')->_('否')),
            'label'=>app::get('site')->_('隐藏'),
            'required' => true,
            'default' => 'false',
            'default_in_list'=>true,
            'in_list'=>true,
        ),
    ),
);
