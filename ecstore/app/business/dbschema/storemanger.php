<?php 
$db['storemanger']=array ( 
  'columns' =>
  array (
    'store_id' =>
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
    'shop_name' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('店主名'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'account_id' => array (
      'type' => 'table:members@b2c',
      'label' => app::get('business')->_('店主ID'),
      'required' => true,
      'default' => 0,
      'editable' => false,
      'pkey'=>false,
    ),
    'store_idcardname' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('店主实名'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'store_idcard' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('身份证号'),
      'width' => 75,
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),

    'store_name' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('店铺名称'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'is_title'=>true,
      'default_in_list' => true,
    ),

    'store_cat' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('所属分类'),
      'width' => 75,
      'editable' => false,
    ),

    
    'area' => 
    array (
      'label' => app::get('b2c')->_('地区'),
      'width' => 110,
      'type' => 'region',
      //'sdfpath' => 'contact/area',
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => false,
    ),
    'addr' => 
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('地址'),
      //'sdfpath' => 'contact/addr',
      'width' => 110,
      'editable' => true,
      'filtertype' => 'normal',
      'in_list' => true,
      'default_in_list' => true,
      
    ),
    
    'tel' => 
    array (
      'type' => 'varchar(30)',
      'label' => app::get('b2c')->_('手机'),
      'width' => 110,
      //'sdfpath' => 'contact/phone/telephone',
      'searchtype' => 'head',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),    
    'zip' => 
    array (
      'type' => 'varchar(64)',
      'label' => app::get('b2c')->_('邮箱'),
      'width' => 110,
      //'sdfpath' => 'contact/zipcode',
      'editable' => true,
      'filtertype' => 'normal',
      'in_list' => true,
      'default_in_list' => false,
    ),
    

   'store_grade' => 
    array (
      'type' => 'table:storegrade',
      'label' => app::get('b2c')->_('店铺等级'),
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'default_in_list' => true,
    ),

     'store_region' => 
        array (
          'type' => 'varchar(255)',
          'label' => app::get('b2c')->_('经营范围'),
          'width' => 75,
          'editable' => false,
          'in_list' => true,
          'default_in_list' => true,
        ),

    'last_time' =>
    array (
      'type' => 'int',
      'label' => app::get('b2c')->_('有效期'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'earnest' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => false,
      'label' => app::get('b2c')->_('保证金'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
      'in_list' => true,
      'orderby'=>true,

    ),

   'company_name' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('企业名称'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),

    'company_no' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('营业执照号'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),

     'company_taxno' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('税务登记证号'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),

      'company_codename' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('企业组织机构代码'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),


   'company_idname' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('法定代表人名'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),

    'company_idcard' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('法人身份证'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),

    'company_cname' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('公司负责人姓名'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),

     'company_cidcard' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('负责人身份证'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),

     'company_charge' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('负责人职位'),
      'width' => 75,
      //'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),

     'company_ctel' => 
    array (
      'type' => 'varchar(30)',
      'label' => app::get('b2c')->_('企业联系电话'),
      'width' => 110,
      //'sdfpath' => 'contact/phone/telephone',
      'searchtype' => 'head',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),

 
  'company_area' => 
    array (
      'label' => app::get('b2c')->_('公司注册地区'),
      'width' => 110,
      'type' => 'region',
      //'sdfpath' => 'contact/area',
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => false,
    ),
    'company_addr' => 
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('公司注册地址'),
      //'sdfpath' => 'contact/addr',
      'width' => 110,
      'editable' => true,
      'filtertype' => 'normal',
      'in_list' => true,
      'default_in_list' => true,
      
    ), 
    
     'company_carea' => 
    array (
      'label' => app::get('b2c')->_('公司联系地区'),
      'width' => 110,
      'type' => 'region',
      //'sdfpath' => 'contact/area',
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => false,
    ),
    'company_caddr' => 
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('公司联系地址'),
      //'sdfpath' => 'contact/addr',
      'width' => 110,
      'editable' => true,
      'filtertype' => 'normal',
      'in_list' => true,
      'default_in_list' => true,
      
    ), 
    
    'company_earnest' =>
    array (
      'type' => 'varchar(50)',
      'default' => '0',
      'required' => false,
      'label' => app::get('b2c')->_('注册资金（万元）'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
      'in_list' => true,
      'orderby'=>true,

    ),

    'company_time' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('公司成立时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

     'company_timestart' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('营业执照有效期'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

      'company_timeend' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('营业执照有效期'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

      'company_remark' => 
    array (
      'label' => app::get('b2c')->_('营业执照经营范围'),
      'type' => 'text',
      'width' => 75,
      'in_list' => true,
      'default_in_list' => false,
    ),

      'company_url' => 
    array (
      'label' => app::get('b2c')->_('公司官网地址'),
      'type' => 'text',
      'width' => 75,
      'in_list' => true,
      'default_in_list' => false,
    ),

     'shopstatus' => 
    array (
      'type' => 'intbool',
      'label' => app::get('b2c')->_('开业状态'),
      'default' => '1',
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),


    'status' => 
    array (
      'type' => 'intbool',
      'label' => app::get('b2c')->_('开启状态'),
      'default' => '0',
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'approved' =>
    array (
      'type' => array(
            '0'=>'待审核',
            '1'=>'审核通过',
            '2'=>'审核未通过',
      ),
      'default' => '0',
      'label' => app::get('b2c')->_('审核状态'),
      'width' => 150,
      'comment' => app::get('b2c')->_('审核状态'),
      'editable' => true,
      'in_list' => true,
    ),

    'certification' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('认证'),
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),

    'recommend' => 
    array (
      'type' => 'intbool',
      'label' => app::get('b2c')->_('推荐'),
      'default' => '0',
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
      'default_in_list' => false,
    ),

    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),

    'last_modify' =>
    array (
      'type' => 'last_modify',
      'label' => app::get('b2c')->_('更新时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'd_order' =>
    array (
      'type' => 'number',
      'default' => 30,
      'required' => true,
      'label' => app::get('b2c')->_('排序'),
      'width' => 30,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => false,
    ),
    'image' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('图片'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),

    'image_id' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('证件照'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),

    'image_cid' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('营业执照'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),

    'image_codeid' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('组织机构代码'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),

    'image_taxid' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('税务登记证'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'remark' => 
    array (
      'label' => app::get('b2c')->_('备注'),
      'type' => 'text',
      'width' => 75,
      'in_list' => true,
      'default_in_list' => false,
    ),

    'approvedremark' => 
    array (
      'label' => app::get('b2c')->_('审核备注'),
      'type' => 'text',
      'width' => 75,
      'in_list' => true,
      'default_in_list' => false,
    ),

   'approve_time' =>
    array (
      'type' => 'int',
      'label' => app::get('b2c')->_('审核时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),


    'approved_time' =>
    array (
      'type' => 'int',
      'label' => app::get('b2c')->_('审核通过时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'apply_time' =>
    array (
      'type' => 'int',
      'label' => app::get('b2c')->_('申请时间'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'theme_id' =>
      array (
        'type' => 'table:theme',
        'required' => false,
        'label' => '当前模版',
        'width' => 110,
        'hidden' => true,
        'editable' => false,
        'in_list' => false,
    ),
   
    'fav_count' => 
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'buy_m_count' => 
    array (
      'type' => 'int unsigned', 
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'store_space' => 
    array (
      'type' => 'bigint unsigned', 
      'default' => 1073741824,
      'required' => true,
      'editable' => false,
    ),
    'store_usedspace' => 
    array (
      'type' => 'bigint unsigned', 
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'experience' =>
    array (
      'label' => app::get('b2c')->_('经验值'),
      'type' => 'int(10)',
      'default' => 0,
      'editable' => false,
    ),
    'alert_num' => 
    array (
      'type' => 'decimal(20,2)',
      'label' => '预警库存',
      'width' => 30,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
      'in_list' => true,
    ),

    'limit_goods' => 
    array (
      'type' => 'intbool',
      'label' => app::get('b2c')->_('限制发布商品'),
      'default' => '0',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),

    'limit_goodsdown' => 
    array (
      'type' => 'intbool',
      'label' => app::get('b2c')->_('下架所有商品'),
      'default' => '0',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),

     'limit_news' => 
    array (
      'type' => 'intbool',
      'label' => app::get('b2c')->_('商品降权'),
      'default' => '0',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),

     'limit_news_value' => 
    array (
      'type' => 'int unsigned',
      'label' => app::get('b2c')->_('商品降权值'),
      'default' => 100,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),

     'limit_store' => 
    array (
      'type' => 'intbool',
      'label' => app::get('b2c')->_('店铺屏蔽'),
      'default' => '0',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),

      'limit_storedown' => 
    array (
      'type' => 'intbool',
      'label' => app::get('b2c')->_('关闭店铺'),
      'default' => '0',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),

     'limit_sales' => 
    array (
      'type' => 'intbool',
      'label' => app::get('b2c')->_('限制参加营销活动'),
      'default' => '0',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),

     'limit_earnest' => 
    array (
      'type' => 'intbool',
      'label' => app::get('b2c')->_('扣除违约金'),
      'default' => '0',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
    ),

    'store_cert' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('店铺识别码'),
      'width' => 75,
      'in_list' => true,
      'default_in_list' => false,
      'editable' => false,
    ),
	 'bank_name' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('银行名称'),
      'width' => 75,
      //'searchtype' => 'has',
      'editable' => true,
      //'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),
	 'bank_cardid' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('银行卡号'),
      'width' => 75,
     // 'searchtype' => 'has',
      'editable' => false,
      //'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),
  )
);