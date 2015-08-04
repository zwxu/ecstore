<?php
class business_ctl_admin_storemanger extends desktop_controller {
    var $workground = 'business.wrokground.store';
    var $pagelimit = 10;
    var $member_model ;
    public function __construct($app) {
        parent :: __construct($app);
        $this -> member_model = $this -> app -> model('storemanger');
        header("cache-control: no-store, no-cache, must-revalidate");
    } 
    function index() {
      
        $custom_actions[] = array('label' => "添加店铺", 'href' => 'index.php?app=business&ctl=admin_storemanger&act=add_page&p[0]=add', 'target' => '_blank');
        $custom_actions[] =  array('label' => "批量操作",
                        'icon' => 'batch.gif',
                        'group' => array(
                            array('label' => app :: get('shop') -> _('店铺违规'), 'icon' => 'download.gif', 'submit' => 'index.php?app=business&ctl=admin_storemanger&act=singleBatchEdit&p[0]=violation', 'target' => 'dialog'),
                            array('label' => app :: get('shop') -> _('店铺排序'), 'icon' => 'download.gif', 'submit' => 'index.php?app=business&ctl=admin_storemanger&act=singleBatchEdit&p[0]=storemangerdorder', 'target' => 'dialog'),                        
                            array('label' => app :: get('shop') -> _('店铺识别码'), 'icon' => 'download.gif', 'submit' => 'index.php?app=business&ctl=admin_storemanger&act=createappkey'),
                            array('label' => app :: get('shop') -> _('店铺有效期'), 'icon' => 'download.gif', 'submit' => 'index.php?app=business&ctl=admin_storemanger&act=singleBatchEdit&p[0]=storemangertime', 'target' => 'dialog'),

                            ),
                        );


        if($this->has_permission('send_email')){
            $custom_actions[] =  array('label'=>app::get('b2c')->_('群发邮件'),'submit'=>'index.php?app=b2c&ctl=admin_member&act=send_email&source=index','target'=>'dialog::{title:\''.app::get('b2c')->_('群发邮件').'\',width:700,height:400}');
        }
        if($this->has_permission('send_msg')){
            $custom_actions[] =  array('label'=>app::get('b2c')->_('群发站内信'),'submit'=>'index.php?app=b2c&ctl=admin_member&act=send_msg&source=index','target'=>'dialog::{title:\''.app::get('b2c')->_('群发站内信').'\',width:500,height:350}');
        }
        if($this->has_permission('send_sms')){
            $custom_actions[] =  array('label'=>app::get('b2c')->_('群发短信'),'submit'=>'index.php?app=b2c&ctl=admin_member&act=send_sms&source=index','target'=>'dialog::{title:\''.app::get('b2c')->_('群发短信').'\',width:500,height:350}');
        }


        $actions_base['title'] = app::get('b2c')->_('店铺列表');
        $actions_base['actions'] = $custom_actions;
        $actions_base['allow_detail_popup'] = true;
        $actions_base['use_buildin_export'] = true;
        $actions_base['use_buildin_set_tag'] = true;
        $actions_base['use_buildin_filter'] = true;
        $actions_base['use_buildin_recycle'] = false;
        $actions_base['finder_aliasname'] ='index';
        $actions_base['use_view_tab'] = true;
        $actions_base['object_method'] =array('count' => 'count_finder_approved', 'getlist' => 'get_list_approved');
        $actions_base['base_filter'] = array('approved'=>'1');
       
        $this -> finder('business_mdl_storemanger',$actions_base);
                
    } 

    function indexcat() {
        $this -> finder('business_mdl_storemanger', array('title' => '分类店铺列表',
                'allow_detail_popup' => true,
                'use_buildin_export' => true,
                'use_buildin_set_tag' => true,
                'use_buildin_filter' => true,
                'finder_aliasname' =>'indexcat',
                'base_filter' => $_GET['filter'],
                'use_view_tab' => true,
                'actions' => array(
                    array('label' => "批量操作",
                        'icon' => 'batch.gif',
                        'group' => array(
                            array('label' => app :: get('shop') -> _('店铺排序'), 'icon' => 'download.gif', 'submit' => 'index.php?app=business&ctl=admin_storemanger&act=singleBatchEdit&p[0]=storemangerdorder', 'target' => 'dialog'),
                            ),
                        ),
                    ),
                'object_method' => array('count' => 'count_finder', 'getlist' => 'get_list_finder'),
                ));
    } 
    // 店铺审核列表
    function approveindex() {

        $custom_actions[] =  array('label' => "批量操作",
                        'icon' => 'batch.gif',
                        'group' => array(
                            array('label' => app :: get('shop') -> _('店铺审核'), 'icon' => 'download.gif', 'submit' => 'index.php?app=business&ctl=admin_storemanger&act=singleBatchEdit&p[0]=storemangerapproved', 'target' => 'dialog'),
                            array('label' => app :: get('shop') -> _('店铺排序'), 'icon' => 'download.gif', 'submit' => 'index.php?app=business&ctl=admin_storemanger&act=singleBatchEdit&p[0]=storemangerdorder', 'target' => 'dialog'),
                            ),
                        );


        if($this->has_permission('send_email')){
            $custom_actions[] =  array('label'=>app::get('b2c')->_('群发邮件'),'submit'=>'index.php?app=b2c&ctl=admin_member&act=send_email&source=approveindex','target'=>'dialog::{title:\''.app::get('b2c')->_('群发邮件').'\',width:700,height:400}');
        }
        if($this->has_permission('send_msg')){
            $custom_actions[] =  array('label'=>app::get('b2c')->_('群发站内信'),'submit'=>'index.php?app=b2c&ctl=admin_member&act=send_msg&source=approveindex','target'=>'dialog::{title:\''.app::get('b2c')->_('群发站内信').'\',width:500,height:350}');
        }
        if($this->has_permission('send_sms')){
            $custom_actions[] =  array('label'=>app::get('b2c')->_('群发短信'),'submit'=>'index.php?app=b2c&ctl=admin_member&act=send_sms&source=approveindex','target'=>'dialog::{title:\''.app::get('b2c')->_('群发短信').'\',width:500,height:350}');
        }


        $actions_base['title'] = app::get('b2c')->_('店铺审核');
        $actions_base['actions'] = $custom_actions;
        $actions_base['allow_detail_popup'] = true;
        $actions_base['use_buildin_export'] = true;
        $actions_base['use_buildin_set_tag'] = true;
        $actions_base['use_buildin_filter'] = true;
        $actions_base['use_buildin_recycle'] = true;
        $actions_base['finder_aliasname'] ='approveindex';
        $actions_base['base_filter'] = array('approved|in'=>array('0','2'));

        $actions_base['use_view_tab'] = true;
        $actions_base['object_method'] =array('count' => 'count_finder_approve', 'getlist' => 'get_list_approve');
        $this -> finder('business_mdl_storemanger',$actions_base);
       
    } 

