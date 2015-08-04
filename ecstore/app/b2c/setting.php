<?php


$setting = array(
'errorpage.p404'=>array('type'=>SET_T_STR,'default'=>app::get('b2c')->_('对不起，无法找到您访问的页面，请返回重新访问。')),
'errorpage.p500'=>array('type'=>SET_T_STR,'default'=>app::get('b2c')->_('对不起，系统无法执行您的请求，请稍后重新访问。')),
'admin.dateFormat'=>array('type'=>SET_T_STR,'default'=>'Y-m-d'),
'admin.timeFormat'=>array('type'=>SET_T_STR,'default'=>'Y-m-d H:i:s'),
'cache.admin_tmpl'=>array('type'=>SET_T_STR,'default'=>DATA_DIR.'/cache/admin_tmpl'),
'cache.apc.enabled'=>array('type'=>SET_T_BOOL,'default'=>false),
'cache.front_tmpl'=>array('type'=>SET_T_STR,'default'=>DATA_DIR.'/cache/front_tmpl'),
'log.level'=>array('type'=>SET_T_INT,'default'=>3),
'log.path'=>array('type'=>SET_T_STR,'default'=>DATA_DIR.'/logs'),

'misc.53kf_account'=>array('type'=>SET_T_STR,'default'=>''),
'misc.53kf_adv'=>array('type'=>SET_T_STR,'default'=>''),
'misc.53kf_interval'=>array('type'=>SET_T_STR,'default'=>''),
'misc.53kf_open'=>array('type'=>SET_T_STR,'default'=>''),
'misc.53kf_style'=>array('type'=>SET_T_STR,'default'=>''),

'misc.forum_code'=>array('type'=>SET_T_STR,'default'=>''),
'misc.forum_key'=>array('type'=>SET_T_STR,'default'=>''),
'misc.forum_login_api'=>array('type'=>SET_T_STR,'default'=>''),
'misc.forum_url'=>array('type'=>SET_T_STR,'default'=>''),

'misc.im_alpha'=>array('type'=>SET_T_STR,'default'=>''),
'misc.im_hide'=>array('type'=>SET_T_STR,'default'=>''),
'misc.im_list'=>array('type'=>SET_T_STR,'default'=>''),
'misc.im_position'=>array('type'=>SET_T_STR,'default'=>''),
'misc.im_show_page'=>array('type'=>SET_T_STR,'default'=>''),

'point.get_policy'=>array('type'=>SET_T_STR,'default'=>''),
'point.get_rate'=>array('type'=>SET_T_STR,'default'=>''),
'point.refund_method'=>array('type'=>SET_T_STR,'default'=>''),
'point.set_commend'=>array('type'=>SET_T_STR,'default'=>''),
'point.set_commend_help'=>array('type'=>SET_T_STR,'default'=>''),
'point.set_commend_help_v'=>array('type'=>SET_T_STR,'default'=>''),
'point.set_commend_v'=>array('type'=>SET_T_STR,'default'=>''),
'point.set_coupon'=>array('type'=>SET_T_STR,'default'=>''),
'point.set_register'=>array('type'=>SET_T_STR,'default'=>''),
'point.set_register_v'=>array('type'=>SET_T_STR,'default'=>''),

'coupon.code.encrypt_len'=>array('type'=>SET_T_INT,'default'=>5),
'coupon.code.count_len'=>array('type'=>SET_T_INT,'default'=>5),
'coupon.mc.use_times'=>array('type'=>SET_T_INT,'default'=>1,'desc'=>app::get('b2c')->_('优惠券可用次数')),

// 'security.guest.enabled'=>array('type'=>SET_T_BOOL,'default'=>'true','desc'=>app::get('b2c')->_('是否支持非会员购物'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('开启后顾客下订单必须先登录').'</span>'),
'webcall.ordernotice.enabled'=>array('type'=>SET_T_BOOL,'default'=>'true','desc'=>app::get('b2c')->_('是否开启订单通知'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('开启后顾客下单成功后会通知客服').'</span>'),
'webcall.service.enabled'=>array('type'=>SET_T_BOOL,'default'=>'true','desc'=>app::get('b2c')->_('是否开启在线客服'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('开启后顾客可联系在线客服').'</span>'),
'shop.showGenerator'=>array('type'=>SET_T_BOOL,'default'=>true),
'site.version'=>array('type'=>SET_T_INT,'default'=>time(),'desc'=>app::get('b2c')->_('version的最后修改时间')),
'site.coupon_order_limit'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('每张订单可用优惠券数量')),//WZP
'site.delivery_time'=>array('type'=>SET_T_STR,'default'=>2,'desc'=>app::get('b2c')->_('默认备货时间')),//WZP
'site.goods_property'=>array('type'=>SET_T_STR,'default'=>''),//WZP
'site.index_hot_num'=>array('type'=>SET_T_STR,'default'=>''),
'site.index_new_num'=>array('type'=>SET_T_STR,'default'=>''),
'site.index_recommend_num'=>array('type'=>SET_T_STR,'default'=>''),
'site.index_special_num'=>array('type'=>SET_T_STR,'default'=>''),
'site.show_mark_price'=>array('type'=>SET_T_BOOL,'default'=>'true','desc'=>app::get('b2c')->_('前台是否显示市场价'),'javascript'=>'$$(\'input[name^=set[site.show_mark_price]\').addEvent(\'click\',function(e){var row=this.getParent(\'tr\');if(this.checked&&this.get(\'value\')==\'true\'){row.getNext(\'tr\').show();if(this.name==\'set[site.show_mark_price]\')row.getNext(\'tr\').getNext(\'tr\').show();}if(this.checked&&this.get(\'value\')==\'false\'){row.getNext(\'tr\').hide();if(this.name==\'set[site.show_mark_price]\')row.getNext(\'tr\').getNext(\'tr\').hide();}});$$(\'input[name^=set[site.show_mark_price]\').each(function(el){el.fireEvent(\'click\');});'),//WZP

'selllog.display.switch'=>array('type'=>SET_T_BOOL,'default'=>'false','desc'=>app::get('b2c')->_('前台是否显示销售记录'),'javascript'=>'$$(\'input[name^=set[selllog.display.switch]\').addEvent(\'click\',function(e){var row=this.getParent(\'tr\');if(this.checked&&this.get(\'value\')==\'true\'){row.getNext(\'tr\').show();if(this.name==\'set[selllog.display.switch]\')row.getNext(\'tr\').getNext(\'tr\').show();}if(this.checked&&this.get(\'value\')==\'false\'){row.getNext(\'tr\').hide();if(this.name==\'set[selllog.display.switch]\')row.getNext(\'tr\').getNext(\'tr\').hide();}});$$(\'input[name^=set[selllog.display.switch]\').each(function(el){el.fireEvent(\'click\');});'),//WZP

'selllog.display.limit'=>array('type'=>SET_T_INT,'default'=>'3','desc'=>app::get('b2c')->_('销售记录低于多少条不显示'),'javascript'=>'$$("input[name^=set[selllog.display.limit]]").addEvent("change",function(el){var _target=$(el.target)||$(el);if (_target.value == "" || _target.value == "0"){_target.value ="1"}});','vtype'=>'digits'),
'selllog.display.listnum'=>array('type'=>SET_T_INT,'default'=>'20','desc'=>app::get('b2c')->_('商品详细页显示销售记录数'),'javascript'=>'$$("input[name^=set[selllog.display.listnum]]").addEvent("change",function(el){var _target=$(el.target)||$(el);if (_target.value == "" || _target.value == "0"){_target.value ="10"}});','vtype'=>'digits'),

'site.login_valide'=>array('type'=>SET_T_BOOL,'default'=>'true','desc'=>app::get('b2c')->_('会员登录需输入验证码')),//WZP
'site.login_type'=>array('type'=>SET_T_ENUM,'default'=>'href','options'=>array('href'=>app::get('b2c')->_('跳转至登录页'),'target'=>app::get('b2c')->_('弹出登陆窗口')),'desc'=>app::get('b2c')->_('顾客登录方式')),
'site.register_valide'=>array('type'=>SET_T_BOOL,'default'=>'true','desc'=>app::get('b2c')->_('会员注册需输入验证码')),
'site.buy.target'=>array('type'=>SET_T_ENUM,'default'=>'3','options'=>array('2'=>app::get('b2c')->_('弹出购物车页面'),'1'=>app::get('b2c')->_('本页跳转到购物车页面'),'3'=>app::get('b2c')->_('不跳转页面，直接加入购物车'),
//'4'=>'询问用户'
),'desc'=>app::get('b2c')->_('顾客点击商品购买按钮后')),//Ever
'site.market_price'=>array('type'=>SET_T_ENUM,'default'=>'1','desc'=>app::get('b2c')->_('如果无市场价,则市场价=销售价'),'options'=>array('1'=>'×','2'=>'+')),
'site.market_rate'=>array('type'=>SET_T_STR,'default'=>'1.2','desc'=>app::get('b2c')->_(''),'javascript'=>'var _site_market_rate = $E("input[name^=\'set[site.market_rate]\']");if (_site_market_rate.value == "") _site_market_rate.value = "1.2";_site_market_rate.addEvent("change",function(e){var _target=$(e.target)||$(e);if (_target.value == "" || _target.value <="1") _target.value="1.2";});','vtype'=>'positive','helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('建议数值大于1，输入数值小于1则值自动设为1.2').'</span>'),
'site.save_price'=>array('type'=>SET_T_ENUM,'default'=>'1','desc'=>app::get('b2c')->_('商品页是否显示节省金额'),'options'=>array('0'=>app::get('b2c')->_('否'),'1'=>app::get('b2c')->_('显示节省的金额'),'2'=>app::get('b2c')->_('显示百分比'),'3'=>app::get('b2c')->_('显示折扣'))),
'site.member_price_display'=>array('type'=>SET_T_ENUM,'default'=>'0','desc'=>app::get('b2c')->_('会员价显示设定'),'options'=>array('1'=>app::get('b2c')->_('显示所有会员等级价格'),'2'=>app::get('b2c')->_('不显示会员价') )),//guzhengxiao
'site.meta_desc'=>array('type'=>SET_T_TXT,'default'=>'','desc'=>app::get('b2c')->_('META_DESCRIPTION<br />(页面描述)')),//WZP
'site.meta_key_words'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('META_KEYWORDS<br />(页面关键词)')),//WZP
'site.order_storage'=>array('type'=>SET_T_ENUM,'default'=>'','options'=>array('0'=>app::get('b2c')->_('订单发货后扣除库存'),'1'=>app::get('b2c')->_('订单生成立即扣库存')),'desc'=>app::get('b2c')->_('库存扣除方式')),//WZP
'site.offline_pay'=>array('type'=>SET_T_BOOL,'default'=>'true','desc'=>app::get('b2c')->_('支持线下支付方式')),//WZP
'site.searchlist_num'=>array('type'=>SET_T_STR,'default'=>''),
'site.b2c_certify'=>array('type'=>SET_T_ENUM,'options'=>array('0'=>app::get('b2c')->_('显示在底部'),'1'=>app::get('b2c')->_('显示在左侧')),'default'=>'','desc'=>app::get('b2c')->_('ShopEx Store 认证显示')),//WZP

// 'site.tax_ratio'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('税率'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('例：税率为5%请填写0.05').'</span>','vtype'=>'required&&unsigned'),//WZP
// 'site.trigger_tax'=>array('type'=>SET_T_BOOL,'default'=>'','desc'=>app::get('b2c')->_('是否设置含税价格'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('设置后顾客下订单可以选择是否需要发票').'</span>','javascript'=>'$$(\'input[name^=set[site.trigger_tax]\').addEvent(\'click\',function(e){var row=this.getParent(\'tr\');if(this.checked&&this.get(\'value\')==\'true\'){row.getNext(\'tr\').show();}if(this.checked&&this.get(\'value\')==\'false\'){row.getNext(\'tr\').hide();}});'),//WZP
'site.min_order'=>array('type'=>SET_T_BOOL,'default'=>'false','desc'=>app::get('b2c')->_('是否开启订单起订量')),//WZP
'site.copyright'=>array('type'=>SET_T_TXT,'default'=>'copyright &copy; 2008','desc'=>app::get('b2c')->_('版权信息')),//WZP
'site.logo'=>array('type'=>SET_T_IMAGE,'default'=>'669f61c74cc8624dc1156939682aacd3','desc'=>app::get('b2c')->_('商店Logo'),'backend'=>'public','extends_attr'=>array('width'=>200,'height'=>95)),
'site.certtext'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('备案号')),
'site.cert'=>array('type'=>SET_T_FILE,'default'=>'cert/bazs.cert|bazs.cert|fs_storage','desc'=>app::get('b2c')->_('备案证书')),
'site.homepage.tmpl_name'=>array('type'=>SET_T_STR,'default'=>'1-column'),
'site.thumbnail_pic_height'=>array('type'=>SET_T_INT,'default'=>80,'desc'=>app::get('b2c')->_('缩略图高度')),
'site.thumbnail_pic_width'=>array('type'=>SET_T_INT,'default'=>110,'desc'=>app::get('b2c')->_('缩略图宽度')),
'site.small_pic_height'=>array('type'=>SET_T_INT,'default'=>300,'desc'=>''),
'site.small_pic_width'=>array('type'=>SET_T_INT,'default'=>300,'desc'=>''),
'site.big_pic_height'=>array('type'=>SET_T_INT,'default'=>600,'desc'=>''),
'site.big_pic_width'=>array('type'=>SET_T_INT,'default'=>600,'desc'=>''),
'site.default_thumbnail_pic'=>array('type'=>SET_T_STR,'default'=>'images/default/default_thumbnail_pic.gif|default/default_thumbnail_pic.gif|fs_storage','desc'=>''),
'site.default_small_pic'=>array('type'=>SET_T_STR,'default'=>'images/default/default_small_pic.gif|default/default_small_pic.gif|fs_storage','desc'=>''),
'site.default_big_pic'=>array('type'=>SET_T_STR,'default'=>'images/default/default_big_pic.gif|default/default_big_pic.gif|fs_storage','desc'=>''),
'site.watermark.wm_small_enable'=>array('type'=>SET_T_INT,'default'=>0,'desc'=>''),
'site.watermark.wm_small_loc'=>array('type'=>SET_T_INT,'default'=>1,'desc'=>''),
'site.watermark.wm_small_text'=>array('type'=>SET_T_STR,'default'=>'','desc'=>''),
'site.watermark.wm_small_font'=>array('type'=>SET_T_STR,'default'=>'','desc'=>''),
'site.watermark.wm_small_font_size'=>array('type'=>SET_T_INT,'default'=>10,'desc'=>''),
'site.watermark.wm_small_font_color'=>array('type'=>SET_T_STR,'default'=>'#000000','desc'=>''),
'site.watermark.wm_small_pic'=>array('type'=>SET_T_STR,'default'=>'images/default/wm_big_pic.gif|default_big_pic.gif|fs_storage','desc'=>''),
'site.watermark.wm_small_transition'=>array('type'=>SET_T_INT,'default'=>100,'desc'=>''),
'site.watermark.wm_big_enable'=>array('type'=>SET_T_INT,'default'=>0,'desc'=>''),
'site.watermark.wm_big_loc'=>array('type'=>SET_T_INT,'default'=>0,'desc'=>''),
'site.watermark.wm_big_font'=>array('type'=>SET_T_STR,'default'=>'','desc'=>''),
'site.watermark.wm_big_text'=>array('type'=>SET_T_STR,'default'=>'','desc'=>''),
'site.watermark.wm_big_font_size'=>array('type'=>SET_T_INT,'default'=>10,'desc'=>''),
'site.watermark.wm_big_font_color'=>array('type'=>SET_T_STR,'default'=>'#000000','desc'=>''),
'site.watermark.wm_big_pic'=>array('type'=>SET_T_STR,'default'=>'images/default/wm_big_pic.gif|default/wm_big_pic.gif|fs_storage','desc'=>''),
'site.watermark.wm_big_transition'=>array('type'=>SET_T_INT,'default'=>100,'desc'=>''),
'site.homepage_title'=>array('type'=>SET_T_STR,'default'=>'{ENV_shopname}','desc'=>app::get('b2c')->_('TITLE(首页标题)'),'display'=>'false'),
'site.homepage_meta_key_words'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('META_KEYWORDS<br />(页面关键词)')),
'site.homepage_meta_desc'=>array('type'=>SET_T_TXT,'default'=>'{ENV_shopname}','desc'=>app::get('b2c')->_('META_DESCRIPTION<br />(页面描述)')),
'site.goods_title'=>array('type'=>SET_T_STR,'default'=>'{ENV_goods_name}_{ENV_shopname}','desc'=>app::get('b2c')->_('TITLE(首页标题)'),'display'=>'false'),
'site.goods_meta_key_words'=>array('type'=>SET_T_STR,'default'=>'{ENV_goods_kw}','desc'=>app::get('b2c')->_('META_KEYWORDS<br />(页面关键词)')),
'site.goods_meta_desc'=>array('type'=>SET_T_TXT,'default'=>app::get('b2c')->_('{ENV_goods_name}现价{ENV_goods_price};{ENV_goods_intro}'),'desc'=>app::get('b2c')->_('META_DESCRIPTION<br />(页面描述)')),
'site.list_title'=>array('type'=>SET_T_STR,'default'=>'{ENV_path}_{ENV_goods_cat_p}_{ENV_shopname}','desc'=>app::get('b2c')->_('TITLE(首页标题)'),'display'=>'false'),
'site.list_meta_key_words'=>array('type'=>SET_T_STR,'default'=>'{ENV_brand}','desc'=>app::get('b2c')->_('META_KEYWORDS<br />(页面关键词)')),
'site.list_meta_desc'=>array('type'=>SET_T_TXT,'default'=>'{ENV_path},{ENV_shopname}'.app::get('b2c')->_('共找到').'{ENV_goods_amount}'.app::get('b2c')->_('个商品').'','desc'=>app::get('b2c')->_('META_DESCRIPTION<br />(页面描述)')),
'site.brand_index_title'=>array('type'=>SET_T_STR,'default'=>app::get('b2c')->_('品牌专区_{ENV_shopname}'),'desc'=>app::get('b2c')->_('TITLE(首页标题)'),'display'=>'false'),
'site.brand_index_meta_key_words'=>array('type'=>SET_T_STR,'default'=>'{ENV_brand}','desc'=>app::get('b2c')->_('META_KEYWORDS<br />(页面关键词)')),
'site.brand_index_meta_desc'=>array('type'=>SET_T_TXT,'default'=>'{ENV_shopname}'.app::get('b2c')->_('提供').'{ENV_brand}'.app::get('b2c')->_('等品牌的商品。').'','desc'=>app::get('b2c')->_('META_DESCRIPTION<br />(页面描述)')),
'site.brand_list_title'=>array('type'=>SET_T_STR,'default'=>'{ENV_brand}_{ENV_shopname}','desc'=>app::get('b2c')->_('TITLE(首页标题)'),'display'=>'false'),
'site.brand_list_meta_key_words'=>array('type'=>SET_T_STR,'default'=>'{ENV_brand_kw}','desc'=>app::get('b2c')->_('META_KEYWORDS<br />(页面关键词)')),
'site.brand_list_meta_desc'=>array('type'=>SET_T_TXT,'default'=>'{ENV_brand_intro}','desc'=>app::get('b2c')->_('META_DESCRIPTION<br />(页面描述)')),
'site.article_list_title'=>array('type'=>SET_T_STR,'default'=>'{ENV_article_cat}_{ENV_shopname}','desc'=>app::get('b2c')->_('TITLE(首页标题)'),'display'=>'false'),
'site.article_list_meta_key_words'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('META_KEYWORDS<br />(页面关键词)')),
'site.article_list_meta_desc'=>array('type'=>SET_T_TXT,'default'=>'{ENV_shopname}{ENV_article_cat}','desc'=>app::get('b2c')->_('META_DESCRIPTION<br />(页面描述)')),
'site.article_title'=>array('type'=>SET_T_STR,'default'=>'{ENV_article_title}_{ENV_shopname}{ENV_article_cat}','desc'=>app::get('b2c')->_('TITLE(首页标题)'),'display'=>'false'),
'site.article_meta_key_words'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('META_KEYWORDS<br />(页面关键词)')),
'site.article_meta_desc'=>array('type'=>SET_T_TXT,'default'=>'{ENV_article_intro}','desc'=>app::get('b2c')->_('META_DESCRIPTION<br />(页面描述)')),
'system.admin_verycode'=>array('type'=>SET_T_BOOL,'default'=>true,'desc'=>app::get('b2c')->_('管理员后台登录启用验证码'),'display'=>'false'),
'store.address'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('联系地址')),
'store.certificate_num'=>array('type'=>SET_T_STR,'default'=>''),
'store.company_name'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('网站所有人')),
'store.contact'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('联系人')),
'store.email'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('电子邮件')),
'store.mobile_phone'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('手机')),
//'store.shop_url'=>array('type'=>SET_T_STR,'default'=>kernel::base_url(1),'desc'=>app::get('b2c')->_('商店网址')),
'store.telephone'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('固定电话')),
'store.zip_code'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('邮政编码')),
'system.contact.email'=>array('type'=>SET_T_STR,'default'=>''),
'system.contact.mobile'=>array('type'=>SET_T_STR,'default'=>''),
'system.contact.name'=>array('type'=>SET_T_STR,'default'=>''),
'system.contact.phone'=>array('type'=>SET_T_STR,'default'=>''),
'system.clientdpi'=>array('type'=>SET_T_STR,'default'=>'96'),
'store.greencard'=>array('type'=>SET_T_BOOL,'default'=>true),

