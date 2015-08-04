<?php



class express_ctl_admin_delivery_printer extends desktop_controller{
    public $workground = 'ectools_ctl_admin_order';

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
        define('DPGB_TMP_MODE',1);
        define('DPGB_HOME_MODE',2);
        $this->pagedata['dpi'] = intval(app::get('b2c')->getConf('system.clientdpi'));
        if(!$this->pagedata['dpi']){
            $this->pagedata['dpi'] = 96;
        }
        $this->model = $this->app->model('print_tmpl');
        $this->o = &app::get('image')->model('image_attach');
        $this->obj = $this;
    }

    public function index()
    {
        $this->finder('express_mdl_print_tmpl',array(
            'title'=>app::get('express')->_('快递单模板'),
            'actions'=>array(
                            array('label'=>app::get('express')->_('添加模版'),'icon'=>'add.gif','target'=>'_blank','href'=>'index.php?app=express&ctl=admin_delivery_printer&act=add_tmpl'),
                            array('label'=>app::get('express')->_('导入模版'),'icon'=>'add.gif','target'=>'dialog::{title:\''.app::get('express')->_('导入模版').'\'}','href'=>'index.php?app=express&ctl=admin_delivery_printer&act=import'),
                        ),'use_buildin_set_tag'=>false,'use_buildin_recycle'=>true,'use_buildin_filter'=>false,
            ));
    }


     function do_print(){
        $this->get_delivery_info($_POST,$data);

        $aData = $this->o->getList('image_id',array('target_id' => $_POST['dly_tmpl_id'],'target_type' => 'print_tmpl'));
        $image_id = $aData[0]['image_id'];
        $this->pagedata['bg_id'] = $image_id;
        $url = $this->show_bg_picture(1,$image_id);

        // addnew
        $data['order_id'] = $_POST['order']['order_id'];
        $data['order_print'] = $data['order_id'];
        $oOrder = app::get('b2c')->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $goodsItems = $oOrder->dump($data['order_id'],'*',$subsdf);
        $this->get_order_info($goodsItems,$data);
        $data['text'] = '自定义的内容';
        $xmltool = kernel::single('site_utility_xml');
        $mydata['item'] = $data;

        $this->pagedata['prt_tmpl'] = $this->model->dump($_POST['dly_tmpl_id']);
        $this->pagedata['templateData'] = json_encode(array(
                'name'=>$this->pagedata['prt_tmpl']['prt_tmpl_title'],
                'enable'=>($this->pagedata['prt_tmpl']['shortcut']=='true')?'1':'0',
                'size'=>array(
                    'width'=>$this->pagedata['prt_tmpl']['prt_tmpl_width'],
                    'height'=>$this->pagedata['prt_tmpl']['prt_tmpl_height'],
                    ),
                'imgUrl'=>$url,
                'dpi'=>96,
                'offset'=>array(
                    'x'=>$this->pagedata['prt_tmpl']['prt_tmpl_offsetx'],
                    'y'=>$this->pagedata['prt_tmpl']['prt_tmpl_offsety'],
                ),
                'ptItem'=>json_decode($this->pagedata['prt_tmpl']['prt_tmpl_data'],true),
            ));

        $this->pagedata['testTemplateData'] = json_encode(array(
            array(
                'label'=>app::get('site')->getConf('site.name'),
                'data'=>'shop_name',
            ),
            array(
                'label'=>'√',
                'data'=>'tick',
            ),
            array(
                'label'=>$data['ship_name'],
                'data'=>'ship_name',
            ),
            array(
                'label'=>$data['ship_addr'],
                'data'=>'ship_addr',
            ),
            array(
                'label'=>$data['ship_tel'],
                'data'=>'ship_tel',
            ),
            array(
                'label'=>$data['ship_mobile'],
                'data'=>'ship_mobile',
            ),
            array(
                'label'=>$data['ship_zip'],
                'data'=>'ship_zip',
            ),
            array(
                'label'=>$data['ship_area_0'],
                'data'=>'ship_area_0',
            ),
            array(
                'label'=>$data['ship_area_1'],
                'data'=>'ship_area_1',
            ),
            array(
                'label'=>$data['ship_area_2'],
                'data'=>'ship_area_2',
            ),
            array(
                'label'=>$data['ship_addr'],
                'data'=>'ship_addr',
            ),
            array(
                'label'=>$data['order_count'],
                'data'=>'order_count',
            ),
            array(
                'label'=>$data['order_memo'],
                'data'=>'order_memo',
            ),
            array(
                'label'=>$data['order_count'],
                'data'=>'order_count',
            ),
            array(
                'label'=>$data['order_weight'],
                'data'=>'order_weight',
            ),
            array(
                'label'=>$data['order_price'],
                'data'=>'order_price',
            ),
            array(
                'label'=>$data['text'],
                'data'=>'text',
            ),
            array(
                'label'=>$data['dly_area_0'],
                'data'=>'dly_area_0',
            ),
            array(
                'label'=>$data['dly_area_1'],
                'data'=>'dly_area_1',
            ),
            array(
                'label'=>$data['dly_area_2'],
                'data'=>'dly_area_2',
            ),
            array(
                'label'=>$data['dly_address'],
                'data'=>'dly_address',
            ),
            array(
                'label'=>$data['dly_tel'],
                'data'=>'dly_tel',
            ),
            array(
                'label'=>$data['dly_mobile'],
                'data'=>'dly_mobile',
            ),
            array(
                'label'=>$data['dly_zip'],
                'data'=>'dly_zip',
            ),
            array(
                'label'=>$data['date_y'],
                'data'=>'date_y',
            ),
            array(
                'label'=>$data['date_m'],
                'data'=>'date_m',
            ),
            array(
                'label'=>$data['date_d'],
                'data'=>'date_d',
            ),
            array(
                'label'=>$data['order_name'],
                'data'=>'order_name',
            ),
            array(
                  'label'=>str_replace('&nbsp;', ' ', $data['order_name_a']),
                'data'=>'order_name_a',
            ),
            array(
                  'label'=>str_replace('&nbsp;', ' ', $data['order_name_as']),
                'data'=>'order_name_as',
            ),
            array(
                  'label'=>str_replace('&nsbsp;', ' ', $data['order_name_ab']),
                'data'=>'order_name_ab',
            ),
            array(
                  'label' => (!empty($data['dly_name']) ? $data['dly_name'] : ' '),
                  'data' => 'dly_name',
                  ),
            array(
                  'label' => $data['order_id'],
                  'data' => 'order_id',
                  ),

        ));
 
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->singlepage('admin/delivery/center/printer.html');
    }

    private function get_delivery_info($arr_post,&$data)
    {
        $obj_dly_center = $this->app->model('dly_center');
        $dly_center = $obj_dly_center->dump($arr_post['dly_center']);
        $data['dly_name'] = $dly_center['uname'];

        list($pkg,$regions,$region_id) = explode(':',$arr_post['order']['ship_area']);
        foreach(explode('/',$regions) as $i=>$region){
            $data['ship_area_'.$i]= $region;
        }

        if($dly_center['region']){
            list($pkg,$regions,$region_id) = explode(':',$dly_center['region']);
            foreach(explode('/',$regions) as $i=>$region){
                $data['dly_area_'.$i]= $region;
            }
        }

        $data['dly_address']=$dly_center['address'];
        $data['dly_tel']=$dly_center['phone'] ? $dly_center['phone'] : 0;
        $data['dly_mobile']=$dly_center['cellphone'] ? $dly_center['cellphone'] : 0;
        $data['dly_zip']=$dly_center['zip']?$dly_center['zip']:0;

        $t = time()+($GLOBALS['user_timezone']-SERVER_TIMEZONE)*3600;
        $data['date_y']=date('Y',$t);
        $data['date_m']=date('m',$t);
        $data['date_d']=date('d',$t);

        $data['order_memo'] = $_POST['order']['order_memo'];

        unset($data['ship_area']);
    }

    private function get_order_info($arr_order,&$data)
    {
        $num = 0;
        $weight = 0;
        $math = kernel::single('ectools_math');
        if ($arr_order['member_id'])
        {
            $oMember = app::get('b2c')->model('members');
            $aMem = $oMember->dump($arr_order['member_id'],'*',array(':account@pam'=>array('*')));
            if(!$aMem){
                $data['member_name'] = app::get('express')->_('非会员顾客!');
            }
            else{
                $data['member_name'] = $aMem['pam_account']['login_name'];
            }
        }
        else{
            $data['member_name'] = app::get('express')->_('非会员顾客');
        }

        if ($arr_order)
        {
            $oProduct = app::get('b2c')->model('products');
            $order_item = app::get('b2c')->model('order_items');
            $data['ship_name']   = $arr_order['consignee']['name'];
            $data['ship_addr']   = $arr_order['consignee']['addr'];
            $data['ship_tel']    = $arr_order['consignee']['telephone']?$arr_order['consignee']['telephone']:0;
            $data['ship_mobile'] = $arr_order['consignee']['mobile']?$arr_order['consignee']['mobile']:0;
            $data['ship_zip']    = $arr_order['consignee']['zip']?$arr_order['consignee']['zip']:0;
            $data['order_memo'] || ( $data['order_memo']  = $arr_order['memo']?$arr_order['memo']:'订单缺省备注');
            $i=0;
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
            }
            foreach ($arr_order['order_objects'] as $k=>$item)
            {
                if ($item['obj_type'] != 'goods')
                {
                    if ($item['obj_type'] == 'gift')
                    {
                        foreach ((array)$item['order_items'] as $key=> $val)
                        {
                            if (!$val['products'])
                            {
                                $tmp = $order_item->getList('*', array('item_id'=>$val['item_id']));
                                $val['products']['bn'] = $tmp[0]['bn'];
                                $val['products']['spec_info'] = $tmp[0]['bn'];
                            }

                            $arr_service_goods_type_obj[$item['obj_type']]->get_default_dly_order_info($val,$data);

                        }
                    }
                    else
                    {
                        $arr_service_goods_type_obj[$item['obj_type']]->get_default_dly_order_info($item,$data);
                    }
                }
                else
                {
                    foreach ((array)$item['order_items'] as $key=> $val)
                    {
                        if ($val['item_type'] == "product" || $val['item_type'] == "ajunct")
                        {
                            if ($val['item_type'] == "product")
                                $val['item_type'] = 'goods';

                            if (!$val['products'])
                            {
                                $tmp = $order_item->getList('*', array('item_id'=>$val['item_id']));
                                $val['products']['bn'] = $tmp[0]['bn'];
                                $val['products']['spec_info'] = $tmp[0]['bn'];
                            }

                            $arr_service_goods_type_obj[$val['item_type']]->get_default_dly_order_info($val,$data);

                        }
                        else
                        {
                            if (!$val['products'])
                            {
                                $tmp = $order_item->getList('*', array('item_id'=>$val['item_id']));
                                $val['products']['bn'] = $tmp[0]['bn'];
                                $val['products']['spec_info'] = $tmp[0]['bn'];
                            }

                            $arr_service_goods_type_obj[$val['item_type']]->get_default_dly_order_info($val,$data);
                        }
                        $weight = $math->number_plus(array($weight, $val['weight']));
                        $num = $math->number_plus(array($num, $val['quantity']));
                        /*elseif($val['item_type'] == "pkg")
                        {
                            $data['order_name'][$i] = array('name'=>$val['name']);
                            $data['order_name_a'][$i] = array('name'=>$val['name'], 'num'=>$val['quantity']);
                            $data['order_name_as'][$i] = array('name'=>$val['name'], 'num'=>$val['quantity'], 'spec'=>$val['products']['spec_info']);
                            $data['order_name_ab'][$i] = array('name'=>$val['name'], 'num'=>$val['quantity'], 'bn'=>$val['products']['bn']);
                            $i++;
                        }*/
                    }
                }
            }
        }
        $data['order_count'] = $num;
        $data['order_weight'] = $weight;
        $data['order_price'] = $arr_order['cur_amount'];
    }

    public function add_tmpl($image_id=null)
    {
        $default_font = array(
            array(
                'label'=>'宋体',
                'data'=>'宋体',
            ),
            array(
                'label'=>'黑体',
                'data'=>'黑体',
            ),
            array(
                'label'=>'Arial',
                'data'=>'Arial',
            ),
            array(
                'label'=>'Tahoma',
                'data'=>'Tahoma',
            ),
            array(
                'label'=>'Times New Roman',
                'data'=>'Times New Roman',
            ),
            array(
                'label'=>'Vrinda',
                'data'=>'Vrinda',
            ),
            array(
                'label'=>'Verdana',
                'data'=>'Verdana',
            ),
            array(
                'label'=>'Serif',
                'data'=>'Serif',
            ),
            array(
                'label'=>'Cursive',
                'data'=>'Cursive',
            ),
            array(
                'label'=>'Fantasy',
                'data'=>'Fantasy',
            ),
            array(
                'label'=>'Sans-Serif',
                'data'=>'Sans-Serif',
            ),
        );
        foreach ($default_font as $ft_item){
            $this->pagedata['printData']['fontItem'][] = $ft_item;
        }
        if(PRINTER_FONTS){
            $font = explode("|",PRINTER_FONTS);
            foreach ($font as $ft_item){
                $this->pagedata['printData']['fontItem'][] = array(
                    'label'=>$ft_item,
                    'data'=>$ft_item
                );
            }
        }
        $this->pagedata['tmpl'] = $this->model->dump($tmpl_id);
        $this->pagedata['res_url'] = $this->app->res_url;


        $url = $this->show_bg_picture(1,$image_id);
        $elements = $this->model->getElements();
        foreach ((array)$elements as $key=>$ele_item){
            $this->pagedata['printData']['printItem'][] = array(
                'label'=>$ele_item,
                'data'=>$key
            );
        }
        $this->pagedata['printData'] = json_encode($this->pagedata['printData']);
        $this->pagedata['templateData'] = json_encode(array(
            'name'=>'',
            'enable'=>'1',
            'size'=>array(
                'width'=>'240',
                'height'=>'158',
                ),
            'imgUrl'=>$url,
            'dpi'=>96,
            'offset'=>array(
                'x'=>'0',
                'y'=>'0',
            ),
            'ptItem'=>array(),
        ));

        $this->pagedata['save_action'] = 'add_save';
        $this->singlepage('admin/printer/dly_printer_editor.html');
    }

    /**
     * 添加快递单模版
     * @param null
     * @return null
     */
    public function add_save()
    {
        $o = &app::get('image')->model('image_attach');
        $this->begin('javascript:opener.finderGroup["'.$_POST['finder_id'].'"].refresh();window.close();');

        if (!$_POST)
            $this->end(false,app::get('express')->_('需要添加的信息不存在！'));

        $tmpl_data = array();
        $tmpl_data['prt_tmpl_offsety'] = floatval($_POST['offset']['y']);
        $tmpl_data['prt_tmpl_offsetx'] = floatval($_POST['offset']['x']);
        $tmpl_data['shortcut'] = $_POST['enable'];
        $tmpl_data['prt_tmpl_title'] = $_POST['name'];
        $tmpl_data['prt_tmpl_height'] = $_POST['size']['height'];
        $tmpl_data['prt_tmpl_width'] = $_POST['size']['width'];
        $tmpl_data['prt_tmpl_data'] = json_encode($_POST['ptItem']);
        $tpl_id = $this->model->insert($tmpl_data);
        if (!$tpl_id)
            $this->end(false, app::get('express')->_('添加快递单模版失败！'));

        if (isset($_POST['tmp_bg']) && $_POST['tmp_bg'])
        {
            $sdf = array(
                'attach_id' => $attach_id?$attach_id:'',
                'target_id' => $tpl_id,
                'target_type' => 'print_tmpl',
                'image_id' => $_POST['tmp_bg'],
                'last_modified' => time(),
            );
            if (!$o->save($sdf))
                $this->end(false, app::get('express')->_('添加快递单模版背景失败！'));
        }

        $this->end(true,app::get('express')->_('添加快递单模版成功！'));
    }

    /**
     * 修改快递单模版
     * @param null
     * @return null
     */
    public function modify_save()
    {
        $o = &app::get('image')->model('image_attach');
        $this->begin('javascript:opener.finderGroup["'.$_POST['finder_id'].'"].refresh();window.close();');

        if (!$_POST['prt_tmpl_id'])
        {
            $this->end(false,app::get('express')->_('要修改的快递单模版不存在！'));
        }
        else
        {
            $tmpl_data = array();
            $tmpl_data['prt_tmpl_id'] = $_POST['prt_tmpl_id'];
            $tmpl_data['prt_tmpl_offsety'] = floatval($_POST['offset']['y']);
            $tmpl_data['prt_tmpl_offsetx'] = floatval($_POST['offset']['x']);
            $tmpl_data['shortcut'] = $_POST['enable'];
            $tmpl_data['prt_tmpl_title'] = $_POST['name'];
            $tmpl_data['prt_tmpl_height'] = $_POST['size']['height'];
            $tmpl_data['prt_tmpl_width'] = $_POST['size']['width'];
            $tmpl_data['prt_tmpl_data'] = json_encode($_POST['ptItem']);

            if ($this->model->update($tmpl_data,array('prt_tmpl_id'=>$_POST['prt_tmpl_id']))){
                $tpl_id = $_POST['prt_tmpl_id'];
                $aData = $o->getList('attach_id',array('target_id' => $tpl_id,'target_type' => 'print_tmpl'));
                $attach_id = $aData[0]['attach_id'];
            }else{
                $tpl_id = false;
            }
        }

        if (isset($_POST['tmp_bg']) && $_POST['tmp_bg'])
        {
            $sdf = array(
                'attach_id' => $attach_id?$attach_id:'',
                'target_id' => $tpl_id,
                'target_type' => 'print_tmpl',
                'image_id' => $_POST['tmp_bg'],
                'last_modified' => time(),
            );
            if (!$o->save($sdf))
                $this->end(false, app::get('express')->_('修改快递单模版背景失败！'));
        }

        $this->end(true,app::get('express')->_('修改快递单模版成功！'));
    }

    /**
     * 显示编辑快递单模版的页面
     * @param string 模版id
     * @return null
     */
    public function edit_tmpl($tmpl_id)
    {
        $default_font = array(
            array(
                'label'=>'宋体',
                'data'=>'宋体',
            ),
            array(
                'label'=>'黑体',
                'data'=>'黑体',
            ),
            array(
                'label'=>'Arial',
                'data'=>'Arial',
            ),
            array(
                'label'=>'Tahoma',
                'data'=>'Tahoma',
            ),
            array(
                'label'=>'Times New Roman',
                'data'=>'Times New Roman',
            ),
            array(
                'label'=>'Vrinda',
                'data'=>'Vrinda',
            ),
            array(
                'label'=>'Verdana',
                'data'=>'Verdana',
            ),
            array(
                'label'=>'Serif',
                'data'=>'Serif',
            ),
            array(
                'label'=>'Cursive',
                'data'=>'Cursive',
            ),
            array(
                'label'=>'Fantasy',
                'data'=>'Fantasy',
            ),
            array(
                'label'=>'Sans-Serif',
                'data'=>'Sans-Serif',
            ),
        );
        foreach ($default_font as $ft_item){
            $this->pagedata['printData']['fontItem'][] = $ft_item;
        }
        if(PRINTER_FONTS){
            $font = explode("|",PRINTER_FONTS);
            foreach ($font as $ft_item){
                $this->pagedata['printData']['fontItem'][] = array(
                    'label'=>$ft_item,
                    'data'=>$ft_item
                );
            }
        }
        $this->pagedata['tmpl'] = $this->model->dump($tmpl_id);
        $this->pagedata['res_url'] = $this->app->res_url;

        if($this->pagedata['tmpl']){
            $aData = $this->o->getList('image_id',array('target_id' => $tmpl_id,'target_type' => 'print_tmpl'));
            $image_id = $aData[0]['image_id'];
            $url = $this->show_bg_picture(1,$image_id);

            $elements = $this->model->getElements();
            foreach ((array)$elements as $key=>$ele_item){
                $this->pagedata['printData']['printItem'][] = array(
                    'label'=>$ele_item,
                    'data'=>$key
                );
            }
            $this->pagedata['printData'] = json_encode($this->pagedata['printData']);
            $this->pagedata['save_action'] = 'modify_save';
            $this->pagedata['templateData'] = json_encode(array(
                'name'=>$this->pagedata['tmpl']['prt_tmpl_title'],
                'enable'=>($this->pagedata['tmpl']['shortcut']=='true')?'1':'0',
                'size'=>array(
                    'width'=>$this->pagedata['tmpl']['prt_tmpl_width'],
                    'height'=>$this->pagedata['tmpl']['prt_tmpl_height'],
                    ),
                'imgUrl'=>$url,
                'dpi'=>96,
                'offset'=>array(
                    'x'=>$this->pagedata['tmpl']['prt_tmpl_offsetx'],
                    'y'=>$this->pagedata['tmpl']['prt_tmpl_offsety'],
                ),
                'ptItem'=>json_decode($this->pagedata['tmpl']['prt_tmpl_data'],true),
            ));
            $this->singlepage('admin/printer/dly_printer_editor.html');
        }else{
            echo "<div class='notice'>ERROR ID</div>";
        }
    }

    public function add_same($tmpl_id)
    {
        /*todo 背景图*/
        $aData = $this->o->getList('image_id',array('target_id' => $tmpl_id,'target_type' => 'print_tmpl'));
        $image_id = $aData[0]['image_id'];
        if($image_id){
         $this->pagedata['image_id'] = $image_id;
        }
        $this->pagedata['tmpl_id'] = $tmpl_id;
        $url = $this->show_bg_picture(1,$image_id);
        $this->pagedata['tmpl_bg'] = $url;
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['tmpl_id'] = $tmpl_id;
        if($this->pagedata['tmpl'] = $this->model->dump($tmpl_id)){
                unset($this->pagedata['tmpl']['prt_tmpl_id']);
                $this->pagedata['elements'] = $this->model->getElements();
                $this->pagedata['save_action'] = 'add_save';
                $this->singlepage('admin/printer/dly_printer_editor.html');
        }
        else{
                 echo "<div class='notice'>ERROR ID</div>";
        }
    }

    function print_test(){
        $this->pagedata['dpi'] = 96;
        $o = &app::get('image')->model('image_attach');

        if($_POST['tmp_bg']){
            $this->pagedata['bg_id'] = $_POST['tmp_bg'];
        }else if($_POST['prt_tmpl_id']){
            $tpl_id = $_POST['prt_tmpl_id'];
            $aData = $o->getList('image_id',array('target_id' => $tpl_id,'target_type' => 'print_tmpl'));
            $this->pagedata['bg_id'] = $aData[0]['image_id'];
        }
        $this->pagedata['res_url'] = $this->app->res_url;

        $this->display('admin/printer/dly_print_test.html');
    }

    public function upload_bg($printer_id=0){
        $this->pagedata['dly_printer_id'] = $printer_id;
        $this->display('admin/printer/dly_printer_uploadbg.html');
    }

    function import(){
        $this->display('admin/printer/dly_printer_import.html');
    }

    public function do_upload_bg()
        {
         $url = $this->show_bg_picture(1,$_POST['background']);
         echo '<script>
        window.pt.replaceBackground("'.$url.'");
        window.pt.setBgID("'.$_POST['background'].'");
        window.pt.dlg.close();
        </script>';
    }


    function download($tmpl_id){
        $tmpl = $this->model->dump($tmpl_id);
        $tar = kernel::single('base_tar');
        $tar->addFile('info',serialize($tmpl));
        $aData = $this->o->getList('image_id',array('target_id' => $tmpl_id,'target_type' => 'print_tmpl'));
        $image_id = $aData[0]['image_id'];

        if($bg = $this->show_bg_picture(1,$image_id)){
            $tar->addFile('background.jpg',file_get_contents($bg));
        }

        #kernel::single('base_session')->close();
        $charset = kernel::single('base_charset');
        $name = $charset->utf2local($tmpl['prt_tmpl_title'],'zh');
        @set_time_limit(0);
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header('Content-type: application/octet-stream');
        header('Content-type: application/force-download');
        header('Content-Disposition: attachment; filename="'.$name.'.dtp"');
        $tar->getTar('output');
    }

    public function done_upload_bg($rs,$file){
        if($rs){
            $url = 'index.php?app=express&ctl=admin_delivery_printer&act=show_bg_picture&p[0]='.$rs.'&p[1]='.$file;
            echo '<script>
                if($("dly_printer_bg")){
                    $("dly_printer_bg").value = "'.$file.'";
        }else{
              new Element("input",{id:"dly_printer_bg",type:"hidden",name:"tmp_bg",value:"__none__"}).inject("dly_printer_form");
        }

        window.printer_editor.dlg.close();
        window.printer_editor.setPicture("'.$url.'");
            </script>';
        }else{
            echo 'Error on upload:'.$file;
        }
    }

    public function show_picture($mode, $image_id)
    {
        readfile($this->show_bg_picture($mode, $image_id));exit;
    }

    public function show_bg_picture($mode,$file){
        $obj_storager = kernel::single("base_storager");
        $str_file = $obj_storager->image_path($file);
        return $str_file;
    }

     function do_upload_pkg()
     {
        $this->begin();
        $file = $_FILES['package'];
        $file_name  = substr($file['name'],strrpos($file['name'],'.'));
        $extname = strtolower($file_name);
        $tar = kernel::single('base_tar');
        $target = DATA_DIR . '/tmp';
        if($extname=='.dtp')
        {
            if($tar->openTAR($file['tmp_name'],$target) && $tar->containsFile('info'))
            {
                if(!($info = unserialize($tar->getContents($tar->getFile('info')))))
                {
                    $this->end(false, app::get('express')->_('无法读取结构信息,模板包可能已损坏！'));
                }
                $info['prt_tmpl_id']='';
                if($tpl_id=$this->model->insert($info))
                {
                    if($tar->containsFile('background.jpg'))
                    { //包含背景图
                        $image = app::get('image')->model('image');
                        $image_id = $image->gen_id();
                        $pic = ($tar->getContents($tar->getFile('background.jpg')));
                        file_put_contents(DATA_DIR.'/'.$tpl_id.'.jpg',$tar->getContents($tar->getFile('background.jpg')));
                        $Image_id = $image->store(DATA_DIR.'/'.$tpl_id.'.jpg',$Image_id);
                        unlink(DATA_DIR.'/'.$tpl_id.'.jpg');
                        $sdf = array(
                            'target_id' => $tpl_id,
                            'target_type' => 'print_tmpl',
                            'image_id' => $Image_id,
                            'last_modified' => time(),
                        );

                        if(!($this->o->save($sdf)))
                        {
                            $this->end(false, app::get('express')->_('模板包中图片有误！'));
                        }
                        else
                        {
                            /*echo "<script>var _dialogIns = top.$('form-express-uploadtpl').getParent('.dialog').retrieve('instance');if(_dialogIns)_dialogIns.close();top.finderGroup['" . $_GET['_finder']['finder_id'] . "'].refresh();</script>";*/
                            $this->end(true, app::get('express')->_('上传成功！'));
                        }
                    }
                }

            }
            else
            {
                $this->end(false, app::get('express')->_('无法解压缩,模板包可能已损坏！'));
            }

        }
        else
        {
            $this->end(false, app::get('express')->_('必须是shopex快递单模板包(.dtp)'));
        }
    }
}