    function singleBatchEdit($editType = '') {
        // $objshopinfo = &$this->app->model('storegrade');
        $newFilter = $_POST;
        unset($newFilter['app']);
        unset($newFilter['ctl']);
        unset($newFilter['act']);
        unset($newFilter['_finder']);
        unset($newFilter['marketable']);
        unset($newFilter['_DTYPE_BOOL']);

        if ($_POST['isSelectedAll'] == '_ALL_')
            $_POST['store_id'][0] = '_ALL_';
        if (count($_POST['store_id']) == 0 && $_POST['_finder']['select'] != 'multi' && !$_POST['_finder']['id'] && !$_POST['filter']) {
            echo __('请选择记录');
            exit;
        } 
        if ($_POST['filter']) {
            $_POST['_finder'] = unserialize($_POST['filter']);
            $editType = $_POST['updateAct'];
        } 
        // 选中记录的ID经序列化后保存于页面
        $this -> pagedata['filter'] = serialize($_POST['store_id']);
        $this -> pagedata['catfilter'] = array('parent_id|noequal'=>0);
        $this -> pagedata['editInfo'] = array('count' => count($_POST['store_id']));

        $this -> display('admin/store/batch/batchEdit' . $editType . '.html');
    } 

    function createappkey(){
        if ($_POST['isSelectedAll'] == '_ALL_')
            $_POST['store_id'][0] = '_ALL_';
        if (count($_POST['store_id']) == 0 && $_POST['_finder']['select'] != 'multi' && !$_POST['_finder']['id'] && !$_POST['filter']) {
            echo __('请选择记录');
            exit;
        } 

        $store_id=$_POST['store_id'];
        $objstore = &$this -> app -> model('storemanger');
        $this -> begin('');   
        foreach($store_id as $key=>$value){

            $arystore= $objstore->getList('company_no,company_taxno,company_codename,store_idcard',array('store_id'=>$value)); 
            if($arystore){
               $arystore[0]['store_cert']= base_certificate::gen_sign($arystore[0]);
               $arystore[0]['store_id']=$value;
               if(!$objstore->save($arystore[0])){
                   $haserror=app :: get('business') -> _('保存失败。');

               }
            }

        }
        ini_set('track_errors', '1');
        restore_error_handler();

        if (!$haserror) {
            $this -> end(true, app :: get('business') -> _('批量操作成功。'),'index.php?app=business&ctl=admin_storemanger&act=index');
        } else {
             echo $GLOBALS['php_errormsg'];
        } 

      

    }