'system.mail_encode'=>array('type'=>SET_T_STR,'default'=>''),
'system.mail_lang'=>array('type'=>SET_T_STR,'default'=>''),
/*'system.money.operation.decimals'=>array('type'=>SET_T_ENUM,'default'=>2,'desc'=>app::get('b2c')->_('前台价格精确到'),'options'=>array(0=>app::get('b2c')->_('无小数位'),1=>app::get('b2c')->_('1位小数'),2=>app::get('b2c')->_('2位小数'),3=>app::get('b2c')->_('3位小数')),'display'=>'false'),*/
'system.money.dec_point'=>array('type'=>SET_T_STR,'default'=>'.'),
'system.money.decimals'=>array('type'=>SET_T_ENUM,'default'=>2,'desc'=>app::get('b2c')->_('订单金额显示位数'),'options'=>array(0=>app::get('b2c')->_('无小数位'),1=>app::get('b2c')->_('1位小数'),2=>app::get('b2c')->_('2位小数'),3=>app::get('b2c')->_('3位小数'))),
'system.money.operation.carryset'=>array('type'=>SET_T_ENUM,'default'=>0,'desc'=>app::get('b2c')->_('价格进位方式'),'options'=>array(0=>app::get('b2c')->_('四舍五入'),1=>app::get('b2c')->_('向上取整'),2=>app::get('b2c')->_('向下取整'))),
'system.money.thousands_sep'=>array('type'=>SET_T_STR,'default'=>''),
'site.currency.defalt_currency'=>array('type'=>SET_T_ENUM, 'default'=>'CNY', 'desc'=> app::get('b2c')->_('站点默认货币'), 'options'=>array_merge(array(''=>app::get('b2c')->_('---请选择货币---')), app::get('ectools')->model('currency')->getSysCur(false))),

