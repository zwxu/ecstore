<?php

$db['files']=array (
  'columns' => 
  array (
    'file_id' => array('type'=>'number','pkey'=>true,'extra' => 'auto_increment'),
    'file_path' => array('type'=>'varchar(255)'),
    'file_type' =>array('type'=>array('private'=>'','public'=>''),'default'=>'public'),
    'last_change_time' => array('type'=>'last_modify'),
  ),
  'version' => '$Rev: 41137 $',
);
