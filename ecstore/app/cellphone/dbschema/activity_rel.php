<?php
$db['activity_rel']=array (
  'columns' => 
  array (
    'act_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'rel_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
  ),
  'comment' => app::get('b2c')->_('活动关联表'),
);
