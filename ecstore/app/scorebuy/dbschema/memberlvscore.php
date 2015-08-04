<?php 

 
/**
* @table goods_lv_price;
*
* @package Schemas
* @version $
* @license Commercial
*/
$db['memberlvscore']=array (
  'columns' => 
  array (
    'id'=>array(
            'type'=>'mediumint(8)',
            'extra'=>'auto_increment',
            'pkey'=>'true',
            'label'=>__('序号'),
        ),
    'aid' => 
    array(
        'type' => 'table:scoreapply@scorebuy',
        'default' => 0,
        'required' => true,
        'editable' => false
    ),
    'gid'=>array(
        'type'=>'table:goods@b2c',
        'required'=>true,
        'label'=>__('活动商品名称'),
        'editable'=>false,
        'in_list'=>true,
        'default_in_list'=>true,
    ),
    'level_id' => 
    array (
      'type' => 'table:member_lv@b2c',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'score' => 
    array (
      'type' => 'number',
      'label' => '积分',
      'width' => 30,
      'editable' => false,
      'in_list' => true,
      'default_in_list'=>true,
    ),
    'price'=>array(
        'type'=>'money',
        'default'=>0,
        'label'=>__('商家参与会员价格'),
        'editable'=>false,
        'hidden'=>true,
        'in_list'=>true,
        'default_in_list'=>true,
    ),
    'last_price'=>array(
        'type'=>'money',
        'default'=>0,
        'label'=>__('最终活动会员价格'),
        'editable'=>false,
        'in_list'=>true,
        'default_in_list'=>true,
    ),
  ),
  'comment' => app::get('b2c')->_('商品会员等级积分'),
);