'system.path.article'=>array('type'=>SET_T_STR,'default'=>''),
'system.category.showgoods'=>array('type'=>SET_T_ENUM,'default'=>0,'desc'=>app::get('b2c')->_('商品分类列表页显示设置'),'options'=>array('0'=>app::get('b2c')->_('显示该分类及下属子分类下所有商品'),'1'=>app::get('b2c')->_('仅显示本分类下商品'))),//liujy
'system.product.alert.num'=>array('type'=>SET_T_INT,'default'=>0,'desc'=>app::get('b2c')->_('商品库存报警数量')),
'system.product.zendlucene'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('前台商品搜索是否启用zendlucene')),
'system.product.autobn.beginnum'=>array('type'=>SET_T_INT,'default'=>100),
'system.product.autobn.length'=>array('type'=>SET_T_INT,'default'=>6),
'system.product.autobn.prefix'=>array('type'=>SET_T_STR,'default'=>'PDT'),
//'system.goods.fastbuy'=>array('type'=>SET_T_BOOL,'default'=>true,'desc'=>app::get('b2c')->_('快速购买')),
'system.send_mail_method'=>array('type'=>SET_T_STR,'default'=>''),
'system.shoplang'=>array('type'=>SET_T_STR,'default'=>''),
'system.shopname'=>array('type'=>SET_T_STR,'default'=>app::get('b2c')->_('点此设置您商店的名称'),'desc'=>app::get('b2c')->_('商店名称')),
'system.shopurl'=>array('type'=>SET_T_STR,'default'=>''),

