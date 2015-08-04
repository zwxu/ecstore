<?php 
 $db['member_comments']=array (
  'columns' => 
  array (
    'comment_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'editable' => false,
      'default_in_list' => true,
    ),
    'for_comment_id' => 
    array (
      'type' => 'mediumint(8) ',
      'label' => '对m的回复',
      'default' => 0,
    ),
    'type_id' => 
    array (
      'type' => 'table:goods',
      'label' => '名称',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'order_id' => 
    array (
      'type' => 'table:orders',
      'label' => '订单编号',
      'in_list' => false,
      'default_in_list' => false,
    ),
    'object_type' => 
    array (
      'type' => 'enum(\'ask\', \'discuss\', \'buy\', \'message\', \'msg\', \'order\')',
      'label' => '类型',
      'default' => 'ask',
      'required' => true,
    ),
    'author_id' => 
    array (
      'type' => 'mediumint(8)',
      'in_list' => false,
      'label' => '作者ID',
      'default' => 0,
      'default_in_list' => false,
    ),
    'author' => 
    array (
      'type' => 'varchar(100)',
      'label' => '发表人',
      'searchtype' => 'has',
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
    ),
    'contact' => 
    array (
      'type' => 'varchar(255)',
      'label' => '联系方式',
      'width' => 110,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
    ),
    'mem_read_status' => 
    array (
      'type' => 'enum(\'false\', \'true\')',
      'label' => '会员阅读标识',
      'default' => 'false',
    ),
    'adm_read_status' => 
    array (
      'type' => 'enum(\'false\', \'true\')',
      'label' => '管理员阅读标识',
      'default' => 'false',
    ),
    'time' => 
    array (
      'type' => 'time',
      'in_list' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'label' => '时间',
    ),
    'lastreply' => 
    array (
      'type' => 'time',
      'label' => '最后回复时间',
    ),
    'reply_name' => 
    array (
      'type' => 'varchar(100)',
      'in_list' => true,
      'label' => '最后回复人',
      'default_in_list' => true,
    ),
    'inbox' => 
    array (
      'type' => 'bool',
      'label' => '收件箱',
      'default' => 'true',
    ),
    'track' => 
    array (
      'type' => 'bool',
      'label' => '发件箱',
      'default' => 'true',
    ),
    'has_sent' => 
    array (
      'type' => 'bool',
      'label' => '是否发送',
      'default' => 'true',
    ),
    'to_id' => 
    array (
      'type' => 'table:members',
      'default' => 0,
      'required' => true,
    ),
    'to_uname' => 
    array (
      'type' => 'varchar(100)',
      'default_in_list' => true,
    ),
    'title' => 
    array (
      'type' => 'varchar(255)',
      'in_list' => true,
      'label' => '标题',
      'is_title' => true,
      'searchtype' => 'has',
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'default_in_list' => true,
    ),
    'comment' => 
    array (
      'type' => 'longtext',
      'label' => '内容',
      'in_list' => true,
      'searchtype' => 'has',
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'default_in_list' => true,
    ),
    'ip' => 
    array (
      'type' => 'varchar(15)',
      'in_list' => true,
      'label' => 'IP',
      'default_in_list' => true,
    ),
    'display' => 
    array (
      'type' => 'bool',
      'in_list' => true,
      'label' => '前台是否显示',
      'filtertype' => 'bool',
      'default' => 'true',
      'default_in_list' => true,
    ),
    'gask_type' => 
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'editable' => false,
    ),
    'addon' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'p_index' => 
    array (
      'type' => 'tinyint(2)',
      'label' => 'p_index',
      'default_in_list' => true,
    ),
    'disabled' => 
    array (
      'type' => 'enum(\'false\', \'true\')',
      'default' => 'false',
      'default_in_list' => true,
    ),
    'comments_type' => 
    array (
      'type' => 
      array (
        0 => '解释',
        1 => '评论',
        2 => '回复',
        3 => '追加',
      ),
      'default' => '0',
      'required' => true,
      'label' => '评论类型',
      'editable' => false,
    ),
    'store_id' => 
    array (
      'type' => 'table:storemanger@business',
      'required' => false,
      'label' => '店铺名称',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
  ),
  'comment' => '咨询,评论,留言,短消息表',
  'engine' => 'innodb',
  'version' => '$Rev$',
  'index' => 
  array (
    'idx_store_id' => 
    array (
      'columns' => 
      array (
        0 => 'store_id',
      ),
    ),
  ),
);