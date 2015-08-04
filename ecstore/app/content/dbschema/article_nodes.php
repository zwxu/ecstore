<?php

 

$db['article_nodes'] = array (
    'columns' =>
    array (
        'node_id' =>array (
            'type' => 'number',
            'required' => true,
            'label'=> app::get('content')->_('节点id'),
            'pkey' => true,
            'extra' => 'auto_increment',
            'width' => 10,
            'editable' => false,
            'in_list' => true,
        ),
        'parent_id' =>array (
            'type' => 'number',
            'required' => true,
            'default' => 0,
            'label'=> app::get('content')->_('父节点'),
            'width' => 10,
            'editable' => true,
            'in_list' => true,
        ),
        'node_depth' => array(
            'type' => 'tinyint(1)',
            'required' => true,
            'default' => 0,
            'label' => app::get('content')->_('节点深度'),
            'editable' => false,
        ),
        'node_name' =>array (
            'type' => 'varchar(50)',
            'required' => true,
            'default'=>'',
            'label'=> app::get('content')->_('节点名称'),
            'is_title' => true,
            'editable' => true,
            'default_in_list' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'node_pagename' =>array (
            'type' => 'varchar(50)',
            'label'=> app::get('content')->_('节点页面名'),
            'editable' => true,
            'in_list' => true,
        ),
        'node_path'=>array (
            'type' => 'varchar(200)',
            'label'=> app::get('content')->_('节点路径'),
            'editable' => false,
            'in_list' => false,
        ),
        'seo_title'=>array (
            'type' => 'varchar(100)',
            'label' => app::get('content')->_('SEO标题'),
            'editable' => true,
        ), 
        'seo_description' =>array(
            'type' => 'mediumtext',
            'label' => app::get('content')->_('SEO简介'),
            'editable' => true,
        ),
        'seo_keywords' =>array(
            'type' => 'varchar(200)',
            'label' => app::get('content')->_('SEO关键字'),
            'editable' => true,
        ),
        'has_children' => array(
            'type' => 'bool',
            'default' => 'false',
            'required' => true,
            'label' => app::get('content')->_('是否存在子节点'),
            'editable' => false,
            'in_list' => false,
        ),
        'ifpub'=>array (
            'type' => 'bool',
            'default' => 'false',
            'required' => true,
            'label' => app::get('content')->_('发布'),
            'editable' => true,
            'in_list' => true,
        ),
        'hasimage'=>array (
            'type' => 'bool',
            'default' => 'false',
            'required' => true,
            'label' => app::get('content')->_('图'),
            'editable' => true,
            'in_list' => true,
        ),
        'ordernum'=> array (
            'type' => 'number',
            'required' => true,
            'default' => 0,
            'editable' => true,
            'label' => app::get('content')->_('排序'),
        ),
        'homepage'=> array (
            'type' => 'bool',
            'default' => 'false',
            'editable' => true,
            'label' => app::get('content')->_('主页'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'uptime'=> array (
            'type' => 'time',
            'editable' => true,
            'label' => app::get('content')->_('修改时间'),
        ),
        'tmpl_path' =>array (
            'type' => 'varchar(50)',
            'label'=> app::get('content')->_('单独页模板'),
            'editable' => false,
        ),
       'list_tmpl_path' =>array (
            'type' => 'varchar(50)',
            'label'=> app::get('content')->_('列表页模板'),
            'editable' => false,
        ),
        'content' =>array (
            'type' => 'longtext',
            'label'=> app::get('content')->_('文章内容'),
            'editable' => true,
        ),
        'disabled' => array(
            'type' => 'bool',
            'required' => true,
            'default' => 'false',
            'editable' => true,
        ),
    ),
  'comment' => app::get('content')->_('文章节点表'),
  'index' => 
      array (
        'ind_disabled' => 
        array (
          'columns' => 
          array (
            0 => 'disabled',
          ),
        ),
        'ind_ordernum' => 
        array (
          'columns' => 
          array (
            0 => 'ordernum',
          ),
        ),
  ),
  'version' => '$Rev$',
);
