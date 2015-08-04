<?php 
$db['storegrade']=array ( 
  'columns' =>
  array (
    'grade_id' =>
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'grade_name' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('等级名称'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'is_title'=>true,
      'default_in_list' => true,
    ),

    'goods_num' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('允许发布商品数'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'is_title'=>true,
      'default_in_list' => true,
    ),

     'coupons_num' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('允许发行优惠数'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'is_title'=>true,
      'default_in_list' => true,
    ),


    'theme_num' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('可选模板套数'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'is_title'=>true,
      'default_in_list' => true,
    ),

    'grade_money' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('收费标准'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'is_title'=>true,
      'default_in_list' => true,
    ),

     'issue_money' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('保证金额'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'is_title'=>true,
      'default_in_list' => true,
    ),

     'issue_type' =>
    array (
      'type' =>
      array (
        0 => app::get('b2c')->_('卖场型旗舰店'),
        1 => app::get('b2c')->_('专卖店'),
        2 => app::get('b2c')->_('专营店'),
        3 => app::get('b2c')->_('品牌旗舰店'),
      ),
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('店铺类型'),
      'width' => 110,
      'comment' => app::get('b2c')->_('店铺类型'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
      'filterdefault'=>true,
    ),

    'experience' => 
    array (
      'label' => app::get('b2c')->_('所需经验值'),
      'type' => 'int(10)',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),

     'default_lv' => 
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('是否默认'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),


    'certification' => 
    array (
      'type' => 'intbool',
      'label' => app::get('b2c')->_('需要审核'),
      'default' => '0',
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

   'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),

    'last_modify' =>
    array (
      'type' => 'last_modify',
      'label' => app::get('b2c')->_('更新时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'd_order' =>
    array (
      'type' => 'number',
      'default' => 30,
      'required' => true,
      'label' => app::get('b2c')->_('排序'),
      'width' => 30,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'remark' => 
    array (
      'label' => app::get('b2c')->_('备注'),
      'type' => 'text',
      'width' => 75,
      'in_list' => true,
      'default_in_list' => false,
    ),
    )
);