'system.admin_error_login_times'=>array('type'=>SET_T_INT,'default'=>0),
'system.admin_error_login_time'=>array('type'=>SET_T_INT,'default'=>0),
'system.use_cart'=>array('type'=>SET_T_BOOL,'default'=>true),
'system.seo.mklink'=>array('type'=>SET_T_STR,'default'=>'actmapper.getlink'),
'system.seo.parselink'=>array('type'=>SET_T_STR,'default'=>'actmapper.parse'),
'system.seo.emuStatic'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('商店页面启用伪静态URL')),
'system.seo.noindex_catalog'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('通知搜索引擎不索引目录页')),
'system.ui.current_theme'=>array('type'=>SET_T_STR),
'system.ui.webslice'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('支持ie8的webslice特性')),
'system.backup.splitFile'=>array('type'=>SET_T_BOOL,'default'=>false),
//'ux.dragcart'=>array('type'=>SET_T_BOOL,'default'=>true,'desc'=>'允许拖动加入购物车'),
'site.title_format'=>array('type'=>SET_T_STR,'default'=>'%1 $system.shopname$','desc'=>app::get('b2c')->_('网站标题格式')),
'site.stripHtml'=>array('type'=>SET_T_BOOL,'default'=>true,'desc'=>app::get('b2c')->_('是否压缩html')),
'site.url.base'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('主站访问地址')),
'site.url.themeres'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('模板资源访问地址')),
'site.url.widgetsres'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('版块资源访问地址')),
'site.promotion.display'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('商品页是否显示订单促销')),
//'site.url.mediaurl'=>array('type'=>SET_T_STR,'default'=>'','desc'=>'商品、文章等图片资源访问地址'),
'goods.rate_nums'=>array('type'=>SET_T_INT,'default'=>'10','desc'=>app::get('b2c')->_('相关商品最大数量')),
'site.level_switch'=>array('type'=>SET_T_ENUM,'default'=>'0','options'=>array('0'=>app::get('b2c')->_('按积分'),'1'=>app::get('b2c')->_('按经验值')),'desc'=>app::get('b2c')->_('会员等级升级方式'),'extends_attr'=>array('except_point'=>'1'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('经验值指的是顾客历史消费金额累积值，1元=1个经验值').'</span>','javascript'=>'$("site-level-switch").addEvent("change",function(e){var _el = e.target || e;if ($(_el).getValue() == "1"){$$(".site-level-switch_cancel").getParent("tr").hide();}else{$$(".site-level-switch_cancel").getParent("tr").show();}});if ($("site-level-switch").getValue() == "1"){$$(".site-level-switch_cancel").getParent("tr").hide();}else{$$(".site-level-switch_cancel").getParent("tr").show();}','id'=>'site-level-switch'),
'site.level_point'=>array('type'=>SET_T_ENUM,'default'=>'0','options'=>array('0'=>app::get('b2c')->_('否'),'1'=>app::get('b2c')->_('是')),'desc'=>app::get('b2c')->_('积分消费是否降会员等级')),

//'gallery.default_view'=>array('type'=>SET_T_ENUM,'default'=>'index','options'=>array('index'=>app::get('b2c')->_('图文混排'),'grid'=>app::get('b2c')->_('橱窗形式'),'text'=>app::get('b2c')->_('文字列表')),'desc'=>app::get('b2c')->_('商品列表默认展示方式')),
'gallery.default_view'=>array('type'=>SET_T_ENUM,'default'=>'grid','options'=>array('index'=>app::get('b2c')->_('店铺形式'),'grid'=>app::get('b2c')->_('大图形式'),'text'=>app::get('b2c')->_('小图形式')),'desc'=>app::get('b2c')->_('商品列表默认展示方式')),

'system.fast_delivery_as_progress'=>array('type'=>SET_T_BOOL,'default'=>true,'desc'=>app::get('b2c')->_('后台手工发货为"已发货"')),
'system.auto_delivery'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('用户到款则自动发货')),
'system.auto_delivery_physical'=>array('type'=>SET_T_STR,'default'=>'no','desc'=>app::get('b2c')->_('用户到款自动发货时，实体商品如何处理(auto:发货为ready,no:不发货,yes:发货为progress)')),
'system.auto_use_advance'=>array('type'=>SET_T_BOOL,'default'=>true,'desc'=>app::get('b2c')->_('自动使用预存款')),

