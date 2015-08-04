<?php 

 
$db['goods_type']=array (
  'columns' =>
  array (
    'type_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('类型序号'),
      'width' => 110,
      'editable' => false,
      'in_list' => false,
    ),
    'name' =>
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => app::get('b2c')->_('类型名称'),
      'is_title' => true,
      'width' => 150,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
      'searchtype' => 'has',
    ),
    'floatstore' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('小数型库存'),
      'width' => 75,
      'editable' => false,
      'in_list' => true,
    ),
    
    
    
    'alias' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'is_physical' =>
    array (
      'type' => 'intbool',
      'default' => '1',
      'required' => true,
      'label' => app::get('b2c')->_('实体商品'),
      'width' => 75,
      'editable' => false,
      'in_list' => true,
    'default_in_list' => true,  
    ),
    'schema_id' =>
    array (
      'type' => 'varchar(30)',
      'required' => true,
      'default' => 'custom',
      'hidden' => 1,
      'width' => 110,
      'editable' => false,
    ),
    'setting' =>
    array (
      'type' => 'serialize',
      'comment' => app::get('b2c')->_('类型设置'),
      'width' => 110,
      'editable' => false,
      'label' => app::get('b2c')->_('类型设置'),

    ),
    'minfo' =>
    array (
      'type' => 'serialize',
      'editable' => false,
    ),
    'params' =>
    array (
      'type' => 'serialize',
      'editable' => false,
    ),
    'sizetable' =>
    array (
      'type' => 'serialize',
      'editable' => false,
      'label' => app::get('b2c')->_('尺码表'),
    ),
    'dly_func' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'ret_func' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'reship' =>
    array (
      'default' => 'normal',
      'required' => true,
      'type' =>
      array (
        'disabled' => app::get('b2c')->_('不支持退货'),
        'func' => app::get('b2c')->_('通过函数退货'),
        'normal' => app::get('b2c')->_('物流退货'),
        'mixed' => app::get('b2c')->_('物流退货+函数式动作'),
      ),
      'editable' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    
    'is_def' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => app::get('b2c')->_('类型标示'),
      'width' => 110,
      'editable' => false,
      'in_list' => false,
    ),
    'lastmodify' =>
    array (
      'label' => app::get('b2c')->_('供应商最后更新时间'),
      'width' => 150,
      'type' => 'time',
      'hidden' => 1,
      'in_list' => false,
    ),
  ),
  'comment' => app::get('b2c')->_('商品类型表'),
  'index' =>
  array (
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
  ),
  'version' => '$Rev: 40654 $',
);