    function progressviolation($processitem,$store_id){
        $violation=&$this -> app -> model('violation');
       
        foreach($processitem as $key => $row) {

             $total=$row['total'];
     
             //处理是否到达扣分节点。
             $viodata= $violation -> getList('*', array('cat_id' =>$key),0,1);

             if($viodata){
                 if(strval($viodata[0]['score_value'])>$total){

                 }else {

                   $total_cat_Data[$key]['cat_id'] =$key;
                   $total_cat_Data[$key]['store_id'] = $store_id;
                   $total_cat_Data[$key]['remark'] = $viodata[0]['remark'];
                   $total_cat_Data[$key]['score'] = $total;

                   //到达扣分节点  mktime(23, 59, 59, 12, 31, date("Y")+1);
                   $start = time();

                   if($viodata[0]['goods_days'] > 0 ){
                      $total_cat_Data[$key]['goods_starttime'] =$start;
                      $total_cat_Data[$key]['goods_endtime'] = $start + ($viodata[0]['goods_days'] * 24 * 60 * 60);
                      $cat_Data[$key]['processed'] ='1';
                   }

                   if($viodata[0]['goodsdown_days'] > 0 ){
                      $total_cat_Data[$key]['goodsdown_starttime'] =$start;
                      $total_cat_Data[$key]['goodsdown_endtime'] = $start + ($viodata[0]['goodsdown_days'] * 24 * 60 * 60);
                      $cat_Data[$key]['processed'] ='1';

                   }

                   if($viodata[0]['news_days'] > 0 ){
                      $total_cat_Data[$key]['news_starttime'] =$start;
                      $total_cat_Data[$key]['news_endtime'] = $start + ($viodata[0]['news_days'] * 24 * 60 * 60);
                      $cat_Data[$key]['processed'] ='1';
                   }
                    //Add  2013-8-13  商品降权值
                   if($viodata[0]['news_value'] > 0 ){
                       $total_cat_Data[$key]['news_value'] = $viodata[0]['news_value'];
                       $cat_Data[$key]['processed'] ='1';
                   }

                   if($viodata[0]['store_days'] > 0 ){
                      $total_cat_Data[$key]['store_starttime'] =$start;
                      $total_cat_Data[$key]['store_endtime'] = $start + ($viodata[0]['store_days'] * 24 * 60 * 60);
                      $cat_Data[$key]['processed'] ='1';
                   }

                   if($viodata[0]['storedown_days'] > 0 ){
                      $total_cat_Data[$key]['storedown_starttime'] =$start;
                      $total_cat_Data[$key]['storedown_endtime'] = $start + ($viodata[0]['storedown_days'] * 24 * 60 * 60);
                      $cat_Data[$key]['processed'] ='1';
                   }

                   if($viodata[0]['sales_days'] > 0 ){
                      $total_cat_Data[$key]['sales_starttime'] =$start;
                      $total_cat_Data[$key]['sales_endtime'] = $start + ($viodata[0]['sales_days'] * 24 * 60 * 60);
                      $cat_Data[$key]['processed'] ='1';
                   }

                   if($viodata[0]['earnest_money'] > 0 ){
                       $total_cat_Data[$key]['earnest'] = $viodata[0]['earnest_money'];
                       $cat_Data[$key]['processed'] ='1';
                   }

                   $total_cat_Data[$key]['processed'] = '9';
       
                   if($cat_Data[$key]['processed'] =='1'){
                      $result= $total_cat_Data; 
                   }

                 }

             } 

        }

        return  $result;

    }

    function prepareviolationData($data){ 

         $objvio=&$this -> app -> model('violationcat');
         

         foreach($data['violation'] as $key => $row) {
             //根据cat_id 计算扣除分数
             $tempData=$objvio-> getList('*', array('cat_id' => $row),0,1);
             if(empty($tempData)){
               continue;
             }
            
            /*
             //根据cat_path取得其顶级分类idstrpos($newstring, 'a', 1); 
             $cat_path =$tempData[0]['cat_path']; 
             if(strlen($cat_path)>1){
                $cat_parent = substr($cat_path,0,strpos($cat_path,',',1)+1);
             }
             */

             $cat_Data[$key]['cat_id'] =$row;
             $cat_Data[$key]['store_id'] = $data['store_id'];
             $cat_Data[$key]['remark'] = $data['remark'];
             $cat_Data[$key]['score'] = $tempData[0]['score'];
             //$cat_Data[$key]['total'] = strval($process[$cat_parent]['total']) + strval($tempData[0]['score']);
         }
         return $cat_Data;
    }

