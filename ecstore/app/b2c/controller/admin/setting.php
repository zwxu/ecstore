<?php
 

class b2c_ctl_admin_setting extends desktop_controller{

    var $require_super_op = true;

    public function __construct($app){
        parent::__construct($app);
        $this->ui = new base_component_ui($this);
        $this->app = $app;
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    public function index(){
        $this->basic();
    }

    function basic(){
        $all_settings = array(
            app::get('b2c')->_('商店基本设置')=>array(
                'site.logo',
                'system.shopname',
              //  'store.shop_url',
                // 'system.enable_network',
            ),
            app::get('b2c')->_('店家信息')=>array(
                'store.site_owner',
                'store.contact',
                'store.telephone',
                'store.mobile',
                'store.email',
                'store.qq',
                'store.wangwang',
                'store.address',
                'store.zip_code',
            ),
            app::get('b2c')->_('购物设置')=>array(
                // 'security.guest.enabled',
                //'site.storage.enabled',
                'site.buy.target',
                'system.money.decimals',
                'system.money.operation.carryset',
                // 'site.trigger_tax',
                // 'site.tax_ratio',
                'site.checkout.zipcode.required.open',
                // 'site.checkout.receivermore.open',
               
                'search.goods.tip',
                // 'search.shop.tip',
                'search.position.tip',
             
                // 'site.delivery_time',
                // 'site.rsc_rpc',
                //'system.goods.fastbuy',
                //'site.min_order',
                //'site.min_order_amount',
            ),
            app::get('b2c')->_('积分设置')=>array(
                'site.get_policy.method',
                'site.get_rate.method',
                'site.level_switch',
                'site.point_promotion_method',
                //'site.level_point',
            ),
            app::get('b2c')->_('购物显示设置')=>array(
                'site.login_type',
                'site.register_valide',
                'site.login_valide',
                'gallery.default_view',
                // 'system.category.showgoods',
                // 'site.show_storage',
                'site.show_mark_price',
                'site.market_price',
                'site.market_rate',
                'selllog.display.switch',
                'selllog.display.limit',
                'selllog.display.listnum',
                'site.save_price',
                //'site.promotion.display',
                'cart.show_order_sales.type',
                //'cart.show_order_sales.total_limit',
                'site.member_price_display',
                //'site.retail_member_price_display',
               // 'site.wholesale_member_price_display',
                // 'selllog.display.switch',
                // 'selllog.display.limit',
                // 'selllog.display.listnum',
                'site.show_storage',
                'goodsbn.display.switch',
                'storeplace.display.switch',
                'goodsprop.display.switch',
                'goods.recommend',
                'goodsprop.display.position',
                'gallery.display.listnum',
                    
                'gallery.display.slistnum',
                'gallery.display.shoplistnum',
                'gallery.display.buyCount',
                    
               //'gallery.display.grid.colnum',
                'gallery.deliver.time',
                'gallery.comment.time',
                //'site.associate.search',
                //'site.property.select',
                'site.cat.select',
                'site.imgzoom.show',
                'site.imgzoom.width',
                'site.imgzoom.height',
            ),
             app::get('b2c')->_('其他设置')=>array(
                // 'site.certtext',
                'system.product.alert.num',
                'system.goods.freez.time',
                //'system.admin_verycode',
                // 'system.upload.limit',
                //'system.product.zendlucene',
                //'site.activity.payed_ship_time',
                //'site.activity.no_attendActivity_time',
                //'site.group.payed_time',
                //'site.spike.payed_time',
                //'site.score.payed_time',
                // 'site.order.send_type',
            ),
        );

        // set service for extension settings.
        $obj_extension_services = kernel::servicelist('b2c_extension_settings');
        if ($obj_extension_services)
        {
            foreach ($obj_extension_services as $obj_ext_service)
            {
                $obj_ext_service->settings($all_settings);
            }
        }

        $html= $this->_process($all_settings);
        echo $html;
    }

    function _process($all_settings){
        $setting = new base_setting($this->app);
        $setlib = $setting->source();
        $obj_b2c_shop = $this->app->model('shop');
        $cnt = $obj_b2c_shop->count(array('status'=>'bind','node_type'=>'ecos.ome'));

        // 发票高级配置埋点
        foreach( kernel::servicelist('invoice_setting') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'addHtml') ) {
                    $addHtml = $services->addHtml();
                }
            }
        }
        if (isset($addHtml) && !empty($addHtml)) {
            $setlib = array_merge($setlib, $addHtml);
        }
        $typemap = array(
            SET_T_STR=>'text',
            SET_T_INT=>'number',
            SET_T_ENUM=>'select',
            SET_T_BOOL=>'bool',
            SET_T_TXT=>'text',
            SET_T_FILE=>'file',
            SET_T_IMAGE=>'image',
            SET_T_DIGITS=>'number',
        );
        $tabs = array_keys($all_settings);
        $html = $this->ui->form_start(array('tabs'=>$tabs,'method'=>'POST'));
        $input_style = false;
        $arr_js = array();
        foreach($tabs as $tab=>$tab_name){
            foreach($all_settings[$tab_name] as $set){
                $current_set = $this->app->getConf($set);
                if($set == 'system.shopname'){
                    $current_set = app::get('site')->getConf('site.name');
                }
                if($_POST['set'] && array_key_exists($set,$_POST['set'])){
                    if($current_set!==$_POST['set'][$set]){
                        $current_set = $_POST['set'][$set];
                        $this->app->setConf($set,$_POST['set'][$set]);
                    }
                }

                $input_type = $typemap[$setlib[$set]['type']];

                $form_input = array(
                    'title'=>$setlib[$set]['desc'],
                    'type'=>$input_type,
                    'name'=>"set[".$set."]",
                    'tab'=>$tab,
                    'helpinfo'=>$setlib[$set]['helpinfo'],
                    'value'=>$current_set,
                    'options'=>$setlib[$set]['options'],
                    'vtype' => $setlib[$set]['vtype'],
                    'class' => $setlib[$set]['class'],
                    'id' => $setlib[$set]['id'],
                    'default' => $setlib[$set]['default'],
                );
                if ($input_type=='select')
                    $form_input['required'] = true;
        if($cnt>0){
             if($form_input['name']=="set[system.goods.freez.time]"){
                if($current_set!='1'){
                    $current_set=1;
                }
                if($current_set=='1'){
                    $form_input['disabled'] ="disabled";
                }
             }
        }
                if (isset($setlib[$set]['extends_attr']) && $setlib[$set]['extends_attr'] && is_array($setlib[$set]['extends_attr']))
                {
                    foreach ($setlib[$set]['extends_attr'] as $_key=>$extends_attr)
                    {
                        $form_input[$_key] = $extends_attr;
                    }
                }

                $arr_js[] = $setlib[$set]['javascript'];

                $html.=$this->ui->form_input($form_input);
            }
        }

        if (!$_POST)
        {
            $this->pagedata['_PAGE_CONTENT'] = $html .= $this->ui->form_end() . '<script type="text/javascript">window.addEvent(\'domready\',function(){';

            $str_js = '';
            if (is_array($arr_js) && $arr_js)
            {
                foreach ($arr_js as $str_javascript)
                {
                    $str_js .= $str_javascript;
                }
            }

            $str_js .= '$("main").addEvent("click",function(el){
                el = el.target || el;
                if ($(el).get("id")){
                    var _id = $(el).get("id");
                    var _class_name = "";
                    if (_id.indexOf("-t") > -1){
                        _class_name = _id.substr(0, _id.indexOf("-t"));
                        $$("."+_class_name).getParent("tr").show();
                    }
                    if (_id.indexOf("-f") > -1){
                        _class_name = _id.substr(0, _id.indexOf("-f"));
                        var _destination_node = $$("."+_class_name);
                        _destination_node.getParent("tr").hide();
                        _destination_node.each(function(item){if (item.getNext(".caution") && item.getNext(".caution").hasClass("error")) item.getNext(".caution").remove();});
                    }
                }
            });';

            $this->pagedata['_PAGE_CONTENT'] .= $str_js . '});</script>';
            $this->page();
        }
        else
        {
            $this->begin();
            app::get('site')->setConf('site.name',$_POST['set']['system.shopname']);
            $this->end(true, app::get('b2c')->_('当前配置修改成功！'));
        }
    }

    function licence(){
        $this->sidePanel();
        echo '<iframe width="100%" height="100%" src="'.constant('URL_VIEW_LICENCE').'" ></iframe>';
    }

    function imageset(){
        $ctl = new image_ctl_admin_manage($this->app);
        $ctl->imageset();
    }

}

