<?php

 
$db['hasrole']=array (
  'columns' => 
  array (
    'user_id' => 
    array (
      'type' => 'table:users',
      'required' => true,
      'pkey' => true,
    ),
    'role_id' => 
    array (
      'type' => 'table:roles',
      'required' => true,
      'pkey' => true,
    ),
  ),
  'version' => '$Rev: 40654 $',
);