    function saveBatchEdit() { 
        $filter = unserialize($_POST['filter']); 

        $objGoods = &$this -> app -> model('storemanger');
        $sto= kernel::single("business_memberstore",$shopinfo['account_id']);

        $haserror = false; 
        $objviolation = &$this -> app -> model('storeviolation');

        $this -> begin('');   
        foreach($filter as $key => $row) {
            $storegrade['store_id'] = $row;
            switch($_POST['updateAct']){
                //店铺违规
                case 'violationed':
                    $storegrade['violation'] = $_POST['set']['violationcat'];
                    $storegrade['remark'] = $_POST['set']['remark'];

                    $violationData = $this -> prepareviolationData($storegrade);
                    
                    //保存本次违规处理
                    foreach($violationData as $key => $item) {
                         if ($objviolation -> save($item)) {
                            
                        }
                    }

                    //判断是否到达分数节点:获取已有的未处理违规处罚总分。
                    $processitem = $objviolation -> getprocessitem($storegrade['store_id']);
                    //保存分数节点处理
                    $resr= $this->progressviolation($processitem,$storegrade['store_id']);

                   

                    if($resr){
                      foreach($resr as $key => $item) {
                           if($objviolation -> save($item)){
                              //更新
                              $re=$objviolation ->updateprocessed($item['store_id'],$item['cat_id']);
                           }

                       }
                      
                    }



                    break;
                //店铺审核
                case 'approved':
                    $storegrade['approved'] = $_POST['set']['approved'];
                    //
                    $storegrade['approvedremark'] = $_POST['set']['approvedremark'];
                    //approve_time
                    $storegrade['approve_time'] = time();

                    //approved_time
                    if($storegrade['approved'] == '1' && empty( $storegrade['approved_time'])){
                        $storegrade['approved_time'] = time();
                    }
                    $activeys=  $_POST['set']['activeys'];

                    if($storegrade['approved'] == '1'){
                        $storegrade['last_time'] = mktime(23, 59, 59, 12, 31, date("Y")+1);
                    }

                    break;

                //统一修改有效期：
                case 'lasttime': 
                    $storegrade['last_time'] = strtotime($_POST['set']['last_time']);
                    break;
                //排序
                default:
                    $storegrade['d_order'] = $_POST['set']['dorder'];
                    break;

            }

            if ($objGoods -> save($storegrade)) {
            
                //发送申请成功的短信邮件或消息
                if ($_POST['updateAct'] == 'approved' ){
                    
                    if($storegrade['approved'] == '1'){
                        $type='approved';

                    }elseif($storegrade['approved'] == '2'){
                        $type='approvefailed';

                    }

                    if($type){
                        $storeinfo =$objGoods ->get_list_finder('*',array('store_id'=>$storegrade['store_id']));
                        if($storeinfo[0]){
                            
                            //解决不能取得当前保存的记录
                            $sto ->process($storeinfo[0]['account_id']);

                            //发送申请成功的短信邮件或消息
                            $aData['uname']=$sto->storeinfo['store_idcardname'].'('.$sto->storeinfo['account_loginname'].')'; 
                            $aData['Remark']=trim($storegrade['approvedremark']);
                            $aData['Issname']= $sto->storeinfo['issue_typename'];
                            $aData['issue_money'] = $sto->storeinfo['store_gradeinfo']['issue_money'];
                            $objGoods->fireEvent($type, $aData, $sto->storeinfo['account_id']);
                        }
                        
                    }
                }

            } 
        } 

        ini_set('track_errors', '1');
        restore_error_handler();

        if (!$haserror) {
            $this -> end(true, app :: get('business') -> _('批量操作成功。'));
        } else {
             echo $GLOBALS['php_errormsg'];
            //$this -> end(false, app :: get('business') -> _('批量操作失败：') . $haserror);
        } 
    } 

    function add_page($title='') {

        if($title){
            $this -> pagedata['title'] = "添加店铺"; 
        }else {
            $this -> pagedata['title'] = "编辑店铺"; 
        }

        // 等级
        $o = $this -> app -> model('storegrade');
        $storegrade = array();
        $storealltype = array();
        foreach($o -> getList('*', '', 0, -1) as $item) {
            // $storegrade[$item['grade_id'].'/'.$item['grade_name']] = $item['grade_name'];
            $storegrade[$item['grade_id']] = $item['grade_name'];
            $storealltype[$item['grade_id']] = $item['issue_type'];
        } 

               


        /**
         * //经营范围
         * $storeregion =array();
         * $m =  app::get('b2c')->model('goods_cat');
         * foreach($m  ->getList('*',array('parent_id'=>'0')) as $item){
         * $storeregion[$item['cat_id']] = $item['cat_name'];
         * }
         * //print_r( $m  ->getList('*',array('parent_id'=>'0')));exit;
         */

        $this -> pagedata['storealltype'] = $storealltype;
        $this -> pagedata['storetype'] = array('parent_id' => '0');
        $this -> pagedata['storegrade'] = $storegrade;
        $this -> pagedata['storeregion'] = $storeregion;
        $this -> pagedata['image_dir'] = &app :: get('image') -> res_url;
        $this -> singlepage('admin/store/new.html');
    } 

    public function toAdd() {
        $aData = $this -> _prepareGoodsData($_POST);

        /*
        if ($aData['earnest'] < $aData['issue_money']) {
            $this -> begin('');
            $this -> end(false, app :: get('business') -> _('保证金金额不足！' . '更新ID为：' . $aData['store_id']));
            exit;
        } 
        */

        $o = $this -> app -> model('storemanger'); 
        // 店主重复检查
        if (!$aData['store_id']) {
            if ($o -> check_id($aData['account_id'], $msg)) {
            } else {
                $this -> begin('');
                $this -> end(false, $msg);
                exit;
            } 
        } 
        /*
        foreach($aData as $key => $val) {
            if ($val == '') unset($aData[$key]);
        } 
        */

        if ($o -> save($aData)) {
                   
            if (! $aData['store_id']) {
                $this -> begin('');
                $this -> end(true, app :: get('business') -> _('保存成功！' . '新增ID为：' . $o -> db -> lastInsertId()));
            } else {
                //add by Huoxh 2014-05-14 店铺关闭后商品未下架。
                if($aData['status']=='0'){//当店铺关店时。则对应的说有商品下架。             
                    $objGoods = app::get('b2c')->model('goods');
                    $glist = $objGoods->setEnabled(array('store_id'=>$aData['store_id']),'false');
                }
                $this -> begin('');
                $this -> end(true, app :: get('business') -> _('更新成功！' . '更新ID为：' . $aData['store_id']));
            } 
        } else {
            $this -> begin('');
            $this -> end(false, app :: get('business') -> _('失败: 操作失败！' . '更新ID为：' . $aData['store_id']));
        } 
    } 

