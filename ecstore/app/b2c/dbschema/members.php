<?php 


$db['members']=array ( 
  'columns' =>
  array (
    'member_id' =>
    array (
      'type' => 'table:account@pam',
      'pkey' => true,
      'sdfpath' => 'pam_account/account_id',
      'label' => app::get('b2c')->_('用户名'),
      'width' => 110,
      'searchtype' => 'has',
      'filtertype' => false,
      'filterdefault' => 'true',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'member_lv_id' =>
    array (
      'required' => true,
      'default' => 0,
      'label' => app::get('b2c')->_('会员等级'),
      'sdfpath' => 'member_lv/member_group_id',
      'width' => 75,
      'type' => 'table:member_lv',
      'editable' => true,
      'filtertype' => 'bool',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'name' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('姓名'),
      'width' => 75,
      'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'is_title'=>true,
      'default_in_list' => false,
    ),
	
    'idcard' =>
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
	'nickname' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('business')->_('昵称'),
      'width' => 75,
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),//end
    'point' =>
    array (
      'type' => 'int(10)',
      'default' => 0,
      'required' => true,
      'sdfpath' => 'score/total',
      'label' => app::get('b2c')->_('积分'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'lastname' =>
    array (
      'sdfpath' => 'contact/lastname',
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'firstname' =>
    array (
      'sdfpath' => 'contact/firstname',
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'area' =>
    array (
      'label' => app::get('b2c')->_('地区'),
      'width' => 110,
      'type' => 'region',
      'sdfpath' => 'contact/area',
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
      'sdfpath' => 'contact/addr',
      'width' => 110,
      'editable' => true,
      'filtertype' => 'normal',
      'in_list' => true,
      'default_in_list' => false,

    ),
    'mobile' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('手机'),
      'width' => 75,
      'sdfpath' => 'contact/phone/mobile',
      'searchtype' => 'head',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => false,
    ),
    'tel' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('固定电话'),
      'width' => 110,
      'sdfpath' => 'contact/phone/telephone',
      'searchtype' => 'head',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => false,
    ),
    'email' =>
    array (
      'type' => 'varchar(200)',
      'label' => 'EMAIL',
      'width' => 110,
      'sdfpath' => 'contact/email',
      'required' => 1,
      'default' => '',  
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => false,
    ),
    'zip' =>
    array (
      'type' => 'varchar(20)',
      'label' => app::get('b2c')->_('邮编'),
      'width' => 110,
      'sdfpath' => 'contact/zipcode',
      'editable' => true,
      'filtertype' => 'normal',
      'in_list' => true,
    ),

    'order_num' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => app::get('b2c')->_('订单数'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'refer_id' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('来源ID'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => false,
    ),
    'refer_url' =>
    array (
      'type' => 'varchar(200)',
      'label' => app::get('b2c')->_('推广来源URL'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => false,
    ),
    'b_year' =>
    array (
        'label' => app::get('b2c')->_('生年'),
      'type' => 'smallint unsigned',
      'width' => 30,
      'editable' => false,
      'in_list'=>false,
    ),
    'b_month' =>
    array (
      'label' => app::get('b2c')->_('生月'),
      'type' => 'tinyint unsigned',
      'width' => 30,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
    ),
    'b_day' =>
    array (
      'label' => app::get('b2c')->_('生日'),
      'type' => 'tinyint unsigned',
      'width' => 30,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
    ),
    'sex' =>
    array (
      'type' =>
      array (
        0 => app::get('b2c')->_('女'),
        1 => app::get('b2c')->_('男'),
        2 => '-',
      ),
      'sdfpath' => 'profile/gender',
      'default' => 2,
      'required' => true,
      'label' => app::get('b2c')->_('性别'),
      'width' => 30,
      'editable' => true,
      'filtertype' => 'yes',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'addon' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'wedlock' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'education' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
    'vocation' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'interest' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'advance' =>
    array (
      'type' => 'money',
      'default' => '0.00',
      'required' => true,
      'label' => app::get('b2c')->_('预存款'),
      'sdfpath' => 'advance/total',
      'width' => 110,
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
    ),
    'advance_freeze' =>
    array (
      'type' => 'money',
      'default' => '0.00',
      'sdfpath' => 'advance/freeze',
      'required' => true,
      'editable' => false,
    ),
    'point_freeze' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'sdfpath' => 'score/freeze',
      'editable' => false,
    ),
    'point_history' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),

    'score_rate' =>
    array (
      'type' => 'decimal(5,3)',
      'editable' => false,
    ),
    'reg_ip' =>
    array (
      'type' => 'varchar(16)',
      'label' => app::get('b2c')->_('注册IP'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'regtime' =>
    array (
      'label' => app::get('b2c')->_('注册时间'),
      'width' => 75,
      'type' => 'time',
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'state' =>
    array (
      'type' => 'tinyint(1)',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('验证状态'),
      'width' => 110,
      'editable' => false,
      'in_list' => false,
    ),
    'pay_time' =>
    array (
      'type' => 'number',
      'editable' => false,
    ),
    'biz_money' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
   
    'pw_answer' =>
    array (
      'label' => app::get('b2c')->_('回答'),
      'type' => 'varchar(250)',
      'sdfpath' => 'account/pw_answer',
      'editable' => false,
    ),
    'pw_question' =>
    array (
      'label' => app::get('b2c')->_('安全问题'),
      'type' => 'varchar(250)',
      'sdfpath' => 'account/pw_question',
      'editable' => false,
    ),

     'seller' => 
    array (
      'type' => 'varchar(8)',
      'label' => app::get('b2c')->_('企业用户'),
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
     ),

    'fav_tags' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'custom' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'cur' =>
    array (
      'sdfpath' => 'currency',
      'type' => 'varchar(20)',
      'label' => app::get('b2c')->_('货币'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'lang' =>
    array (
      'type' => 'varchar(20)',
      'label' => app::get('b2c')->_('语言'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'unreadmsg' =>
    array (
      'type' => 'smallint unsigned',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('未读信息'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'remark' =>
    array (
      'label' => app::get('b2c')->_('备注'),
      'type' => 'text',
      'width' => 75,
      'in_list' => true,
    ),
    'remark_type' =>
    array (
      'type' => 'varchar(2)',
      'default' => 'b1',
      'required' => true,
      'editable' => false,
    ),
    'login_count' =>
    array (
      'type' => 'int(11)',
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
      'in_list' => true,
    ),
    'foreign_id' =>
    array (
      'type' => 'varchar(255)',
    ),
    'member_refer' =>
    array (
      'type' => 'varchar(50)',
      'hidden' => true,
      'default' => 'local',
    ),
	'source' =>
    array (
      'type' => array(
            'pc' =>app::get('b2c')->_('标准平台'),
            'mobile' => app::get('b2c')->_('手机触屏')
       ),
      'required' => false,
      'label' => app::get('mobile')->_('平台来源'),
      'width' => 110,
      'editable' => false,
      'default' =>'pc',
      'in_list' => true,
      'default_in_list' => false,
      'filterdefault' => false,
      'filtertype' => 'normal',
    ),
    'reg_type' =>
    array(
        'type' => array(
            'email' => app::get('b2c')->_('邮箱注册'),
            'mobile' => app::get('b2c')->_('手机注册'),
            'username' => app::get('b2c')->_('用户名注册')
        ),
        'label' => app::get('b2c')->_('注册类型'),
        'requierd' => true,
        'default' => 'username',
        'width' => 110,
        'editable' => false,
        'in_list' => true,
        'default_in_list' => false,
        'filterdefault' => true,
        'filtertype' => 'normal',
    ),
    'verify_email' => array(
        'type' => array(
            'Y' => app::get('b2c')->_('已验证'),
            'N' => app::get('b2c')->_('未验证'),
        ),
        'requierd' => true,
        'default' => 'N',
    ),
    'verify_mobile' => array(
        'type' => array(
            'Y' => app::get('b2c')->_('已验证'),
            'N' => app::get('b2c')->_('未验证'),
        ),
        'requierd' => true,
        'default' => 'N',
    ),
  ),
  'comment' => app::get('b2c')->_('商店会员表'),
  'index' =>
  array (
    'ind_email' =>
    array (
      'columns' =>
      array (
        0 => 'email',
      ),
    ),
    'ind_regtime' =>
    array (
      'columns' =>
      array (
        0 => 'regtime',
      ),
    ),
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 42798 $',
);
