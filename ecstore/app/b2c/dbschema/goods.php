<?php 
 $db['goods']=array (
  'columns' => 
  array (
    'goods_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'bn' => 
    array (
      'type' => 'varchar(200)',
      'label' => '商品编号',
      'width' => 110,
      'searchtype' => 'head',
      'editable' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'name' => 
    array (
      'type' => 'varchar(200)',
      'required' => true,
      'default' => '',
      'label' => '商品名称',
      'is_title' => true,
      'width' => 310,
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'custom',
      'filterdefault' => true,
      'filtercustom' => 
      array (
        'has' => '包含',
        'tequal' => '等于',
        'head' => '开头等于',
        'foot' => '结尾等于',
      ),
      'in_list' => true,
      'default_in_list' => true,
      'order' => '1',
    ),
    'price' => 
    array (
      'type' => 'money',
      'sdfpath' => 'product[default]/price/price/price',
      'default' => '0',
      'required' => true,
      'label' => '销售价',
      'width' => 75,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
      'in_list' => true,
      'orderby' => true,
    ),
    'type_id' => 
    array (
      'type' => 'table:goods_type',
      'sdfpath' => 'type/type_id',
      'label' => '类型',
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'cat_id' => 
    array (
      'type' => 'table:goods_cat',
      'required' => true,
      'sdfpath' => 'category/cat_id',
      'default' => 0,
      'label' => '分类',
      'width' => 75,
      'editable' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'orderby' => true,
    ),
    'brand_id' => 
    array (
      'type' => 'table:brand',
      'sdfpath' => 'brand/brand_id',
      'label' => '品牌',
      'width' => 75,
      'editable' => true,
      'hidden' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'marketable' => 
    array (
      'type' => 'bool',
      'default' => 'true',
      'sdfpath' => 'status',
      'required' => true,
      'label' => '上架',
      'width' => 30,
      'editable' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'store' => 
    array (
      'type' => 'decimal(20,2)',
      'label' => '库存',
      'width' => 30,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'store_freeze' => 
    array (
      'type' => 'decimal(20,2)',
      'label' => '冻结库存',
      'width' => 30,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'notify_num' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'label' => '缺货登记',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'uptime' => 
    array (
      'type' => 'time',
      'depend_col' => 'marketable:true:now',
      'label' => '上架时间',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'downtime' => 
    array (
      'type' => 'time',
      'depend_col' => 'marketable:false:now',
      'label' => '下架时间',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'last_modify' => 
    array (
      'type' => 'last_modify',
      'label' => '更新时间',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'orderby' => true,
    ),
    'p_order' => 
    array (
      'type' => 'number',
      'default' => 30,
      'required' => true,
      'label' => '排序',
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
      'orderby' => true,
    ),
    'd_order' => 
    array (
      'type' => 'number',
      'default' => 30,
      'required' => true,
      'label' => '排序',
      'width' => 30,
      'editable' => true,
      'in_list' => true,
      'orderby' => true,
    ),
    'score' => 
    array (
      'type' => 'float unsigned',
      'sdfpath' => 'gain_score',
      'label' => '积分',
      'width' => 30,
      'editable' => false,
      'in_list' => true,
    ),
    'cost' => 
    array (
      'type' => 'money',
      'sdfpath' => 'product[default]/price/cost/price',
      'default' => '0',
      'required' => true,
      'label' => '成本价',
      'width' => 75,
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
    ),
    'mktprice' => 
    array (
      'type' => 'money',
      'sdfpath' => 'product[default]/price/mktprice/price',
      'label' => '市场价',
      'width' => 75,
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
    ),
    'weight' => 
    array (
      'type' => 'decimal(20,3)',
      'sdfpath' => 'product[default]/weight',
      'label' => '重量',
      'width' => 75,
      'editable' => false,
      'in_list' => true,
    ),
    'unit' => 
    array (
      'type' => 'varchar(20)',
      'sdfpath' => 'unit',
      'label' => '单位',
      'width' => 30,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'brief' => 
    array (
      'type' => 'varchar(255)',
      'label' => '商品简介',
      'width' => 110,
      'hidden' => false,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'goods_type' => 
    array (
      'type' => 
      array (
        'normal' => '普通商品',
        'bind' => '捆绑商品',
        'gift' => '赠品',
      ),
      'sdfpath' => 'goods_type',
      'default' => 'normal',
      'required' => true,
      'label' => '销售类型',
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
    ),
    'image_default_id' => 
    array (
      'type' => 'varchar(32)',
      'label' => '默认图片',
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'udfimg' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'label' => '是否用户自定义图',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'thumbnail_pic' => 
    array (
      'type' => 'varchar(32)',
      'label' => '缩略图',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'small_pic' => 
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'big_pic' => 
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'intro' => 
    array (
      'type' => 'longtext',
      'sdfpath' => 'description',
      'label' => '详细介绍',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => false,
    ),
    'store_place' => 
    array (
      'type' => 'varchar(255)',
      'label' => '库位',
      'sdfpath' => 'product[default]/store_place',
      'width' => 30,
      'editable' => false,
      'hidden' => true,
    ),
    'min_buy' => 
    array (
      'type' => 'number',
      'label' => '起定量',
      'width' => 30,
      'editable' => false,
      'in_list' => false,
    ),
    'package_scale' => 
    array (
      'type' => 'decimal(20,2)',
      'label' => '打包比例',
      'width' => 30,
      'editable' => false,
      'in_list' => false,
    ),
    'package_unit' => 
    array (
      'type' => 'varchar(20)',
      'label' => '打包单位',
      'width' => 30,
      'editable' => false,
      'in_list' => false,
    ),
    'package_use' => 
    array (
      'type' => 'intbool',
      'label' => '是否开启打包',
      'width' => 30,
      'editable' => false,
      'in_list' => false,
    ),
    'score_setting' => 
    array (
      'type' => 
      array (
        'percent' => '百分比',
        'number' => '实际值',
      ),
      'default' => 'number',
      'editable' => false,
    ),
    'nostore_sell' => 
    array (
      'type' => 'intbool',
      'default' => '0',
      'label' => '是否开启无库存销售',
      'width' => 30,
      'editable' => false,
    ),
    'goods_setting' => 
    array (
      'type' => 'serialize',
      'label' => '商品设置',
      'deny_export' => true,
    ),
    'spec_desc' => 
    array (
      'type' => 'serialize',
      'label' => '物品',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
    ),
    'params' => 
    array (
      'type' => 'serialize',
      'editable' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'rank_count' => 
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'comments_count' => 
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'view_w_count' => 
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'view_count' => 
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'count_stat' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'buy_count' => 
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'buy_w_count' => 
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'p_1' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_1/value',
      'editable' => false,
    ),
    'p_2' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_2/value',
      'editable' => false,
    ),
    'p_3' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_3/value',
      'editable' => false,
    ),
    'p_4' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_4/value',
      'editable' => false,
    ),
    'p_5' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_5/value',
      'editable' => false,
    ),
    'p_6' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_6/value',
      'editable' => false,
    ),
    'p_7' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_7/value',
      'editable' => false,
    ),
    'p_8' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_8/value',
      'editable' => false,
    ),
    'p_9' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_9/value',
      'editable' => false,
    ),
    'p_10' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_10/value',
      'editable' => false,
    ),
    'p_11' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_11/value',
      'editable' => false,
    ),
    'p_12' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_12/value',
      'editable' => false,
    ),
    'p_13' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_13/value',
      'editable' => false,
    ),
    'p_14' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_14/value',
      'editable' => false,
    ),
    'p_15' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_15/value',
      'editable' => false,
    ),
    'p_16' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_16/value',
      'editable' => false,
    ),
    'p_17' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_17/value',
      'editable' => false,
    ),
    'p_18' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_18/value',
      'editable' => false,
    ),
    'p_19' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_19/value',
      'editable' => false,
    ),
    'p_20' => 
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_20/value',
      'editable' => false,
    ),
    'p_21' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_21/value',
      'editable' => false,
    ),
    'p_22' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_22/value',
      'editable' => false,
    ),
    'p_23' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_23/value',
      'editable' => false,
    ),
    'p_24' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_24/value',
      'editable' => false,
    ),
    'p_25' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_25/value',
      'editable' => false,
    ),
    'p_26' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_26/value',
      'editable' => false,
    ),
    'p_27' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_27/value',
      'editable' => false,
    ),
    'p_28' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_28/value',
      'editable' => false,
    ),
    'p_29' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_29/value',
      'editable' => false,
    ),
    'p_30' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_30/value',
      'editable' => false,
    ),
    'p_31' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_31/value',
      'editable' => false,
    ),
    'p_32' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_32/value',
      'editable' => false,
    ),
    'p_33' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_33/value',
      'editable' => false,
    ),
    'p_34' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_34/value',
      'editable' => false,
    ),
    'p_35' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_35/value',
      'editable' => false,
    ),
    'p_36' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_36/value',
      'editable' => false,
    ),
    'p_37' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_37/value',
      'editable' => false,
    ),
    'p_38' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_38/value',
      'editable' => false,
    ),
    'p_39' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_39/value',
      'editable' => false,
    ),
    'p_40' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_40/value',
      'editable' => false,
    ),
    'p_41' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_41/value',
      'editable' => false,
    ),
    'p_42' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_42/value',
      'editable' => false,
    ),
    'p_43' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_43/value',
      'editable' => false,
    ),
    'p_44' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_44/value',
      'editable' => false,
    ),
    'p_45' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_45/value',
      'editable' => false,
    ),
    'p_46' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_46/value',
      'editable' => false,
    ),
    'p_47' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_47/value',
      'editable' => false,
    ),
    'p_48' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_48/value',
      'editable' => false,
    ),
    'p_49' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_49/value',
      'editable' => false,
    ),
    'p_50' => 
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_50/value',
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
    'goods_state' => 
    array (
      'type' => 
      array (
        'new' => '全新',
        'used' => '二手',
      ),
      'required' => true,
      'default' => 'new',
      'label' => '是否全新',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'buy_m_count' => 
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'view_m_count' => 
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'fav_count' => 
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'freight_bear' => 
    array (
      'type' => 
      array (
        'business' => '商家',
        'member' => '会员',
      ),
      'required' => true,
      'default' => 'member',
      'label' => '运费承担',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'marketable_allow' => 
    array (
      'type' => 'bool',
      'default' => 'true',
      'required' => true,
      'label' => '后台人工上架',
      'width' => 30,
      'editable' => true,
    ),
    'marketable_content' => 
    array (
      'type' => 'varchar(255)',
      'label' => '上下架原因',
      'width' => 110,
      'hidden' => false,
      'editable' => false,
    ),
    'act_type' => 
    array (
      'type' => 'varchar(50)',
      'required' => false,
      'default' => 'normal',
      'label' => '商品类型',
      'width' => 110,
      'editable' => false,
    ),
    'avg_point' => 
    array (
      'type' => 'decimal(8,2)',
      'default' => 0,
      'required' => true,
      'label' => '商品评分',
    ),
    'goods_kind' => 
    array (
      'type' => 
      array (
        'virtual' => '实体商品',
        'entity' => '虚拟商品',
        '3rdparty' => '第三方流程商品',
      ),
      'default' => 'virtual',
      'required' => true,
      'label' => '商品种类',
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
    ),
    'goods_kind_detail' => 
    array (
      'type' => 'varchar(32)',
      'default' => '',
      'label' => '商品种类详细',
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
    ),
    'goods_order_down' => 
    array (
      'type' => 'int unsigned',
      'default' => 100,
      'required' => true,
      'editable' => false,
    ),
  ),
  'comment' => '商品表',
  'index' => 
  array (
    'uni_bn' => 
    array (
      'columns' => 
      array (
        0 => 'bn',
      ),
      'prefix' => 'UNIQUE',
    ),
    'ind_p_1' => 
    array (
      'columns' => 
      array (
        0 => 'p_1',
      ),
    ),
    'ind_p_2' => 
    array (
      'columns' => 
      array (
        0 => 'p_2',
      ),
    ),
    'ind_p_3' => 
    array (
      'columns' => 
      array (
        0 => 'p_3',
      ),
    ),
    'ind_p_4' => 
    array (
      'columns' => 
      array (
        0 => 'p_4',
      ),
    ),
    'ind_p_5' => 
    array (
      'columns' => 
      array (
        0 => 'p_5',
      ),
    ),
    'ind_p_6' => 
    array (
      'columns' => 
      array (
        0 => 'p_6',
      ),
    ),
    'ind_p_7' => 
    array (
      'columns' => 
      array (
        0 => 'p_7',
      ),
    ),
    'ind_p_8' => 
    array (
      'columns' => 
      array (
        0 => 'p_8',
      ),
    ),
    'ind_p_9' => 
    array (
      'columns' => 
      array (
        0 => 'p_9',
      ),
    ),
    'ind_p_10' => 
    array (
      'columns' => 
      array (
        0 => 'p_10',
      ),
    ),
    'ind_p_11' => 
    array (
      'columns' => 
      array (
        0 => 'p_11',
      ),
    ),
    'ind_p_12' => 
    array (
      'columns' => 
      array (
        0 => 'p_12',
      ),
    ),
    'ind_p_13' => 
    array (
      'columns' => 
      array (
        0 => 'p_13',
      ),
    ),
    'ind_p_14' => 
    array (
      'columns' => 
      array (
        0 => 'p_14',
      ),
    ),
    'ind_p_15' => 
    array (
      'columns' => 
      array (
        0 => 'p_15',
      ),
    ),
    'ind_p_16' => 
    array (
      'columns' => 
      array (
        0 => 'p_16',
      ),
    ),
    'ind_p_17' => 
    array (
      'columns' => 
      array (
        0 => 'p_17',
      ),
    ),
    'ind_p_18' => 
    array (
      'columns' => 
      array (
        0 => 'p_18',
      ),
    ),
    'ind_p_19' => 
    array (
      'columns' => 
      array (
        0 => 'p_19',
      ),
    ),
    'ind_p_20' => 
    array (
      'columns' => 
      array (
        0 => 'p_20',
      ),
    ),
    'ind_p_23' => 
    array (
      'columns' => 
      array (
        0 => 'p_23',
      ),
    ),
    'ind_p_22' => 
    array (
      'columns' => 
      array (
        0 => 'p_22',
      ),
    ),
    'ind_p_21' => 
    array (
      'columns' => 
      array (
        0 => 'p_21',
      ),
    ),
    'ind_p_24' => 
    array (
      'columns' => 
      array (
        0 => 'p_24',
      ),
    ),
    'ind_p_25' => 
    array (
      'columns' => 
      array (
        0 => 'p_25',
      ),
    ),
    'ind_p_26' => 
    array (
      'columns' => 
      array (
        0 => 'p_26',
      ),
    ),
    'ind_p_27' => 
    array (
      'columns' => 
      array (
        0 => 'p_27',
      ),
    ),
    'ind_p_28' => 
    array (
      'columns' => 
      array (
        0 => 'p_28',
      ),
    ),
    'ind_p_29' => 
    array (
      'columns' => 
      array (
        0 => 'p_29',
      ),
    ),
    'ind_p_30' => 
    array (
      'columns' => 
      array (
        0 => 'p_30',
      ),
    ),
    'ind_p_31' => 
    array (
      'columns' => 
      array (
        0 => 'p_31',
      ),
    ),
    'ind_p_32' => 
    array (
      'columns' => 
      array (
        0 => 'p_32',
      ),
    ),
    'ind_p_33' => 
    array (
      'columns' => 
      array (
        0 => 'p_33',
      ),
    ),
    'ind_p_34' => 
    array (
      'columns' => 
      array (
        0 => 'p_34',
      ),
    ),
    'ind_p_35' => 
    array (
      'columns' => 
      array (
        0 => 'p_35',
      ),
    ),
    'ind_p_36' => 
    array (
      'columns' => 
      array (
        0 => 'p_36',
      ),
    ),
    'ind_p_37' => 
    array (
      'columns' => 
      array (
        0 => 'p_37',
      ),
    ),
    'ind_p_38' => 
    array (
      'columns' => 
      array (
        0 => 'p_38',
      ),
    ),
    'ind_p_39' => 
    array (
      'columns' => 
      array (
        0 => 'p_39',
      ),
    ),
    'ind_p_40' => 
    array (
      'columns' => 
      array (
        0 => 'p_40',
      ),
    ),
    'ind_p_41' => 
    array (
      'columns' => 
      array (
        0 => 'p_41',
      ),
    ),
    'ind_p_42' => 
    array (
      'columns' => 
      array (
        0 => 'p_42',
      ),
    ),
    'ind_p_43' => 
    array (
      'columns' => 
      array (
        0 => 'p_43',
      ),
    ),
    'ind_p_44' => 
    array (
      'columns' => 
      array (
        0 => 'p_44',
      ),
    ),
    'ind_p_45' => 
    array (
      'columns' => 
      array (
        0 => 'p_45',
      ),
    ),
    'ind_p_46' => 
    array (
      'columns' => 
      array (
        0 => 'p_46',
      ),
    ),
    'ind_p_47' => 
    array (
      'columns' => 
      array (
        0 => 'p_47',
      ),
    ),
    'ind_p_48' => 
    array (
      'columns' => 
      array (
        0 => 'p_48',
      ),
    ),
    'ind_p_49' => 
    array (
      'columns' => 
      array (
        0 => 'p_49',
      ),
    ),
    'ind_p_50' => 
    array (
      'columns' => 
      array (
        0 => 'p_50',
      ),
    ),
    'ind_frontend' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
        1 => 'goods_type',
        2 => 'marketable',
      ),
    ),
    'idx_goods_type' => 
    array (
      'columns' => 
      array (
        0 => 'goods_type',
      ),
    ),
    'idx_d_order' => 
    array (
      'columns' => 
      array (
        0 => 'd_order',
      ),
    ),
    'idx_goods_type_d_order' => 
    array (
      'columns' => 
      array (
        0 => 'goods_type',
        1 => 'd_order',
      ),
    ),
    'idx_marketable' => 
    array (
      'columns' => 
      array (
        0 => 'marketable',
      ),
    ),
    'idx_store_id' => 
    array (
      'columns' => 
      array (
        0 => 'store_id',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 44513 $',
);