    function _prepareGoodsData(&$data) {
        $shopinfo = $data['shopinfo'];

        if(empty($shopinfo['account_id'])){
             $shopinfo['account_id']  =  $membername = $this -> member_model -> getmemberidbyloginname($loginname);
        }

        //有效期
        $shopinfo['last_time']=strtotime ($shopinfo['last_time']);
     
        $b2c_brand = &app :: get('b2c') -> model('brand');

        $business_brand = &app :: get('business') -> model('brand');

        if (empty($shopinfo['certification']['uname'])) {
            $shopinfo['certification']['uname'] = 'off';
        } 

        if (empty($shopinfo['certification']['ushop'])) {
            $shopinfo['certification']['ushop'] = 'off';
        } 


        if (is_array($shopinfo['brand_id'])) {

            if( $shopinfo['store_id']){

                foreach($shopinfo['brand_id'] as $key1 => $val) {
                    $shopinfo['attach'][$key1]['brand_id'] = $val;
                    $brand = $business_brand -> getList('*', array('brand_id' => $val,'store_id'=>$shopinfo['store_id']));
                    if ($brand[0]) {
                        $shopinfo['attach'][$key1]['id'] = $brand[0]['id'];
                        $shopinfo['attach'][$key1]['brand_name'] = $brand[0]['brand_name'];
                        $shopinfo['attach'][$key1]['brand_url'] = $brand[0]['brand_url'];
                        $shopinfo['attach'][$key1]['brand_desc'] = $brand[0]['brand_desc'];
                        $shopinfo['attach'][$key1]['brand_logo'] = $brand[0]['brand_logo'];
                        $shopinfo['attach'][$key1]['brand_keywords'] = $brand[0]['brand_keywords'];
                        $shopinfo['attach'][$key1]['store_cat'] = $brand[0]['store_cat'];
                        $shopinfo['attach'][$key1]['brand_desc'] = $brand[0]['brand_desc'];
                        $shopinfo['attach'][$key1]['fail_reason'] = $brand[0]['fail_reason'];
                        $shopinfo['attach'][$key1]['disabled'] = $brand[0]['disabled'];
                        $shopinfo['attach'][$key1]['status'] = $brand[0]['status'];
                        $shopinfo['attach'][$key1]['type'] = $brand[0]['type'];
                        $shopinfo['attach'][$key1]['brand_aptitude'] = $brand[0]['brand_aptitude'];
                    } else {

                        $shopinfo['attach'][$key1]['brand_id'] = $val;
                        $brand = $b2c_brand -> getList('*', array('brand_id' => $val));
                        if ($brand[0]) {
                            $shopinfo['attach'][$key1]['brand_name'] = $brand[0]['brand_name'];
                            $shopinfo['attach'][$key1]['brand_url'] = $brand[0]['brand_url'];
                            $shopinfo['attach'][$key1]['brand_desc'] = $brand[0]['brand_desc'];
                            $shopinfo['attach'][$key1]['brand_logo'] = $brand[0]['brand_logo'];
                        } 


                    }
                }



            }else{
                foreach($shopinfo['brand_id'] as $key1 => $val) {
                    $shopinfo['attach'][$key1]['brand_id'] = $val;
                    $brand = $b2c_brand -> getList('*', array('brand_id' => $val));
                    if ($brand[0]) {
                        $shopinfo['attach'][$key1]['brand_name'] = $brand[0]['brand_name'];
                        $shopinfo['attach'][$key1]['brand_url'] = $brand[0]['brand_url'];
                        $shopinfo['attach'][$key1]['brand_desc'] = $brand[0]['brand_desc'];
                        $shopinfo['attach'][$key1]['brand_logo'] = $brand[0]['brand_logo'];
                    } 
                }
            }
        } else {
             if( $shopinfo['store_id']){
                 $brand = $business_brand -> getList('*', array('brand_id' => $shopinfo['brand_id'],'store_id'=>$shopinfo['store_id']));
                if ($brand[0]) {
                    $shopinfo['attach'][0]['id'] = $brand[0]['id'];
                    $shopinfo['attach'][0]['brand_id'] = $shopinfo['brand_id'];
                    $shopinfo['attach'][0]['brand_name'] = $brand[0]['brand_name'];
                    $shopinfo['attach'][0]['brand_url'] = $brand[0]['brand_url'];
                    $shopinfo['attach'][0]['brand_desc'] = $brand[0]['brand_desc'];
                    $shopinfo['attach'][0]['brand_logo'] = $brand[0]['brand_logo'];
                    $shopinfo['attach'][0]['brand_keywords'] = $brand[0]['brand_keywords'];
                    $shopinfo['attach'][0]['store_cat'] = $brand[0]['store_cat'];
                    $shopinfo['attach'][0]['brand_desc'] = $brand[0]['brand_desc'];
                    $shopinfo['attach'][0]['fail_reason'] = $brand[0]['fail_reason'];
                    $shopinfo['attach'][0]['disabled'] = $brand[0]['disabled'];
                    $shopinfo['attach'][0]['status'] = $brand[0]['status'];
                    $shopinfo['attach'][0]['type'] = $brand[0]['type'];
                    $shopinfo['attach'][0]['brand_aptitude'] = $brand[0]['brand_aptitude'];
                } else {

                    $brand = $b2c_brand -> getList('*', array('brand_id' => $shopinfo['brand_id']));
                    if ($brand[0]) {
                        $shopinfo['attach'][0]['brand_id'] = $shopinfo['brand_id'];
                        $shopinfo['attach'][0]['brand_name'] = $brand[0]['brand_name'];
                        $shopinfo['attach'][0]['brand_url'] = $brand[0]['brand_url'];
                        $shopinfo['attach'][0]['brand_desc'] = $brand[0]['brand_desc'];
                        $shopinfo['attach'][0]['brand_logo'] = $brand[0]['brand_logo'];
                    } 


                }

             }else {
                $brand = $b2c_brand -> getList('*', array('brand_id' => $shopinfo['brand_id']));
                if ($brand[0]) {
                    $shopinfo['attach'][0]['brand_id'] = $shopinfo['brand_id'];
                    $shopinfo['attach'][0]['brand_name'] = $brand[0]['brand_name'];
                    $shopinfo['attach'][0]['brand_url'] = $brand[0]['brand_url'];
                    $shopinfo['attach'][0]['brand_desc'] = $brand[0]['brand_desc'];
                    $shopinfo['attach'][0]['brand_logo'] = $brand[0]['brand_logo'];
                } 
             }
        } 
        unset($shopinfo['brand_id']);

        if ($shopinfo['member_id']) {
            foreach($shopinfo['member_id'] as $key1 => $val) {
                $shopinfo['attachmember'][$key1]['member_id'] = $val;
            } 
            unset($shopinfo['member_id']);
        } 

        // 经营范围
        /*
        $obj_storegrade = $this -> app -> model('storegrade');

        $stype = $obj_storegrade -> getList('*', array('grade_id' => $shopinfo['store_grade']));

        if ($stype[0]['issue_type']) {
            $shopinfo['issue_money'] = $stype[0]['issue_money'];
            switch ($stype[0]['issue_type']) {
                case '0':
                    unset($shopinfo['store_region']);
                    break;
                default:

                    if (is_array($shopinfo['store_region'])) {
                        $shopinfo['store_region'] = ',' . implode(",", $shopinfo['store_region']) . ',';
                    } else {
                        $shopinfo['store_region'] = ',' . $shopinfo['store_region'] . ',';
                    } 
                    break;
            } 
        } 
        */

         if (is_array($shopinfo['store_region'])) {
                        $shopinfo['store_region'] = ',' . implode(",", $shopinfo['store_region']) . ',';
         } else if($shopinfo['store_region']){
                        $shopinfo['store_region'] = ',' . $shopinfo['store_region'] . ',';
         } else {
                 $shopinfo['store_region'] = '';
         }

          //密保
        $obj_members = &app :: get('b2c') -> model('members');
        $member =  $obj_members -> getList('*', array('member_id' =>$account_id));

        if($member){
            if(empty( $shopinfo['pw_question'])){
             $shopinfo['pw_question']=$member[0]['pw_question'];
            }
            if(empty( $shopinfo['pw_answer'])){
                $shopinfo['pw_answer']=$member[0]['pw_answer'];
            }
             //店主手机，邮箱
             if(empty( $shopinfo['tel'])){
                $shopinfo['tel']=$member[0]['mobile'];
             }
             if(empty($shopinfo['zip'])){
                $shopinfo['zip']=$member[0]['email'];
             }

        }

        return $shopinfo;
    } 