'search.show.range'=>array('type'=>SET_T_BOOL,'default'=>1,'desc'=>app::get('b2c')->_('搜索是否显示价格区间')),
'errorpage.searchempty'=>array('type'=>SET_T_STR,'default'=>'<h1 class="error" style="">'.app::get('b2c')->_('非常抱歉，没有找到相关商品').'</h1>
        <p style="margin:15px 1em;"><strong>'.app::get('b2c')->_('建议：').'</strong><br />'.app::get('b2c')->_('适当缩短您的关键词或更改关键词后重新搜索，如：将 “索尼手机X1” 改为 “索尼+X1”').'</p>'),

'order.flow.payed'=>array('type'=>SET_T_BOOL,'default'=>true,'desc'=>app::get('b2c')->_('订单付款流程')),
'order.flow.consign'=>array('type'=>SET_T_BOOL,'default'=>true,'desc'=>app::get('b2c')->_('订单发货流程')),
'order.flow.refund'=>array('type'=>SET_T_BOOL,'default'=>true,'desc'=>app::get('b2c')->_('订单退款流程')),
'order.flow.reship'=>array('type'=>SET_T_BOOL,'default'=>true,'desc'=>app::get('b2c')->_('订单退货流程')),

'certificate.id'=>array('type'=>SET_T_TXT,'default'=>'','desc'=>app::get('b2c')->_('ShopEx证书编号')),
'certificate.token'=>array('type'=>SET_T_TXT,'default'=>'','desc'=>app::get('b2c')->_('ShopEx证书密钥')),
'certificate.str'=>array('type'=>SET_T_TXT,'default'=>'','desc'=>app::get('b2c')->_('ShopEx证书身份说明')),
'certificate.formal'=>array('type'=>SET_T_TXT,'default'=>'','desc'=>app::get('b2c')->_('ShopEx证书身份')),

'certificate.kft.cid'=>array('type'=>SET_T_STR,'default'=>'false','desc'=>app::get('b2c')->_('客服通公司id')),
'certificate.kft.style'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('客服通风格号')),
'certificate.kft.action'=>array('type'=>SET_T_STR,'default'=>'TOAPPLY','desc'=>app::get('b2c')->_('客服通动作')),
'certificate.kft.enable'=>array('type'=>SET_T_STR,'default'=>'TOAPPLY','desc'=>app::get('b2c')->_('客服通开关')),



'certificate.channel.url'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('渠道url')),
'certificate.channel.name'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('渠道商名')),
'certificate.channel.status'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('渠道状态')),
'certificate.channel.service'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('渠道服务类型')),

'certificate.distribute'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('是否开通分销模块')),

'messenger.sms.config'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('短信sms签名')),

'b2c.wss.username'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('合作统计用户名')),
'b2c.wss.password'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('合作统计密码')),
'b2c.wss.enable'=>array('type'=>SET_T_INT,'default'=>'0','desc'=>app::get('b2c')->_('合作统计开关')),
'b2c.wss.show'=>array('type'=>SET_T_INT,'default'=>'0','desc'=>app::get('b2c')->_('合作统计前台开关')),
'b2c.wss.js'=>array('type'=>SET_T_TXT,'default'=>'','desc'=>app::get('b2c')->_('合作统计js')),

'comment.index.listnum'=>array('type'=>SET_T_STR,'default'=>4,'desc'=>app::get('b2c')->_('商品首页显示评论条数')),
'comment.list.listnum'=>array('type'=>SET_T_STR,'default'=>10,'desc'=>app::get('b2c')->_('评论列表页显示评论条数')),
'comment.switch.ask'=>array('type'=>SET_T_STR,'default'=>'on','desc'=>app::get('b2c')->_('商品询问开关')),
'comment.switch.discuss'=>array('type'=>SET_T_STR,'default'=>'on','desc'=>app::get('b2c')->_('商品评论开关')),
'comment.switch.buy'=>array('type'=>SET_T_STR,'default'=>'off','desc'=>app::get('b2c')->_('商品经验评论开关')),
'comment.display.ask'=>array('type'=>SET_T_STR,'default'=>'reply','desc'=>app::get('b2c')->_('商品评论(询问),回复显示')),
'comment.display.discuss'=>array('type'=>SET_T_STR,'default'=>'reply','desc'=>app::get('b2c')->_('商品评论(评论),回复显示')),
'comment.display.buy'=>array('type'=>SET_T_STR,'default'=>'reply','desc'=>app::get('b2c')->_('商品评论(经验),回复显示')),
'comment.power.ask'=>array('type'=>SET_T_STR,'default'=>'null','desc'=>app::get('b2c')->_('商品评论(询问),发布权限')),
'comment.power.discuss'=>array('type'=>SET_T_STR,'default'=>'member','desc'=>app::get('b2c')->_('商品评论(评论),发布权限')),
'comment.power.buy'=>array('type'=>SET_T_STR,'default'=>'buyer','desc'=>app::get('b2c')->_('商品评论(经验),发布权限')),
'comment.null_notice.ask'=>array('type'=>SET_T_STR,'default'=>app::get('b2c')->_('如果您对本商品有什么问题,请提问咨询!'),'desc'=>app::get('b2c')->_('没有咨询记录,提示文字')),
'comment.null_notice.discuss'=>array('type'=>SET_T_STR,'default'=>app::get('b2c')->_('如果您对本商品有什么评价或经验,欢迎分享!'),'desc'=>app::get('b2c')->_('商品评论(经验),发布权限')),
'comment.null_notice.buy'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('商品评论(经验),发布权限')),
'comment.submit_display_notice.ask'=>array('type'=>SET_T_STR,'default'=>app::get('b2c')->_('您的问题已经提交成功!'),'desc'=>app::get('b2c')->_('没有咨询记录,提示文字')),
'comment.submit_hidden_notice.ask'=>array('type'=>SET_T_STR,'default'=>app::get('b2c')->_('您的问题已经提交成功,管理员会尽快回复!'),'desc'=>app::get('b2c')->_('商品评论(经验),发布权限')),
'comment.submit_display_notice.discuss'=>array('type'=>SET_T_STR,'default'=>app::get('b2c')->_('感谢您的分享!'),'desc'=>app::get('b2c')->_('商品评论(经验),发布权限')),
'comment.submit_hidden_notice.discuss'=>array('type'=>SET_T_STR,'default'=>app::get('b2c')->_('感谢您的分享,管理员审核后会自动显示!'),'desc'=>app::get('b2c')->_('没有咨询记录,提示文字')),
'comment.submit_display_notice.buy'=>array('type'=>SET_T_STR,'default'=>app::get('b2c')->_('如果您对本商品有什么问题,请提问咨询!'),'desc'=>app::get('b2c')->_('商品评论(经验),发布权限')),
'comment.submit_hidden_notice.buy'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('商品评论(经验),发布权限')),

