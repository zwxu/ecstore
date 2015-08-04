<?php 
$db['businessactivity'] = array(
    'columns'=>array(
        'id'=>array(
            'type'=>'mediumint(8)',
            'extra'=>'auto_increment',
            'pkey'=>'true',
            'label'=>__('序号'),
            'in_list'=>true,
        ),
        'gid'=>array(
            'type'=>'table:goods@b2c',
            'required'=>true,
            'label'=>__('活动商品名称'),
            'editable'=>false,
            'locked' => 1,
            'in_list'=>true,
            'default_in_list'=>true,
        ),
		'cat_id'=>array(
			'type'=>'table:goods_cat@b2c',
            'required'=>true,
            'label'=>__('商品类目'),
            'editable'=>false,
		),
        'aid'=>array(
            'type'=>'table:activity@timedbuy',
            'required'=>true,
            'label'=>__('所属活动'),
            'editable'=>false,
            'locked' => 1,
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'member_id'=>array(
            'type'=>'table:account@pam',
            'required'=>true,
            'label'=>__('申请人'),
            'editable'=>false,
            'locked' => 1,
            'in_list'=>true,
            'default_in_list'=>true,
        ),
		'store_id'=>array(
			'type'=>'table:storemanger@business',
            'required'=>true,
            'label'=>__('申请店铺'),
            'editable'=>false,
            'locked' => 1,
            'in_list'=>true,
            'default_in_list'=>true,
		),
        'price'=>array(
            'type'=>'money',
            'default'=>0,
            'label'=>__('价格'),
            'editable'=>false,
            'hidden'=>true,
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'nums' => array(
            'type'=>'mediumint(8)',
            'label'=>__('参加活动的商品数量'),
            'default'=>'0',
            'editable'=>false,
            'filtertype'=>'number',
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'remainnums'=>array(
            'type'=>'mediumint(8)',
            'label'=>__('参加活动的剩余商品数量'),
            'default'=>'0',
            'editable'=>false,
            'filtertype'=>'number',
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'presonlimit'=>array(
            'type'=>'mediumint(8)',
            'label'=>__('每人限购'),
            'editable'=>false,
            'filtertype'=>'number',
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'discription'=>array(
            'type'=>'varchar(200)',
            'label'=>__('活动描述'),
            'editable'=>false,
            'in_list'=>false,
            'default_in_list'=>false,
        ),
        'status'=>array(
            'type'=>array(
                1=>__('待审核'),
                2=>__('审核通过'),
                3=>__('审核不通过'),
            ),
            'default'=>'1',
            'label'=>__('活动状态'),
            'editable'=>false,
            'in_list'=>true,
            'default_in_list' => true,
        ),
        'remark'=>array(
           'type'=>'varchar(255)',
           'label'=>__('备注'),
           'editable'=>false,
           'in_list'=>true,
           'default_in_list' => true,
        ),
        'last_midifity'=>array(
            'type' => 'time',
            'label'=>__('最后修改时间'),
            'editable' => false,
            'required' => false
        ),
		
		'disabled'=>array(
			'type'=>'bool',
			'default'=>'false',
		),
    ),
	'index' => 
  array (
    'ind_aid' => 
    array (
      'columns' => 
      array (
        0 => 'aid',
      ),
    ),
    'ind_gid' => 
    array (
      'columns' => 
      array (
        0 => 'gid',
      ),
    ),
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
	'ind_store_id' => 
    array (
      'columns' => 
      array (
        0 => 'store_id',
      ),
    ),
  ),

);