    public function edit() {
        if (($id = $_GET['store_id'])) {
            $filter = array('store_id' => $id,
                );

            $arr_info = $this -> app -> model('storemanger') -> dump($filter, '*', 'default');

            if($arr_info['account_id']){

                //密保
                $obj_members = &app :: get('b2c') -> model('members');
                $member =  $obj_members -> getList('*', array('member_id' => $arr_info['account_id']));

                if($member){
                     $arr_info['pw_question']=$member[0]['pw_question'];
                     $arr_info['pw_answer']=$member[0]['pw_answer'];
                     //店主手机，邮箱
                     if(empty( $arr_info['tel']) || $arr_info['tel'] != $member[0]['mobile']){
                        $arr_info['tel']=$member[0]['mobile'];
                     }

                     if(empty( $arr_info['zip']) || $arr_info['zip'] != $member[0]['email']){
                        $arr_info['zip']=$member[0]['email'];
                     }

                }
            }



            $arr_info['certification'] = unserialize($arr_info['certification']);

            $arryshopid = array(); 

           


            // 经营范围
            $obj_storegrade = $this -> app -> model('storegrade');
            $stype = $obj_storegrade -> getList('*', array('grade_id' => $arr_info['store_grade']));

            if ($stype[0]['issue_type'] >= '0') {

               

               foreach($arr_info['attach'] as $val) {
                        $arryshopid[] = $val['brand_id'];
               } 

               $arr_info['brand_id'] = $arryshopid;  

           
               /*

                if ($arr_info['attach']) {
                    switch ($stype[0]['issue_type']) {
                        case '1':
                            foreach($arr_info['attach'] as $val) {
                                $arryshopid = $val['brand_id'];
                            } 

                            $arr_info['brand_id'] = $arryshopid;

                            break;
                        default:

                            foreach($arr_info['attach'] as $val) {
                                $arryshopid[] = $val['brand_id'];
                            } 

                            $arr_info['brand_id'] = $arryshopid;
                            break;
                    } 
                } 
                */
                /*

                if ($arr_info['store_region']) {
                    switch ($stype[0]['issue_type']) {
                        case '0':
                            unset($arr_info['store_region']);
                            break;

                        case '2':
                            $arr_info['store_region'] = str_replace(',', "", $arr_info['store_region']);
                            break;
                        default:

                            $arr_info['store_region'] = explode(",", $arr_info['store_region']);
                            break;
                    } 
                }
                */

                if ($arr_info['store_region']) {
                     $arr_info['store_region'] = explode(",", $arr_info['store_region']);
                }


                //print_r($arr_info['store_region']);exit;


            } 

            unset($arr_info['attach']);

            $arrymemberid = array();
            foreach($arr_info['attachmember'] as $val) {
                $arrymemberid[] = $val['member_id'];
            } 

            $arr_info['member_id'] = $arrymemberid;
            unset($arr_info['attachmember']); 
            // 经营范围
            if ($arr_info['store_grade']) {
                $obj_storegrade = $this -> app -> model('storegrade');
                $stype = $obj_storegrade -> getList('*', array('grade_id' => $arr_info['store_grade']));
                $arr_info['issue_type'] = $stype[0]['issue_type'];
            } else {
                $arr_info['issue_type'] = 0;
            } 

           

           /*
           print_r( "<pre>");
           print_r( $arr_info);
           print_r( "</pre>");
           
           exit;
           */
           
            // 后台编辑 无商品信息时提示数据错误
            if (!$arr_info) {
                exit('操作失败！相关信息为空！数据异常！！');
            } else {
                // 其中的'target_type'！='shopinfo' 的图片
                foreach ($arr_info['images'] as $i => $value) {
                    if ($arr_info['images'][$i]['target_type'] != 'shopinfo') {
                        unset($arr_info['images'][$i]);
                    } 
                } 

                $this -> pagedata['goods']['images'] = $arr_info['images'];
                $this -> pagedata['goods']['image_default_id'] = $arr_info['image_default_id'];
                $this -> pagedata['shopinfo'] = $arr_info;
                $this -> add_page();
            } 
        } else {
            exit('操作失败！相关信息为空！店铺id不能为空！');
        } 
    } 