'goodsbn.display.switch'=>array('type'=>SET_T_BOOL,'default'=>true,'desc'=>app::get('b2c')->_('是否启用商品编号')),
'goodsprop.display.position'=>array('type'=>SET_T_ENUM,'default'=>'1','options'=>array('1'=>app::get('b2c')->_('仅商品价格上方'),'2'=>app::get('b2c')->_('仅商品详情中'),'0'=>app::get('b2c')->_('两处同时显示')),'desc'=>app::get('b2c')->_('属性显示位置')),
'storeplace.display.switch'=>array('type'=>SET_T_BOOL,'default'=>true,'desc'=>app::get('b2c')->_('是否使用商品货位')),
'system.location'=>array('type'=>SET_T_STR,'default'=>'mainland'),

'gallery.display.listnum'=>array('type'=>SET_T_INT,'default'=>'20','desc'=>app::get('b2c')->_('搜索列表显示条数'),'javascript'=>'$$("input[name^=set[gallery.display.listnum]]").addEvent("change",function(e){var _target = $(e.target)||$(e);if (_target.value == "" || _target.value == "0")_target.value = "20";});','vtype'=>'digits'),
'gallery.display.grid.colnum'=>array('type'=>SET_T_INT,'default'=>'4','desc'=>app::get('b2c')->_('搜索橱窗页每行显示数')),
'gallery.deliver.time'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('搜索列表是否启用发布时间')),
'gallery.comment.time'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('搜索列表是否启用评论次数')),
'site.associate.search'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('前台是否启用联想搜索')),

'site.cat.select'=>array('type'=>SET_T_BOOL,'default'=>true,'desc'=>app::get('b2c')->_('列表页是否启用分类筛选')),
'plugin.passport.config.current_use'=>array('type'=>SET_T_STR,'default'=>'', 'desc'=>app::get('b2c')->_('当前使用的passport')),
//'store.gtype.status'=>array('type'=>SET_T_ENUM,'default'=>0,'desc'=>'商店是否有自定义商品类型','options'=>array('0'=>app::get('b2c')->_('否'),'1'=>'是')),//liujy

'system.message.open'=>array('type'=>SET_T_ENUM,'default'=>'off','desc'=>app::get('b2c')->_('商店留言发布'),'options'=>array('on'=>app::get('b2c')->_('会员提交后立即发布'),'off'=>app::get('b2c')->_('管理员回复后发布'))),

'service.wltx'=>array('type'=>SET_T_STR,'default'=>''),
'system.default_storager'=>array('type'=>SET_T_STR,'default'=>'filesystem'),
'site.show_storage'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('前台是否显示库存数量')),
'site.refer_timeout'=>array('type'=>SET_T_INT,'default'=>15,'desc'=>app::get('b2c')->_('推荐链接过期时间（天）')),
'site.is_open_return_product'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('是否开启退货功能')),
'site.api.maintenance.is_maintenance'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>'','display'=>'false'),
'site.api.maintenance.notify_msg'=>array('type'=>SET_T_STR,'default'=>'','desc'=>'','display'=>'false'),

'system.upload.limit'=>array('type'=>SET_T_ENUM,'default'=>0,'desc'=>app::get('b2c')->_('前台图片大小限定'),'options'=>array(0=>'500k',1=>'1M',2=>'2M',3=>'3M',4=>'5M',5=>app::get('b2c')->_('无限制')),'display'=>'false'),
'system.goods.freez.time'=>array('type'=>SET_T_ENUM,'default'=>1,'desc'=>app::get('b2c')->_('库存预占触发时间'),'options'=>array('1'=>app::get('b2c')->_('下订单'),'2'=>app::get('b2c')->_('订单付款')),'display'=>'false'),
'system.guide' => array('type'=>SET_T_STR,'default'=>'', 'desc'=>app::get('b2c')->_('向导设置')),
'goodsprop.display.switch'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('是否启用商品属性链接')),
'goods.recommend'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('是否开启商品推荐(商品详细页)')),
'store.site_owner'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('商店所有人')),
'store.contact'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('联系人')),
'store.mobile'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('手机')),
'store.qq'=>array('type'=>SET_T_STR,'default'=>'','desc'=>'qq'),
'store.wangwang'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('旺旺')),
'system.enable_network'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('启用网络连接')),
'site.rsc_rpc'=>array('type'=>SET_T_BOOL,'default'=>'true','desc'=>app::get('b2c')->_('优化商店运营数据')),

'site.get_policy.method'=>array('type'=>SET_T_ENUM,'default'=>'1','options'=>array('1'=>app::get('b2c')->_('不使用积分'),'2'=>app::get('b2c')->_('按订单商品总价格计算积分 '),'3'=>app::get('b2c')->_('为商品单独设置积分  ')),'desc'=>app::get('b2c')->_('积分计算方式：'),'id'=>'site-get_policy-method','javascript'=>'$("site-get_policy-method").addEvent("change",function(e){var _el = e.target || e;if ($(_el).getValue() == "1"){$$(".site-get_policy-method_cancel").getParent("tr").hide();}else {$$(".site-get_policy-method_cancel").getParent("tr").show();if ($(_el).getValue() == "3") $$(".site-get_policy-method-goods-point_cancel").getParent("tr").hide(); else $$(".site-get_policy-method-goods-point_cancel").getParent("tr").show();}if ($("site-point-expired-t") && $("site-point-expired-t").checked == true){$$(".site-point-expired").getParent("tr").show();}else{$$(".site-point-expired").getParent("tr").hide();}});if ($("site-get_policy-method").getValue() == "1"){$$(".site-get_policy-method_cancel").getParent("tr").hide();}else{$$(".site-get_policy-method_cancel").getParent("tr").show();if ($("site-get_policy-method").getValue() == "3") $$(".site-get_policy-method-goods-point_cancel").getParent("tr").hide(); else $$(".site-get_policy-method-goods-point_cancel").getParent("tr").show();}'),
'site.get_rate.method'=>array('type'=>SET_T_STR,'default'=>'','vtype'=>'positive','desc'=>app::get('b2c')->_('积分换算比率：'),'class'=>'site-get_policy-method_cancel site-get_policy-method-goods-point_cancel'),
'site.min_order_amount'=>array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('订单起订金额')),

'system.event_listener' => array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('监控事件监听')),
'system.event_listener_key' => array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('监控键值')),

'is_delivery_discount_close' => array('type'=>SET_T_BOOL,'default'=>true,'desc'=>app::get('b2c')->_('是否打开配送邮寄折扣')),

'site.get_policy.stage' => array('type'=>SET_T_ENUM,'default'=>'1','desc'=>app::get('b2c')->_('何时结算积分'),'options'=>array(/*'1'=>app::get('b2c')->_('支付完毕时'),'2'=>app::get('b2c')->_('支付发货后'),*/'3'=>app::get('b2c')->_('订单完成时')),'display'=>'false','class'=>'site-get_policy-method_cancel'),

