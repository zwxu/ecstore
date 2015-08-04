<?php 

 
$db['reship_items']=array (
  'columns' => 
  array (
    'item_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'reship_id' => 
    array (
      'type' => 'table:reship',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
	'order_item_id' => 
    array (
      'type' => 'table:order_items',
      'required' => false,
      'default' => 0,
      'editable' => false,
    ),
    'item_type' => 
    array (
      'type' => 
      array (
        'goods' => app::get('b2c')->_('商品'),
        'gift' => app::get('b2c')->_('赠品'),
        'pkg' => app::get('b2c')->_('捆绑商品'),
		'adjunct'=>app::get('b2c')->_('配件商品'),
      ),
      'default' => 'goods',
      'required' => true,
      'editable' => false,
    ),
    'product_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'product_bn' => 
    array (
      'type' => 'varchar(30)',
      'editable' => false,
      'is_title' => true,
    ),
    'product_name' => 
    array (
      'type' => 'varchar(200)',
      'editable' => false,
    ),
    'number' => 
    array (
      'type' => 'float',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
  ),
  'comment' => app::get('b2c')->_('发货/退货单明细表'),
  'version' => '$Rev: 40654 $',
);