    function getmemberidbyloginname() {
        $loginname = trim($_POST['name']);
        $membername = $this -> member_model -> getmemberidbyloginname($loginname);
        echo json_encode($membername);
    } 

    function changeregion() {
        $render = $this -> app -> render();

        $storegrade = trim($_POST['selectvaue']);

        $this -> pagedata['storetype'] = array('parent_id' => '0'); 
        // 经营范围
        $obj_storegrade = $this -> app -> model('storegrade');

        $stype = $obj_storegrade -> getList('*', array('grade_id' => $storegrade));

        if ($stype[0]['issue_type'] >= '0') {
            switch ($stype[0]['issue_type']) {
                case '0':
                    $this -> pagedata['message'] = app :: get('business') -> _('全部类目');
                    echo $render -> fetch('admin/store/none_dialog.html');

                    break;
                case '1':
                    echo $render -> fetch('admin/store/chk_dialog.html');
                    break;

                case '2':
                    echo $render -> fetch('admin/store/rdo_dialog.html');
                    break;
                default:
                    echo $render -> fetch('admin/store/chk_dialog.html');
                    break;
            } 
        } 
    } 


    function updateissuename() {
        $render = $this -> app -> render();

        $storegrade = trim($_POST['selectvaue']);

        $this -> pagedata['storetype'] = array('parent_id' => '0'); 
        // 经营范围
        $obj_storegrade = $this -> app -> model('storegrade');

        $stype = $obj_storegrade -> getList('*', array('grade_id' => $storegrade));

        if ($stype[0]['issue_type'] >= '0') {
            switch ($stype[0]['issue_type']) {
                case '0':
                    echo app :: get('business') -> _('卖场型旗舰店');
                    break;
                case '1':
                    echo app :: get('business') -> _('专卖店');
                    break;

                case '2':
                    echo app :: get('business') -> _('专营店');
                    break;
                case '3':
                    echo app :: get('business') -> _('品牌旗舰店');
                    break;
                default:
                    echo app :: get('business') -> _('类型错误');
                    break;
            } 
        } 
    } 

    function changebrand() {
        $render = $this -> app -> render();

        $storegrade = trim($_POST['selectvaue']); 
        // $this -> pagedata['storetype'] = array('parent_id' => '0');
        //
        $obj_storegrade = $this -> app -> model('storegrade');

        $stype = $obj_storegrade -> getList('*', array('grade_id' => $storegrade));

        if ($stype[0]['issue_type'] >= '0') {
            switch ($stype[0]['issue_type']) {
                //品牌旗舰店（单一品牌）专卖店(单一品牌)
                case '3':
                case '1':
                    echo $render -> fetch('admin/store/rdo_dialogbrand.html');
                    break;
                default:
                    echo $render -> fetch('admin/store/chk_dialogbrand.html');
                    break;
            } 
        } 
    } 
    

    public function pagination($current,$count,$get){
        $app = app::get('business');
        $render = $app->render();
        $ui = new base_component_ui($this->app);
        //unset($get['singlepage']);
        $link = 'index.php?app=business&ctl=admin_storemanger&act=ajax_html&id='.$get['id'].'&finder_act='.$get['page'].'&'.$get['page'].'=%d';
        $this->pagedata['pager'] = $ui->pager(array(
            'current'=>$current,
            'total'=>ceil($count/$this->pagelimit),
            'link' =>$link,
            ));
    }
    