'site.consume_point.stage' => array('type'=>SET_T_ENUM,'default'=>'1','desc'=>app::get('b2c')->_('何时扣除使用积分'),'options'=>array('1'=>app::get('b2c')->_('支付完成时'),'2'=>app::get('b2c')->_('支付并发货后'),'3'=>app::get('b2c')->_('订单完成时')),'display'=>'false','class'=>'site-get_policy-method_cancel'),

'site.point_promotion_method' => array('type'=>SET_T_ENUM,'default'=>'1','desc'=>app::get('b2c')->_('积分升级方式'),'options'=>array('1'=>app::get('b2c')->_('只升不降'),'2'=>app::get('b2c')->_('根据积分余额升级或降级')),'display'=>'false','class'=>'site-get_policy-method_cancel site-level-switch_cancel'),

'site.point_expired' => array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('积分过期设置'),'id'=>'site-point-expired','class'=>'site-get_policy-method_cancel','javascript'=>'if ($("site-point-expired-t") && $("site-point-expired-t").checked == true){$$(".site-point-expired").getParent("tr").show();}else{$$(".site-point-expired").getParent("tr").hide();}'),

'site.point_expried_method' => array('type'=>SET_T_ENUM,'default'=>'1','desc'=>app::get('b2c')->_('积分过期方式'),'options'=>array('1'=>app::get('b2c')->_('设置过期结束时间'),'2'=>app::get('b2c')->_('设置过期时间长度')),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('若选择“设置过期结束时间”请按时间格式输入结束时间').'</span>', 'class'=>'site-point-expired site-get_policy-method_cancel','id'=>'site-point-expried-method','javascript'=>'if ($("site-point-expried-method").getValue() =="1"){$$(".site-point-expried-method").set("vtype","date");}else{$$(".site-point-expried-method").set("vtype","number");}$("site-point-expried-method").addEvent("change",function(e){var _el = e.target || e;if ($(_el).getValue() == "1"){$$(".site-point-expried-method").set("vtype","date");}else{$$(".site-point-expried-method").set("vtype","number");}});'),

