<?php

 

$db['article_bodys'] = array (
    'columns' =>
    array (
        'id' =>array (
            'type' => 'number',
            'required' => true,
            'label'=> app::get('content')->_('自增id'),
            'pkey' => true,
            'extra' => 'auto_increment',
            'width' => 10,
            'editable' => false,
            'in_list' => true,
        ),
        'article_id' =>array (
            'type' => 'table:article_indexs',
            'required' => true,
            'label'=> app::get('content')->_('文章id'),
            'editable' => false,
            'in_list' => true,
        ),
        'tmpl_path' =>array (
            'type' => 'varchar(50)',
            'label'=> app::get('content')->_('单独页模板'),
            'editable' => false,
        ),
        'content' =>array (
            'type' => 'longtext',
            'label'=> app::get('content')->_('文章内容'),
            'editable' => true,
            'in_list' => true,
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
        'goods_info' => array(
            'type' => 'serialize',
            'label' => app::get('content')->_('关联产品'),
        ),
        'hot_link' => array(
            'type' => 'serialize',
            'label' => app::get('content')->_('热词'),
        ),
        'length' => array(
            'type' => 'int unsigned',
            'label' => app::get('content')->_('内容长度'),
        ),
        'image_id' => array(
            'type' => 'varchar(32)',
            'required' => false,
            'label' => app::get('content')->_('图片id'),
        ),
  ),
  'comment' => app::get('content')->_('文章节点表'),
  'index' => 
      array (
        'ind_article_id' => 
        array (
          'columns' => 
          array (
            0 => 'article_id',
          ),
          'prefix' => 'unique',
        ),
  ),
  'version' => '$Rev$',
);