    public function ajax_html(){
        $finder_act = $_GET['finder_act'];
        $html = $this->$finder_act($_GET['id']);
        echo $html;
    }
    
    function detail_space($store_id){
        if(!$store_id) return null;
        $nPage = $_GET['detail_space'] ? $_GET['detail_space'] : 1;
        $app = app::get('business');
        $obj_store = $app->model('storemanger');
        $obj_user = kernel::single('desktop_user');
        $obj_log = $app->model('store_log');

        if($_POST){
            if(!$obj_store->change_spece($store_id,$_POST['modify_space'],$msg,$obj_user->user_id,$_POST['modify_remark'])){
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.$msg.'",_:null}';
                exit;
            }
        }
        $filter = array('store_id'=>$store_id,'type'=>'space');
        $count = $obj_log->count($filter);
        $data['log'] = $obj_log->getList('*', $filter, $this->pagelimit*($nPage-1), $this->pagelimit, 'last_modify desc');
        $accountObj = app::get('pam')->model('account');
        foreach($data['log'] as $key=>$val){
            $data['log'][$key]['change_value'] = floor($data['log'][$key]['change_value']*10/1024/1024/1024)/10;
            if($val['source'] == '1'){
                $operatorInfo = $accountObj->getList('login_name',array('account_id' => $val['operator']));
                $data['log'][$key]['operator_name'] = $operatorInfo['0']['login_name'];
            }else{
                $data['log'][$key]['operator_name'] = '管理员';
            }
        }
        $store_info = $obj_store->getList('store_space,store_usedspace',array('store_id'=>$store_id));
        $data['total'] = floor($store_info[0]['store_space']*10/1024/1024/1024)/10;
        $data['space'] = floor($store_info[0]['store_usedspace']*100/1024/1024/1024)/100;
        $this->pagedata['store'] = $data;

        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_space';
        $this->pagination($nPage,$count,$_GET);
        echo $this->fetch('admin/store/manger/page_space_list.html');
    }
    
    function detail_experience($store_id){
        if(!$store_id) return null;
        $nPage = $_GET['detail_experience'] ? $_GET['detail_experience'] : 1;
        $app = app::get('business');
        $obj_store = $app->model('storemanger');
        $obj_user = kernel::single('desktop_user');
        $obj_log = $app->model('store_log');

        if($_POST){
           if(!$obj_store->change_experience($store_id,$_POST['modify_experience'],$msg,$obj_user->user_id,2,null,$_POST['modify_remark'])){
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.$msg.'",_:null}';
                exit;
           }
        }
        $filter = array('store_id'=>$store_id,'type'=>'experience');
        $count = $obj_log->count($filter);
        $data['log'] = $obj_log->getList('*', $filter, $this->pagelimit*($nPage-1), $this->pagelimit, 'last_modify desc');
        $accountObj = app::get('pam')->model('account');
        foreach($data['log'] as $key=>$val){
            if($val['source'] == '1'){
                $operatorInfo = $accountObj->getList('login_name',array('account_id' => $val['operator']));
                $data['log'][$key]['operator_name'] = $operatorInfo['0']['login_name'];
            }else{
                $data['log'][$key]['operator_name'] = '管理员';
            }
        }
        $data['total'] = $obj_store->getList('experience',array('store_id'=>$store_id));
        $data['total'] = $data['total'][0]['experience'];
        $this->pagedata['store'] = $data;

        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_experience';
        $this->pagination($nPage,$count,$_GET);
        echo $this->fetch('admin/store/manger/page_experience_list.html');
    }
 

    function detail_earnest($store_id){
        if(!$store_id) return null;
        $nPage = $_GET['detail_earnest'] ? $_GET['detail_earnest'] : 1;
        $app = app::get('business');
        $obj_store = $app->model('storemanger');
        $obj_user = kernel::single('desktop_user');
        $obj_log = $app->model('earnest_log');

        if($_POST){
           if(!$obj_store->change_earnest($store_id,$_POST['modify_earnest'],$msg,$obj_user->user_id,2,null,$_POST['modify_remark'])){
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.$msg.'",_:null}';
                exit;
           }
        }
        $filter = array('store_id'=>$store_id);
        $count = $obj_log->count($filter);
        $data['log'] = $obj_log->getList('*', $filter, $this->pagelimit*($nPage-1), $this->pagelimit, 'last_modify desc');
        $accountObj = app::get('pam')->model('account');
        foreach($data['log'] as $key=>$val){
            if($val['source'] == '1'){
                $operatorInfo = $accountObj->getList('login_name',array('account_id' => $val['operator']));
                $data['log'][$key]['operator_name'] = $operatorInfo['0']['login_name'];
            }else{
                $data['log'][$key]['operator_name'] = '管理员';
            }
        }
        $data['total'] = $obj_store->getList('earnest',array('store_id'=>$store_id));
        $data['total'] = $data['total'][0]['earnest'];
        $render = $app->render();
        $render->pagedata['store'] = $data;

        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_earnest';
        $this->pagination($nPage,$count,$_GET);
        return $render->fetch('admin/store/manger/page_earnest_list.html');
    }
} 