'site.point_expired_value' => array('type'=>SET_T_STR,'default'=>'0','desc'=>app::get('b2c')->_('设置积分过期的值'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('过期结束时间格式为Y-M-d/过期间隔时间的最小单位为天').'</span>', 'class'=>'site-point-expired site-get_policy-method_cancel site-point-expried-method'),

'site.point_max_deductible_method' => array('type'=>SET_T_ENUM,'default'=>'1','desc'=>app::get('b2c')->_('下订单抵扣金额'),'options'=>array('1'=>app::get('b2c')->_('每一笔订单最大的抵扣金额。'),'2'=>app::get('b2c')->_('每一笔订单最大的抵扣比例。')),'class'=>'site-get_policy-method_cancel'),

'site.point_max_deductible_value' => array('type'=>SET_T_STR,'default'=>'','desc'=>app::get('b2c')->_('积分抵扣最大金额或比例'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('最大折扣比例1=100%')."</span>",'vtype'=>'required&&positive','class'=>'site-get_policy-method_cancel'),

'site.point_deductible_value' =>array('type'=>SET_T_STR,'default'=>'0.01','desc'=>app::get('b2c')->_('积分抵扣金额的比例值'),'vtype'=>'required&&number','helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('折扣比例0.01=1%')."</span>",'class'=>'site-get_policy-method_cancel'),

'site.point_money_value' => array('type'=>SET_T_STR,'default'=>'1','desc'=>app::get('b2c')->_('获取积分时积分与金额比例'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('例：100(消费1元获得100积分)')."</span>",'vtype'=>'required&&number','class'=>'site-get_policy-method_cancel','javascript'=>'var tag=$$("input[name^=set[site.point_money_value]]");if(tag.get("value") == ""){tag.set("value",1)};tag.addEvent("change",function(e){var _target = $(e.target)||$(e);if (_target.get("value") == "")_target.get("value") = "1";});','vtype'=>'number'),

'site.point_max_get_value' => array('type'=>SET_T_STR,'default'=>'0.5','desc'=>app::get('b2c')->_('积分获取最高比例'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('比例0.5=50%')."</span>",'vtype'=>'required&&number','class'=>'site-get_policy-method_cancel','javascript'=>'var tag=$$("input[name^=set[site.point_max_get_value]]");if(tag.get("value") == ""){tag.set("value",0.5)};tag.addEvent("change",function(e){var _target = $(e.target)||$(e);if (_target.get("value") == "")_target.get("value") = "0.5";});','vtype'=>'number'),

'site.point_mim_get_value' => array('type'=>SET_T_STR,'default'=>'0.005','desc'=>app::get('b2c')->_('积分获取最低比例'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('比例0.005=0.5%')."</span>",'vtype'=>'required&&number','class'=>'site-get_policy-method_cancel','javascript'=>'var tag=$$("input[name^=set[site.point_mim_get_value]]");if(tag.get("value") == ""){tag.set("value",0.005)};tag.addEvent("change",function(e){var _target = $(e.target)||$(e);if (_target.get("value") == "")_target.get("value") = "0.005";});','vtype'=>'number'),

'site.get_point_interval_time' => array('type'=>SET_T_STR,'default'=>'0','desc'=>app::get('b2c')->_('获得积分间隔时间'),'javascript'=>'$E("input[name^=set[site.get_point_interval_time]]").addEvent("change",function(el){var _target=$(el.target)||$(el);if (_target.value == ""){_target.value="0";}});','helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('时间间隔单位为天')."</span>",'vtype'=>'required&&unsigned','class'=>'site-get_policy-method_cancel'),

'site.point_usage' => array('type'=>SET_T_ENUM, 'default'=>'1', 'desc'=>app::get('b2c')->_('积分用途'), 'options'=>array('1'=>app::get('b2c')->_('只用于兑换'), '2'=>app::get('b2c')->_('只用于抵扣')),'class'=>'site-get_policy-method_cancel'),


//购物车显示促销增加setting设置
'cart.show_order_sales.type'=>array('type'=>SET_T_BOOL,'default'=>'true','desc'=>app::get('b2c')->_('购物车是否显示订单促销信息')),

'cart.show_order_sales.total_limit' => array('type'=>SET_T_STR,'default'=>'20','desc'=>app::get('b2c')->_('购物车未执行促销总金额区间百分比：'),'javascript'=>'if($E(\'input[name=set\[cart.show_order_sales.type\]]\').get(\'value\')!=\'false\')$E(\'input[name=set\[cart.show_order_sales.total_limit\]]\').getParent(\'tr\').setStyle(\'display\',\'none\');'),
'site.imgzoom.show'=>array('type'=>SET_T_BOOL,'default'=>'false','desc'=>app::get('b2c')->_('是否启用放大镜功能'),'javascript'=>'$$(\'input[name^=set[site.imgzoom.show]\').addEvent(\'click\',function(e){var row=this.getParent(\'tr\');if(this.checked&&this.get(\'value\')==\'true\'){row.getNext(\'tr\').show();if(this.name==\'set[site.imgzoom.show]\')row.getNext(\'tr\').getNext(\'tr\').show();}if(this.checked&&this.get(\'value\')==\'false\'){row.getNext(\'tr\').hide();if(this.name==\'set[site.imgzoom.show]\')row.getNext(\'tr\').getNext(\'tr\').hide();}});$$(\'input[name^=set[site.imgzoom.show]\').each(function(el){el.fireEvent(\'click\');});'),//WZP
'site.imgzoom.width'=>array('type'=>SET_T_STR,'default'=>'400','desc'=>app::get('b2c')->_('宽'),'javascript'=>'$$("input[name^=set[site.imgzoom.width]]").addEvent("change",function(e){var _target=$(e.target)||$(e);if (_target.value == "" || _target.value == "0") _target.value = "400";});','vtype'=>'number'),//WZP
'site.imgzoom.height'=>array('type'=>SET_T_STR,'default'=>'300','desc'=>app::get('b2c')->_('高'),'javascript'=>'$$("input[name^=set[site.imgzoom.height]]").addEvent("change",function(e){var _target=$(e.target)||$(e);if (_target.value == "" || _target.value == "0") _target.value = "300";});','vtype'=>'number'),//WZP
'site.checkout.zipcode.required.open'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('邮编是否为必填项'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('设置后顾客下订单必须填写邮编').'</span>'),
// 'site.checkout.receivermore.open'=>array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('b2c')->_('是否开启配送时间'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('开启后顾客下订单可以选择配送时间').'</span>'),

'system.default_dc'=>array('type'=>SET_T_INT,'default'=>'1','desc'=>__('Ĭid')),//WZP

// 'site.order.send_type'=>array('type'=>SET_T_BOOL,'default'=>'false','desc'=>app::get('b2c')->_('是否要实时同步订单'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('开启后顾客下订单可以选择配送时间').'</span>'),

//活动设置
//'site.activity.payed_ship_time'=>array('type'=>SET_T_INT,'default'=>'3','desc'=>app::get('b2c')->_('活动订单付款后发货天数'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('活动商品卖家须在买家付款后的几天内完成发货（单位：天，默认：3天）').'</span>','javascript'=>'var tag=$$("input[name^=set[site.activity.payed_ship_time]]");if(tag.get("value") == ""){tag.set("value",3)};tag.addEvent("change",function(e){var _target = $(e.target)||$(e);if (_target.get("value") == "")_target.get("value") = "3";});','vtype'=>'number'),

//'site.activity.no_attendActivity_time'=>array('type'=>SET_T_INT,'default'=>'3','desc'=>app::get('b2c')->_('取消店铺该项活动资格天数'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('卖家出现不发货等问题，将取消店铺该项活动资格天数（单位：天，默认：60天）').'</span>','javascript'=>'var tag=$$("input[name^=set[site.activity.no_attendActivity_time]]");if(tag.get("value") == ""){tag.set("value",60)};tag.addEvent("change",function(e){var _target = $(e.target)||$(e);if (_target.get("value") == "")_target.get("value") = "60";});','vtype'=>'number'),

//团购设置
//'site.group.payed_time'=>array('type'=>SET_T_INT,'default'=>'3','desc'=>app::get('b2c')->_('团购订单必须下单后付款时间'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('买家拍下团购商品后3小时内必须付款否则自动关闭订单（单位：小时，默认：3小时）').'</span>','javascript'=>'var tag=$$("input[name^=set[site.group.payed_time]]");if(tag.get("value") == ""){tag.set("value",3)};tag.addEvent("change",function(e){var _target = $(e.target)||$(e);if (_target.get("value") == "")_target.get("value") = "3";});','vtype'=>'number'),

'system.order.tracking'=>array('type'=>SET_T_BOOL,'default'=>'false','desc'=>app::get('b2c')->_('是否启用订单跟踪查询'),'helpinfo'=>'<span class=\'notice\' style=\'display:inline-block;vertical-align:top;margin-left:10px;\'>'.app::get('b2c')->_('说明：目前订单物流跟踪查询功能是采用快递100提供的免费API接口，该接口目前使用限制为2000次/天，<br>若商家需要无限制或使用其它快递查询接口服务，请另行咨询快递100或其它服务商获取。<br>参考：http://www.kuaidi100.com/help/qa.shtml#qa06').'</span>'),

//秒杀设置
//'site.spike.payed_time'=>array('type'=>SET_T_INT,'default'=>'15','desc'=>app::get('b2c')->_('秒杀订单必须下单后付款时间'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('买家拍下秒杀商品后15分钟内必须付款否则自动关闭订单（单位：分钟，默认：15分钟）').'</span>','javascript'=>'var tag=$$("input[name^=set[site.spike.payed_time]]");if(tag.get("value") == ""){tag.set("value",15)};tag.addEvent("change",function(e){var _target = $(e.target)||$(e);if (_target.get("value") == "")_target.get("value") = "15";});','vtype'=>'number'),

//积分换购设置
//'site.score.payed_time'=>array('type'=>SET_T_INT,'default'=>'3','desc'=>app::get('b2c')->_('积分换购订单必须下单后付款时间'),'helpinfo'=>'<span class=\'notice-inline\'>'.app::get('b2c')->_('买家拍下积分换购商品后3小时内必须付款否则自动关闭订单（单位：小时，默认：3小时）').'</span>','javascript'=>'var tag=$$("input[name^=set[site.score.payed_time]]");if(tag.get("value") == ""){tag.set("value",3)};tag.addEvent("change",function(e){var _target = $(e.target)||$(e);if (_target.get("value") == "")_target.get("value") = "3";});','vtype'=>'number'),

//api版本设置
'api.local.version'=>array('type'=>SET_T_INT,'default'=>'2.0'),

'search.goods.tip'=>array('type'=>SET_T_TXT,'default'=>'搜商品','desc'=>app::get('b2c')->_('搜索商品时提示'),'helpinfo'=>'<span class=\'notice-inline\'>前台搜索商品时，输入框提示信息。</span>'),
// 'search.shop.tip'=>array('type'=>SET_T_TXT,'default'=>'搜店铺','desc'=>app::get('b2c')->_('搜索商店铺时提示'),'helpinfo'=>'<span class=\'notice-inline\'>前台搜索店铺时，输入框提示信息。</span>'),
'search.position.tip'=>array('type'=>SET_T_TXT,'default'=>'在当前位置搜索','desc'=>app::get('b2c')->_('在当前位置搜索时提示'),'helpinfo'=>'<span class=\'notice-inline\'>前台在当前位置搜索时，输入框提示信息。</span>'),
'gallery.display.slistnum'=>array('type'=>SET_T_INT,'default'=>'80','desc'=>app::get('b2c')->_('搜索小图列表显示条数'),'javascript'=>'$$("input[name^=set[gallery.display.slistnum]]").addEvent("change",function(e){var _target = $(e.target)||$(e);if (_target.value == "" || _target.value == "0")_target.value = "80";});','vtype'=>'digits'),
'gallery.display.shoplistnum'=>array('type'=>SET_T_INT,'default'=>'20','desc'=>app::get('b2c')->_('搜索店铺列表显示条数'),'javascript'=>'$$("input[name^=set[gallery.display.shoplistnum]]").addEvent("change",function(e){var _target = $(e.target)||$(e);if (_target.value == "" || _target.value == "0")_target.value = "20";});','vtype'=>'digits'),
'gallery.display.buyCount'=>array('type'=>SET_T_BOOL,'default'=>'true','desc'=>app::get('b2c')->_('列表页是否显示销售量')),



//end 
);
