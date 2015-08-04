<?php 
 
$db['theme']=array ( 
  'columns' => 
    array (
      'theme_id' => 
      array (
        'type' => 'int unsigned',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'editable' => false,
      ),
      'name' =>
      array (
        'type' => 'varchar(200)',
        'required' => true,
        'default' => '',
        'label' => app::get('b2c')->_('模版名'),
        'is_title' => true,
        'width' => 150,
        'in_list' => true,
        'default_in_list' => true,
        'editable' => true,
      ),
      'shop_tmpl_id' => 
      array (
        'type' => 'table:themes_tmpl@site',
        'required' => false,
        'label' => '店铺首页',
        'width' => 310,
        'editable' => false,
        'default_in_list' => true,
        'in_list' => true,
      ),
      'gallery_tmpl_id' => 
      array (
        'type' => 'table:themes_tmpl@site',
        'required' => false,
        'label' => '商品搜索页',
        'width' => 310,
        'editable' => false,
        'default_in_list' => true,
        'in_list' => true,
      ),
      'image' =>
        array (
          'type' => 'varchar(32)',
          'label' => app::get('b2c')->_('预览图'),
          'width' => 75,
          'hidden' => true,
          'editable' => false,
          'in_list' => false,
        ),
    )
);
