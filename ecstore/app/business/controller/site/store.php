<?php

class business_ctl_site_store extends business_ctl_site_member {
    var $cup = array(0=>"A",1=>"B");

    public function __construct(&$app) {
        $noverify = array('index', 'storeapplystep1', 'storeapplystep2', 'storeapplystep3', 'idcardcheck');
        $request = kernel::single('base_component_request');
        if(in_array($request->get_act_name(), $noverify)){
            $this->verify = false;
        }
        parent :: __construct($app);
        $this->cur_view = 'store';

        $obj_members = &app :: get('b2c') -> model('members');
        $this -> member = $obj_members -> get_current_member();

        $shopname = app :: get('b2c') -> getConf('system.shopname');
        $this -> header .= '<meta name="robots" content="noindex,noarchive,nofollow" />';
        $this -> _response -> set_header('Cache-Control', 'no-store');
        $this -> title = $this -> app -> _('我是卖家'). '_' . $shopname;
        $this -> keywords = app :: get('business') -> _('我是卖家') . '_' . $shopname;
        $this -> description = app :: get('business') -> _('我是卖家') . '_' . $shopname;

        $this->sto= kernel::single("business_memberstore",$this -> member['member_id']);
    } 

   /*
    public function index() {
        $this -> path[] = array('title' => app :: get('b2c') -> _('会员中心'), 'link' => $this -> gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('申请开店'), 'link' => '#');
        $GLOBALS['runtime']['path'] = $this -> path;
        $this -> page('site/store/index.html', false, 'business');
    } 
    */

    public function storeapplystep1() {

        $this -> path[] = array('title' => app :: get('b2c') -> _('会员中心'), 'link' => $this -> gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('我要开店'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('选择店铺类型'), 'link' => '#');
        $GLOBALS['runtime']['path'] = $this -> path;
        $this -> title = app :: get('business') -> _('商家入驻');
        $member_id = $this -> member['member_id'];

      
        if( $this -> member['seller'] !='seller'){
            $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_storeapply', 'act' => 'index')); 
            $this -> splash('failed', $url, app :: get('business') -> _('您不是企业用户，请先注册企业用户。'), true, 1, false);
            exit;
        }
       
       
        $sto= kernel::single("business_memberstore",$member_id);

         //解决不能取得当前保存的记录
        $sto ->process($member_id);

        if($sto->isshoper !='false'){
            $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeinfo')); 
            // $this->end(false, app::get('business')->_('提交失败！'));
            $this -> splash('failed', $url, app :: get('business') -> _('您已经开过店了。'), true, 1, false);
            exit;
        }

        $this -> getstoregrade(); 
        // $this->set_tmpl_file('shopindex-shopinfo.html');
        $this -> page('site/store/applystep1.html', false, 'business');
    } 

    private function getstoregrade() {
        $mdstoregrade = $this -> app_current -> model('storegrade');

        $stroegrade = $mdstoregrade -> getList('*', array('default_lv'=>'1','disabled'=>'false'), 0, -1, 'd_order  desc');

        $this -> pagedata['sgrades'] = $stroegrade;
    } 

    public function editstore() {
        $this -> path[] = array('title' => app :: get('business') -> _('会员中心'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('查看店铺'), 'link' => '#');
        $GLOBALS['runtime']['path'] = $this -> path;

        $member_id = $this -> member['member_id'];

        $storemanger_model = &$this -> app_current -> model('storemanger');

        $data = $storemanger_model -> getList('*', array('account_id' => $member_id), 0, -1);

        if (!$data) { // 不是店主，获取所在店铺ID。
            //$data['is_shopper'] = 'false';

             $storemember = &app :: get('business') -> model('storemember') -> getmemberstoreinfo($member_id); 
            // 是店员
            if ($storemember) {
               
                $data[0]  =  $storemember[0];

            } else {

                $url = $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeinfo'));

               
                // 是普通企业会员
                 $this->splash('failed',$url , app::get('b2c')->_('您没有相应的权限，请与店主联系！'));

            } 


            
        } else {
            // 是店主，显示本店信息。
            //$data['is_shopper'] = 'true';
        } 

        $shopinfo = $data[0]; 
        // 等级
        $o = $this -> app_current -> model('storegrade');
        $storegrade = array();
        foreach($o -> getList('*', '', 0, -1) as $item) {
            // $storegrade[$item['grade_id'].'/'.$item['grade_name']] = $item['grade_name'];
            $storegrade[$item['grade_id']] = $item['grade_name'];
        }
        
        // 经营范围
        /*
        $obj_storegrade = $this -> app_current -> model('storegrade');
        $stype = $obj_storegrade -> getList('*', array('grade_id' => $shopinfo['store_grade']));

        if ($stype[0]['issue_type'] >= '0') {
            $shopinfo['issue_type'] =$stype[0]['issue_type'];
            $this -> pagedata['storetype'] = array('parent_id' => '0'); 
            if ($shopinfo['store_region']) {
                    switch ($stype[0]['issue_type']) {
                        case '0':
                            unset($shopinfo['store_region']);
                            break;

                        case '2':
                            $shopinfo['store_region'] = str_replace(',', "", $shopinfo['store_region']);
                            break;
                        default:

                            $shopinfo['store_region'] = explode(",", $shopinfo['store_region']);
                            break;
                    } 
              } 
        }
        */

          //分类
        $o = $this -> app_current -> model('storecat');
        $ostorecat= $o -> getList('*', array('cat_id'=>$shopinfo['store_cat']), 0, -1);
        if($ostorecat){
          $shopinfo['store_catname'] = $ostorecat[0]['cat_name'];
        } 
         
        // 经营范围 store_region
       
       
         if( $shopinfo['store_region']){

             $regionid=explode(",", $shopinfo['store_region']);
             $cat =&app :: get('b2c') -> model('goods_cat');
              foreach($regionid as $key=>$value){
                  if($value){
                     $catname = $cat -> getList('cat_name', array('cat_id' => $value));
                     $storeregion .= $catname['0']['cat_name']."|";
                  }

              }
              $shopinfo['store_regionname'] = $storeregion; 
         } 

          // 经营范围 store_region
         if( $shopinfo['store_region']){

             

             $regionid=explode(",", $shopinfo['store_region']);

             foreach($regionid as $key => $val) {
                 if ($val == '') unset($regionid[$key]);
             } 
             sort($regionid);

             if( $shopinfo['issue_type'] == 2){
                 $shopinfo['store_region'] = $regionid[0];

             }else{
                 $shopinfo['store_region'] = $regionid;
             }
             
             
         }
        // print_r( $m  ->getList('*',array('parent_id'=>'0')));exit;

         //注册地址
        $area = $shopinfo['company_area'];
        $aryAre = split('/', $area);
        $stemp['pro']  = substr($aryAre[0],strpos($aryAre[0], ':')+1);
        $stemp['city'] =  $aryAre[1];
        $stemp['district'] = substr($aryAre[2], 0, strpos($aryAre[2], ':'));
        $shopinfo['company_areaname'] =$stemp['pro'].$stemp['city'].$stemp['district'];


        $this -> pagedata['storegrade'] = $storegrade;
        $this -> pagedata['storeregion'] = $storeregion;

        $this -> pagedata['res_url'] = app :: get('business') -> res_url;

        $this -> pagedata['_PAGE_'] = 'editstore.html';

        $this -> pagedata['shopinfo'] = $shopinfo;

        $this -> output();
    } 

    public function storeapplystep2() {
        $this -> path[] = array('title' => app :: get('b2c') -> _('会员中心'), 'link' => $this -> gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('我要开店'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('选择店铺类型'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeapplystep1', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('填写基本资料'), 'link' => '#');

        $GLOBALS['runtime']['path'] = $this -> path;
        $this -> title = app :: get('business') -> _('商家入驻');
        $this -> pagedata['current_url'] = app :: get('business') -> res_url;

        $_getParams = $this -> _request -> get_params(); 
        // 店铺等级

        //添加判断 edit by luf
        if($_getParams[0] == ''){
            $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeapplystep1', 'full' => 1)), app::get('b2c')->_('请选择一个店铺类型！'));
        }

        $shopinfo['store_grade'] = $_getParams[0];
        $obj_storegrade = &app :: get('business') -> model('storegrade');
        $storegrade =  $obj_storegrade -> getList('*', array('grade_id' =>  $shopinfo['store_grade'],'disabled'=>'false','default_lv'=>'1'));
        if($storegrade){

        }else{
          //$this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeapplystep1', 'full' => 1)), app::get('b2c')->_('您选择的类型有误，请重新选择！'));
        }
        
        $sto= kernel::single("business_memberstore",$this -> member['member_id']);
        //解决不能取得当前保存的记录
        $sto ->process($this -> member['member_id']);

        if($sto->isshoper == 'true'){
          $storemanger_model = &$this -> app_current -> model('storemanger');

          $shopinfo =  $storemanger_model -> getList('*', array('store_id' => $sto -> storeinfo['store_id']), 0, -1);
          $shopinfo = $shopinfo[0];

          if($shopinfo['approved'] !=0){
             $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeinfo', 'full' => 1)), app::get('b2c')->_('您申请的店铺了正在审核或已经通过审核，请与管理中心联系！'));
          }
        }

        if($sto->isshopmember == 'true'){
          $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeinfo', 'full' => 1)), app::get('b2c')->_('您已是店员，请与所在店主联系！'));
        }
       

        //密保
        $obj_members = &app :: get('b2c') -> model('members');
        $member =  $obj_members -> getList('*', array('member_id' => $this -> member['member_id']));

        if($member){
             $shopinfo['pw_question']=$member[0]['pw_question'];
             $shopinfo['pw_answer']=$member[0]['pw_answer'];
             //店主手机，邮箱
             $shopinfo['tel']=$member[0]['mobile'];
             $shopinfo['zip']=$member[0]['email'];

        }

         
        
        $obj_storegrade = $this -> app_current -> model('storegrade');
        $stype = $obj_storegrade -> getList('*', array('grade_id' => $shopinfo['store_grade']));

        if ($stype[0]['issue_type'] >= '0') {
            $shopinfo['issue_type'] =$stype[0]['issue_type'];
            $this -> pagedata['storetype'] = array('parent_id' => '0'); 
            /*
            if ($shopinfo['store_region']) {
                    switch ($stype[0]['issue_type']) {
                        case '0':
                            unset($shopinfo['store_region']);
                            break;

                        case '2':
                            $shopinfo['store_region'] = str_replace(',', "", $shopinfo['store_region']);
                            break;
                        default:

                            $shopinfo['store_region'] = explode(",", $shopinfo['store_region']);
                            break;
                    } 
              } 
              */
        }


        // 经营范围 store_region
         if( $shopinfo['store_region']){

             $regionid=explode(",", $shopinfo['store_region']);

             foreach($regionid as $key => $val) {
                 if ($val == '') unset($regionid[$key]);
             } 
             sort($regionid);

             if( $shopinfo['issue_type'] == 2){
                 $shopinfo['store_region'] = $regionid[0];

             }else{
                 $shopinfo['store_region'] = $regionid;
             }
             
             
         }
         
        //品牌
        $brandid=$sto -> storeinfo['store_brand'];//print_r('<pre>');print_r($brandid);print_r('</pre>');exit;
        if($brandid){
            if($brandid[0]['brand_id']){
                $shopinfo['brand_id']= $brandid[0]['brand_id'];
            } else {
                $shopinfo['store_newbrand']= $brandid[0]['brand_name'];
                $shopinfo['store_newbrand_en']= $brandid[0]['brand_keywords'];
            }
        }

         
        //$this -> pagedata['storeregion'] = $storeregion;

        $this -> pagedata['shopinfo'] = $shopinfo;

        //将member_id传入页面
        $this -> pagedata['member_id'] = $this->member['member_id'];

        $this -> page('site/store/applystep2.html', false, 'business');
    } 

    public function storeapplystep3() { 
        $storemanger_model = &$this -> app_current -> model('storemanger');

        //print_r('<pre>'); print_r($_POST['shopinfo']); print_r('</pre>');exit;

        $shopinfo = $this -> check_input($_POST['shopinfo']); 
        //store_region

         // 经营范围
         /*
        $obj_storegrade = $this -> app_current -> model('storegrade');

        $stype = $obj_storegrade -> getList('*', array('grade_id' => $shopinfo['store_grade']));

        if ($stype) {
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

        if($shopinfo['store_region']){
            if (is_array($shopinfo['store_region'])) {
                $shopinfo['store_region'] = ',' . implode(",", $shopinfo['store_region']) . ',';
            } else {
                $shopinfo['store_region'] = ',' . $shopinfo['store_region'] . ',';
            } 
        }
        

        //$shopinfo['account_id'] = $this -> member['member_id'];
        $shopinfo['shop_name'] = $this -> member['uname'];
        $_getParams = $this -> _request -> get_params(); 

        // 是否是编辑
        $isedit = $_getParams[0];
//update by jyq
        if($isedit){
           $shopinfo['account_id'] = $this -> member['member_id'];
        }
        //修改后登录用户于当前申请用户不同时，店主错误 Add by PanF  2014-5-5
        if($shopinfo['account_id'] !=  $this -> member['member_id']){
            if($isedit){
               $this -> splash('failed', '', app :: get('business') -> _('页面信息已经超时，请重新刷新当前页面。'), true, 1, true);
            } else {
               echo json_encode(array('status' => 'failed', 'msg' => app :: get('business') -> _('页面信息已经超时，请重新刷新当前页面。'),'url'=>''));
               exit;
            }
        }/* else{
            $shopinfo['account_id'] = $this -> member['member_id'];
        }*/
//update by jyq
      

       //品牌
       if(empty($shopinfo['store_newbrand'])){
            $b2c_brand = &app :: get('b2c') -> model('brand');
            if (is_array($shopinfo['brand_id'])) {
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
            
       }else{
            //中文名
            $brand['brand_name']=trim($shopinfo['store_newbrand']);
            //英文名
            $brand['brand_keywords']=trim($shopinfo['store_newbrand_en']);
       }
       unset($shopinfo['brand_id']);
       unset($shopinfo['store_newbrand']);
       unset($shopinfo['store_newbrand']);

       //申请时间
       if(empty($shopinfo['apply_time']) && !$isedit){
            $shopinfo['apply_time'] = time();
       }

      
        if ($storemanger_model -> save($shopinfo)) {
            if ($isedit) {
                $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeapplystep4', 'arg0' => $shopinfo['account_id'], 'arg1' => $isedit));
            } else {
                $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeapplystep4', 'arg0' => $shopinfo['account_id']));
            }


            //保存新申请品牌
            if($brand['brand_name']){

                //删除当前Store使用的品牌
                $obj_recycle = kernel::single('desktop_system_recycle');
                $filter = array(
                    'store_id'=> $shopinfo['store_id'],
                );

                if ($obj_recycle->dorecycle('business_mdl_brand', $filter))
                {
                     $obj_brand = app::get('business')->model('brand');
                    
                      //store_id
                     $brand['store_id'] = $shopinfo['store_id'];
                     $resb = $obj_brand-> getList('*', $brand, 0, -1);
                     if($resb){
                        $brand=$resb[0];
                     }
                     if($obj_brand->save($brand)){
                         $brandmsg= app::get('business')->_(' 品牌申请成功，请等待管理员审核！');
                      }else{
                         $brandmsg= app::get('business')->_(' 品牌申请失败！');
                      } 
                 } else {
                    $brandmsg= app::get('business')->_('品牌申请失败！请重试');
                 }
                  
            }

            //$data = $storemanger_model -> db->select('select * from sdb_business_storemanger');
            //print_r($data);
            $sto= kernel::single("business_memberstore",$shopinfo['account_id']);
            //解决不能取得当前保存的记录
            $sto ->process($shopinfo['account_id']);
			
			if(!is_array($sto->storeinfo)){
			    $splashmsg=app::get('business')->_('开店申请提交成功');
			}
            //发送申请成功的短信邮件或消息
            if(! $isedit){
                $aData['uname']=$sto->storeinfo['store_idcardname'].'('.$sto->storeinfo['account_loginname'].')'; 
                try{
                    $storemanger_model->fireEvent('register', $aData, $sto->storeinfo['account_id']);
                }catch(Exception $e){
                    echo json_encode(array('status' => 'failed', 'msg' => $e->message(),'url'=>''));
                }
                
            }

            if($splashmsg){
                if($isedit){
                      $this -> splash('success', $url, $splashmsg.$brandmsg, true, 1, true);
                }else {
                    echo json_encode(array('status' => 'success', 'msg' => $splashmsg.$brandmsg,'url'=>$url));
                }
            } else {
                 if($isedit){
                     $this -> splash('success', $url, app :: get('business') -> _('编辑提交成功。').$brandmsg, true, 1, true);
                 }else {
                     echo json_encode(array('status' => 'success', 'msg' => app :: get('business') -> _('开店申请提交成功。').$brandmsg,'url'=>$url));
                 }
            }
        } else {
            // $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeapplystep2')); 
            // $this->end(false, app::get('business')->_('提交失败！'));
            if($isedit){
               $this -> splash('failed', '', app :: get('business') -> _('提交失败'), true, 1, true);
            } else {
               echo json_encode(array('status' => 'failed', 'msg' => app :: get('business') -> _('提交失败'),'url'=>''));
            }

        } 
    } 

    public function storeapplystep4() {
        $this -> path[] = array('title' => app :: get('b2c') -> _('会员中心'), 'link' => $this -> gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('我要开店'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('选择店铺类型'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeapplystep1', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('填写基本资料'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeapplystep2', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('上传证件'), 'link' => '#');
        $GLOBALS['runtime']['path'] = $this -> path;
         $this -> title = app :: get('business') -> _('商家入驻');
        $this -> pagedata['current_url'] = app :: get('business') -> res_url;
        $storemanger_model = &$this -> app_current -> model('storemanger');
        $_getParams = $this -> _request -> get_params(); 
        // 直接点第二步
        if (empty($_getParams)) {
            $accountid = $this -> member['member_id'];
            $shopinfo = $storemanger_model -> getList('*', array('account_id' => $accountid), 0, -1);

            if (empty($shopinfo)) {
                $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeapplystep1'));
                $this -> splash('failed', $url, app :: get('business') -> _('您还没有开店，请先去申请。'), 'redirect');
                exit;
            } 

            if($shopinfo['approved'] !=0){
              $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeinfo', 'full' => 1)), app::get('b2c')->_('您申请的店铺了正在审核或已经通过审核，请与管理中心联系！'));
            }

        } else {
            // 店主ID
            $accountid = $_getParams[0];

            if ($_getParams[1]) {
                $isedit = true;
            } 

            if ((!$accountid) or $accountid <> $this -> member['member_id']) {
                $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'editstore'));
                $this -> splash('failed', $url, app :: get('business') -> _('提交失败，请去店铺管理中心继续设置。'), 'redirect');
                exit;
            } 
        } 

        $shopinfo = $storemanger_model -> getList('*', array('account_id' => $accountid), 0, -1);

          // 经营范围
        $obj_storegrade = $this -> app_current -> model('storegrade');

        $stype = $obj_storegrade -> getList('*', array('grade_id' =>$shopinfo[0]['store_grade']));

        if ($stype) {
             $shopinfo[0]['issue_type'] =$stype[0]['issue_type'];

        }
      
        $this -> pagedata['shopinfo'] = $shopinfo[0];

        if ($isedit) {
            $this -> pagedata['_PAGE_'] = 'editstore1.html';
            $this -> output();
        } else {
            $this -> page('site/store/applystep3.html', false, 'business');
        } 
    } 

    public function storeapplyend() {
        $storemanger_model = &$this -> app_current -> model('storemanger');

        $shopinfo = $this -> check_input($_POST['shopinfo']);

        $_getParams = $this -> _request -> get_params();

        $isedit = $_getParams[0];

       
        if ($storemanger_model -> save($shopinfo)) {
            if ($isedit) {
                $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeinfo'));
            } else {
                $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeapplyredirect'));
            } 

           // $this -> splash('success', $url, app :: get('business') -> _('提交成功,等待审核中。。。'), true, 1, true); 
		    echo json_encode(array('status' => 'success', 'msg' => app :: get('business') -> _('提交成功,等待审核中。。。').$brandmsg,'url'=>$url));
            // $this->end(true, app::get('business')->_('提交成功！'),$url,false,true);
        } else {
            $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeapplystep3')); 
            // $this->end(false, app::get('business')->_('提交失败！'));
           // $this -> splash('failed', $url, app :: get('business') -> _('提交失败,请稍后再试。'), true, 1, true);
		   echo json_encode(array('status' => 'success', 'msg' => app :: get('business') -> _('提交失败,请稍后再试。').$brandmsg,'url'=>$url));
        } 
    } 

    public function storeapplyredirect() {
        $this -> path[] = array('title' => app :: get('b2c') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('b2c') -> _('我要开店'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('b2c') -> _('上传证件'), 'link' => '#');
        $GLOBALS['runtime']['path'] = $this -> path;
         $this -> title = app :: get('business') -> _('商家入驻');
        $member_id = $this -> member['member_id'];
        $sto= kernel::single("business_memberstore",$member_id);
         //解决不能取得当前保存的记录
        $sto ->process($member_id);

        if($sto->isshopmember=='true'){
          $issue_typename = $sto->storeinfo[0]['issue_typename'];
        } else {
          $issue_typename = $sto->storeinfo['issue_typename'];
        }


        $this -> pagedata['issue_typename'] = $issue_typename;
        $this -> pagedata['current_url'] = app :: get('business') -> res_url;
        $this -> page('site/store/end.html', false, 'business');
    } 

    function idcardcheck() {
        $storemanger = &$this -> app_current -> model('storemanger');
        $idcard = trim($_POST['idcard']);

        $account_id = $this -> member['member_id'];

        if ($storemanger -> check_idcard($idcard, $account_id, $message)) {
            echo json_encode(array('status' => 'success', 'message' => '<span class="font-green">&nbsp;' . app :: get('b2c') -> _('可以使用') . '</span>')); 
            // echo '<span id="idcard_success" class="fontcolorGreen">&nbsp;'.app::get('business')->_('可以使用').'</span>';
        } else {
            // echo '<span id="idcard_error" class="fontcolorRed">&nbsp;'.$message.'</span>';
            echo json_encode(array('status' => 'false', 'message' => '<span class="font-red">&nbsp;' . $message . '</span>'));
        } 
    } 

    function storeinfo() {
        $this -> path[] = array('title' => app :: get('business') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('查看店铺'), 'link' => '#');
        $GLOBALS['runtime']['path'] = $this -> path;
        $obj_storemember = app :: get('business') -> model('storemember');
        $member_id = $this -> member['member_id']; 
        // 如果是店主，获取店铺
        $storemanger_model = &$this -> app_current -> model('storemanger');
        $data = $storemanger_model -> getList('*', array('account_id' => $member_id), 0, -1);

        if (empty($data)) {
            // 不是店主，获取所在店铺ID。
            $data = $obj_storemember -> getmemberstoreinfo($member_id);
            if(empty($data))
            {
              $data['isshopmember'] = 'false';
              $data['is_shopper'] = 'false';
              $shopinfo = $data;
            } else {
              $data['isshopmember'] = 'true';
              $data['is_shopper'] = 'false';
              $shopinfo = $data[0];
            }
            
        } else {
            // 是店主，显示本店信息。
            $data['isshopmember'] = 'false';
            $data['is_shopper'] = 'true';
            $shopinfo = $data[0];
        } 

        $shopinfo['is_shopper'] = $data['is_shopper']; 
        $shopinfo['isshopmember']= $data['isshopmember']; 
        // 等级
        $o = $this -> app_current -> model('storegrade');
        $storegrade= $o -> getList('*', array('grade_id'=>$shopinfo['store_grade']), 0, -1);
        if($storegrade){
          $shopinfo['store_gradename'] = $storegrade[0]['grade_name'];
          //保证金
          $shopinfo['earnestlimit'] = $storegrade[0]['issue_money'];
        }
        
        //分类
        $o = $this -> app_current -> model('storecat');
        $ostorecat= $o -> getList('*', array('cat_id'=>$shopinfo['store_cat']), 0, -1);
        if($ostorecat){
          $shopinfo['store_catname'] = $ostorecat[0]['cat_name'];
        } 
         
        // 经营范围 store_region
         if( $shopinfo['store_region']){

             $regionid=explode(",", $shopinfo['store_region']);
             $cat =&app :: get('b2c') -> model('goods_cat');
              foreach($regionid as $key=>$value){
                  if($value){
                     $catname = $cat -> getList('cat_name', array('cat_id' => $value));
                     $storeregion .= $catname['0']['cat_name']."|";
                  }

              }
              $shopinfo['store_region'] = $storeregion; 
         } else {

           $shopinfo['store_region'] =  app::get('business')->_('全部类目');

         } 

         //地区

        $area = $shopinfo['area'];
        $aryAre = split('/', $area);
        $stemp['pro']  = substr($aryAre[0],strpos($aryAre[0], ':')+1);
        $stemp['city'] =  $aryAre[1];
        $stemp['district'] = substr($aryAre[2], 0, strpos($aryAre[2], ':'));
        $shopinfo['area'] =$stemp['pro'].$stemp['city'].$stemp['district'];

        //注册地址
        $area = $shopinfo['company_area'];
        $aryAre = split('/', $area);
        $stemp['pro']  = substr($aryAre[0],strpos($aryAre[0], ':')+1);
        $stemp['city'] =  $aryAre[1];
        $stemp['district'] = substr($aryAre[2], 0, strpos($aryAre[2], ':'));
        $shopinfo['company_area'] =$stemp['pro'].$stemp['city'].$stemp['district'];

        //公司联系地址
        $area = $shopinfo['company_carea'];
        $aryAre = split('/', $area);
        $stemp['pro']  = substr($aryAre[0],strpos($aryAre[0], ':')+1);
        $stemp['city'] =  $aryAre[1];
        $stemp['district'] = substr($aryAre[2], 0, strpos($aryAre[2], ':'));
        $shopinfo['company_carea'] =$stemp['pro'].$stemp['city'].$stemp['district'];
      
       // 认证
       $cert = unserialize( $shopinfo['certification']);
   
       if ($cert['uname'] == 'on') {
           $uname = app::get('business')->_('实名认证');
       } else {
           $uname = '';
       } 

       if ($cert['ushop'] == 'on') {
           $ushop = app::get('business')->_('实体认证');
       } else {
           $ushop = '';
       } 

       $shopinfo['certification'] = $uname . " " . $ushop; 


        // print_r( $m  ->getList('*',array('parent_id'=>'0')));exit;
        $this -> pagedata['storegrade'] = $storegrade;
        $this -> pagedata['storeregion'] = $storeregion;

        $this -> pagedata['res_url'] = app :: get('business') -> res_url;

        $this -> pagedata['_PAGE_'] = 'store.html';
        
       
        $shopinfo['store_space'] = !!$shopinfo['store_space']?abs($shopinfo['store_space']):0;
        $shopinfo['store_space'] = floor($shopinfo['store_space']*10/1073741824)/10;
        $shopinfo['store_usedspace'] = !!$shopinfo['store_usedspace']?abs($shopinfo['store_usedspace']):0;
        $shopinfo['store_usedspace'] = floor($shopinfo['store_usedspace']*10/1073741824)/10;
        $shopinfo['space_width'] = floor($shopinfo['store_usedspace']*155/$shopinfo['store_space']);
       

        $this -> pagedata['shopinfo'] = $shopinfo;

        $this -> output();
    } 
    // --------------------------------------店员管理  begin-----------------------------------------
    function storeroles() {
        $this -> path[] = array('title' => app :: get('business') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('角色管理'), 'link' => '#');
        $GLOBALS['runtime']['path'] = $this -> path;

         //获取店铺信息
        $sto= kernel::single("business_memberstore",$this->member['member_id']);
        $sto->process($this->member['member_id']);
      
        if($sto->isshoper !="true" && $sto->isshopmember !="true" ){
          $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeinfo', 'full' => 1)), app::get('b2c')->_('您没有相应的权限，请与店主联系！'));
        }

        if($sto->isshopmember=='true'){
          $store_id = $sto->storeinfo[0]['store_id'];
        } else {
          $store_id = $sto->storeinfo['store_id'];
        }

        //获取当前店铺所有角色
        $objstoreroles = &app :: get('business') -> model('storeroles'); 
        $storeroles =$objstoreroles->getList('*',array('store_id'=> $store_id));
        
        //新建部分数据
        $treedata =$objstoreroles -> get_cpmenu();

        foreach($treedata as $item) {
            
            $this -> pagedata['menus3'][] = $this -> procHTML($item);
        } 

        $this -> pagedata['receiver'] = $storeroles;
        $this -> pagedata['account_id'] =$this->member['member_id'];
        $this -> pagedata['store_id'] =$store_id;
        $this -> output();
    }

     function procHTML(&$tree) {
        $html = '';

        if ($tree['label']) {
            $html .= "<li style='text-align:left;font-weight:bold;font-style:italic;'>" . $tree['label'];
        } 
        foreach($tree['items'] as $k => $t) {
            if ($t['checked']) {
                $html .= "<li style='text-align:left;padding-right:30px;'>
                           <input  class='leaf'  type='checkbox' checked='checked' name='workground[]' value='" . "app=" . $t['app'] . "&ctl=" . $t['ctl'] . "&act=" . $t['link'] . "'>" . $t['label'];
            } else {
                $html .= "<li style='text-align:left;padding-right:30px;'>
                           <input  class='leaf'  type='checkbox' name='workground[]' value='" . "app=" . $t['app'] . "&ctl=" . $t['ctl'] . "&act=" . $t['link'] . "'>" . $t['label'];
            } 
            // $html .= $this->procHTML($t['parent']);
            $html = $html . "</li>";
        } 
        // return $html ? "<ul>".$html."</ul>" : $html;
        return "<ul style='float:left;'>" . $html . "</ul>";
    } 

    function save_storeroles() {
        $roles = &app::get('business')-> model('storeroles');   

        if($_POST['role_id']){
            $_POST['regtime']=time();
            if( $roles -> save($_POST)){
                 echo json_encode(array('status' => 'success', 'msg' => app :: get('business') -> _('操作成功!')));
                 exit;
               }else{
                 echo json_encode(array('status' => 'failed', 'msg' => app :: get('business') -> _('操作失败!'))); 
                 exit;
               }


        } else {
            if ($roles -> validate($_POST, $msg)) {
               $_POST['regtime']=time();
               if( $roles -> save($_POST)){
                   $this->splash('success', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeroles', 'full' => 1)), app::get('b2c')->_('保存成功！'),true,0,true);
               }else{
                   $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeroles', 'full' => 1)), app::get('b2c')->_('保存失败！'),true,0,true);
               }
            } else {

                  $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeroles', 'full' => 1)), $msg,true,0,true);

            } 
        }
    } 




    function modify_storeroles($role_id) { 
        if ((!$role_id)) $this -> splash('failed', 'back', app :: get('business') -> _('参数错误'));

        $obj_roles = &$this -> app_current -> model('storeroles');

        $result=$obj_roles ->getList('*',array('role_id'=>$role_id));
        if($result){
            $this -> pagedata['role_name'] = $result[0]['role_name'];
            $this -> pagedata['role_id'] = $role_id;
            $this -> pagedata['store_id'] = $result[0]['store_id'];
            $this -> pagedata['workground'] = unserialize($result[0]['workground']);
        }else{
           $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeroles', 'full' => 1)),  app::get('b2c')->_('参数错误，请刷新页面再试！'),true,0,true);
        }

        //新建部分数据
        $objstoreroles = &app :: get('business') -> model('storeroles'); 
        $treedata =$objstoreroles -> get_cpmenu(); 
        foreach($treedata as $item) {
            foreach($item['items'] as &$rol){
                $content='app='.$rol['app'].'&ctl='.$rol['ctl'].'&act='.$rol['link'];
                if(in_array($content,$this -> pagedata['workground']) ){
                    $rol['checked']='checked';
                }
            }
           
            $this -> pagedata['menus3'][] = $this -> procHTML($item);
        } 

        $this -> pagedata['_PAGE_'] = 'modifystoreroles.html';

        $this -> output();
    }



    function del_storeroles($role_id) {
        if ((!$role_id)) $this -> splash('failed', 'back', app :: get('business') -> _('参数错误'));

        $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeroles'));

        $obj_storeroles = &$this -> app_current -> model('storeroles');

        if ($obj_storeroles -> del_rec($role_id, $message)) {
            $this -> splash('success', $url, $message, '', '', true);
        } else {
            $this -> splash('failed', $url, $message, '', '', true);
        } 
    } 


    function storemember() {
        $this -> path[] = array('title' => app :: get('business') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('店员管理'), 'link' => '#');
        $GLOBALS['runtime']['path'] = $this -> path;
        $obj_storemember = app :: get('business') -> model('storemember');

        $member_id = $this -> member['member_id']; 
        // 如果是店主，获取店铺
        $data = $obj_storemember -> getshopinfo($member_id); 
        // 不是店主，获取所在店铺ID
        if (!$data) {

            //$data['is_shopper'] = 'false';

             $storemember = &app :: get('business') -> model('storemember') -> getmemberstoreinfo($member_id); 
            // 是店员
            if ($storemember) {
               
                // 不是店主，获取所在店铺ID。
            $data = $obj_storemember -> getshopmember( $storemember[0]['account_id']);  

            } else {
                // 是普通企业会员
                 $this->splash('failed', $this->gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeinfo', 'full' => 1)), app::get('b2c')->_('您没有相应的权限，请与店主联系！'));

            } 


        } else {
            //获取店员信息
            // 不是店主，获取所在店铺ID。
            $xstore_id= $data[0]['store_id'];
            $data = $obj_storemember -> getshopmember($member_id);

        }  
        
         //角色基础数据
        $roles = array('0'=>'请选择');
        $m = $this -> app_current -> model('storeroles');
        if($data[0]['store_id']){
            $xstore_id=$data[0]['store_id'];
            $filter=array('store_id'=>$data[0]['store_id']);
        }else{
            $filter=array('store_id'=>$xstore_id);
        }

        foreach($m -> getList('*',$filter) as $item) {
            $roles[$item['role_id']] = $item['role_name'];
        } 
        $this -> pagedata['roles'] = $roles;
        $this -> pagedata['store_id'] =$xstore_id;
        $this -> pagedata['account_id'] = $member_id;
        $this -> pagedata['receiver'] = $data;

        $this -> output();
    } 

  

    function del_stroemember($store_id, $member_id) {
        if ((!$store_id) or (!$member_id)) $this -> splash('failed', 'back', app :: get('business') -> _('参数错误'));

        $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storemember'));

        $obj_member = &$this -> app_current -> model('storemember');

        if ($obj_member -> del_rec($store_id, $member_id, $message)) {
          
            $host = defined('WEBCALL_HOST')?WEBCALL_HOST:'';
            $objMember = app::get('b2c')->model('members');
            $email = $objMember->getRow('im_webcall',array('member_id'=>intval($member_id)));
            $main = $objMember->db->selectrow('select m.im_webcall from sdb_b2c_members as m join sdb_business_storemanger as s on m.member_id=s.account_id and s.store_id='.intval($store_id));
            if(!$main && !$email){
                $reg_url = "{$host}/deleteAccount.aspx?mainAccount=".urlencode($main['im_webcall'])."&email=".urlencode($email['im_webcall'])."&accountid=B2B2C.szmall.com";
                //初始化
                $ch = curl_init();
                //设置选项，包括URL
                curl_setopt($ch, CURLOPT_URL, $reg_url);
                curl_setopt($ch, CURLOPT_HEADER, false);
                //执行
                $return = curl_exec($ch);
                //释放curl句柄
                curl_close($ch);
                $objMember->db->exec("update sdb_b2c_members set im_webcall='' where member_id=".intval($member_id));
            }
            $this -> splash('success', $url, $message, '', '', true);
        } else {
            $this -> splash('failed', $url, $message, '', '', true);
        } 
    } 

    //角色名是否重复
   function  role_namecheck(){
       //$obj_member = &app :: get('b2c') -> model('members');
        $name = trim($_POST['name']);
        $store_id = trim($_POST['store_id']);


        if (strlen($name) < 3) {
            echo json_encode(array('status' => 'false', 'message' => '<span class="font-green">&nbsp;' . app :: get('b2c') -> _('长度不能小于3') . '</span>'));
            exit;
        } elseif (strlen($name) > 20) {
            echo json_encode(array('status' => 'false', 'message' => '<span class="font-green">&nbsp;' . app :: get('b2c') -> _('角色名过长') . '</span>'));
            exit;
        } 

        if (!preg_match('/^([@\.]|[^\x00-\x2f^\x3a-\x40]){2,20}$/i', $name)) {
            echo json_encode(array('status' => 'false', 'message' => '<span class="font-green">&nbsp;' . app :: get('b2c') -> _('角色名输入有误') . '</span>'));
            exit;
        } else {

          $obj_storeroles=  &app :: get('business') -> model('storeroles');
          if ($obj_storeroles-> is_exists($name, $store_id)) {
                 echo json_encode(array('status' => 'false', 'message' => '<span class="font-green">&nbsp;' . app :: get('b2c') -> _('角色名重复') . '</span>'));
                 exit;
          } else {
             echo json_encode(array('status' => 'success', 'message' => '可以使用！'));
             exit;
          } 
       }
  
    }

    function namecheck() {
        //$obj_member = &app :: get('b2c') -> model('members');
        $name = trim($_POST['name']);

        if (strlen($name) < 3) {
            echo json_encode(array('status' => 'false', 'message' => '<span class="error caution notice-inline">&nbsp;' . app :: get('b2c') -> _('长度不能小于3') . '</span>'));

            exit;
        } elseif (strlen($name) > 20) {
            echo json_encode(array('status' => 'false', 'message' => '<span class="error caution notice-inline">&nbsp;' . app :: get('b2c') -> _('用户名过长') . '</span>'));

            exit;
        } 

        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/u', $name)) {
            echo json_encode(array('status' => 'false', 'message' => '<span class="error caution notice-inline">&nbsp;' . app :: get('b2c') -> _('用户名输入有误') . '</span>'));
            exit;
        } else {

            //$member=$obj_member->getList('*',array('pam_account'=>array('login_name'=>$name)));
            $obj_member = &app :: get('business') -> model('storemember');
            $member= $obj_member->getmemberbyname($name);
            //if (!$obj_member -> is_exists($name)) {
            if (empty($member)) {
                 $b2c_member = &app :: get('b2c') -> model('members');
                if (!$b2c_member -> is_exists($name)){
                    echo json_encode(array('status' => 'noexist', 'message' => '<span class="error caution notice-inline">&nbsp;' . app :: get('b2c') -> _('用户名不存在') . '</span>'));
                    exit;
                }else{
                    echo json_encode(array('status' => 'noseller', 'message' => '非企业账号，不可使用！'));
                    exit;
                }
            } else {
                if($member[0]['seller']=='seller'){

                     $sto= kernel::single("business_memberstore",$member[0]['member_id']);
                     $sto->process($member[0]['member_id']);
                     if($sto->isshoper=='true'){
                         echo json_encode(array('status' => 'false', 'message' => '该用户已经是店主！'));
                         exit;
                     }else if($sto->isshopmember=='true'){
                         echo json_encode(array('status' => 'false', 'message' => '该用户已经是店员！'));
                         exit;
                     }else{
                        echo json_encode(array('status' => 'success', 'message' => '可以使用！'));
                     }
                } else {
                        echo json_encode(array('status' => 'noseller', 'message' => '非企业账号，不可使用！'));
                }
                exit;
            } 
        } 
    } 


    function emailcheck(){
        $obj_member = &app::get('b2c')->model('members');

        if($_POST['email'] != ''){

            if($obj_member->is_exists($_POST['email'])){
                echo '<span class="error caution notice-inline">' . app::get('b2c')->_('该邮箱已被占用') . '</span>';
                exit;
            }

            if($obj_member->is_exists_email($_POST['email'])){
                echo '<span class="error caution notice-inline">' . app::get('b2c')->_('该邮箱已被占用') . '</span>';
                exit;
            }
        }
       
    }


    function insert_rec() {
        $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storemember'));

        $obj_member = &$this -> app_current -> model('storemember');

        $aData = $this -> check_input($_POST);

        if (($member_id = $obj_member -> insertRec($aData, $this -> member['member_id'], $message))) {
            app::get('b2c')->model('members')->db->exec("update sdb_b2c_members set allow_webcall='".((isset($aData['allow_webcall']) && $aData['allow_webcall'] == 'true')?'true':'false')."' where member_id='{$member_id}'"); 
            $this -> splash('success', $url, $message, '', '', true);
        } else {
            $this -> splash('failed', $url, $message, '', '', true);
        } 
    } 

    function modify_stroemember($store_id, $member_id) {
        if ((!$store_id) or (!$member_id)) $this -> splash('failed', 'back', app :: get('business') -> _('参数错误'));

        $obj_member = &$this -> app_current -> model('storemember');

        $this -> pagedata['_PAGE_'] = 'modifymember.html';

        if ($aRet = $obj_member -> getshopmemberbyid($store_id, $member_id)) {
            $this -> pagedata['storemember'] = $aRet[0];
        } else {
            $this -> _response -> set_http_response_code(404);
            $this -> _response -> set_body(app :: get('business') -> _('修改店员不存在！'));
            exit;
        } 

        $this -> output();
    }
    
   function modify_stroeroles($store_id, $member_id) {
        if ((!$store_id) or (!$member_id)) $this -> splash('failed', 'back', app :: get('business') -> _('参数错误'));

        $obj_member = &$this -> app_current -> model('storemember');

        $this -> pagedata['_PAGE_'] = 'modifyroles.html';
         
         //角色基础数据
        $roles = array('0'=>'请选择');
        $m = $this -> app_current -> model('storeroles');
        foreach($m -> getList('*',array('store_id'=>$store_id)) as $item) {
            $roles[$item['role_id']] = $item['role_name'];
        } 
        $this -> pagedata['roles'] = $roles;
        
        if ($aRet = $obj_member->getshopmemberbyid($store_id, $member_id)) {

            $this -> pagedata['storemember'] = $aRet[0];
        } else {
            $this -> _response -> set_http_response_code(404);
            $this -> _response -> set_body(app :: get('business') -> _('修改店员不存在！'));
            exit;
        } 

        $this -> output();
    }

   /*
    function save_security() {
        // $url = $this->gen_url(array('app'=>'business','ctl'=>'site_store','act'=>'storemember'));
        $obj_member = &$this -> app_current -> model('storemember');

        $aData = $this -> check_input($_POST);
        $result = $obj_member -> save_security($aData, $msg);
        if ($result) {
            echo json_encode(array('status' => 'success', 'msg' => app :: get('business') -> _('操作成功')));
            exit; 
            // $this->splash('success',$url,$msg,'','',true);
        } else {
            echo json_encode(array('status' => 'failed', 'msg' => $msg));
            exit; 
            // $this->splash('failed',$url,$msg,'','',true);
        } 
    } 
    */

     function save_roles() {
        // $url = $this->gen_url(array('app'=>'business','ctl'=>'site_store','act'=>'storemember'));
        $obj_member = &$this -> app_current -> model('storemember');

        $aData = $this -> check_input($_POST);
        $result = $obj_member -> save_roles($aData, $msg);
        if ($result) {
            app::get('b2c')->model('members')->db->exec("update sdb_b2c_members set allow_webcall='".((isset($aData['allow_webcall']) && $aData['allow_webcall'] == 'true')?'true':'false')."' where member_id='{$aData['member_id']}'"); 
            echo json_encode(array('status' => 'success', 'msg' => app :: get('business') -> _('操作成功')));
            exit; 
            // $this->splash('success',$url,$msg,'','',true);
        } else {
            echo json_encode(array('status' => 'failed', 'msg' => $msg));
            exit; 
            // $this->splash('failed',$url,$msg,'','',true);
        } 
    } 


    /**
     * 过滤POST来的数据,基于安全考虑,会把POST数组中带HTML标签的字符过滤掉
     */
    function check_input($data) {
        $aData = $this -> arrContentReplace($data);
        return $aData;
    } 

    function arrContentReplace($array) {
        if (is_array($array)) {
            foreach($array as $key => $v) {
                $array[$key] = $this -> arrContentReplace($array[$key]);
            } 
        } else {
            $array = strip_tags($array);
        } 
        return $array;
    } 

    // --------------------------------------店员管理  end-----------------------------------------

    // --------------------------------------店铺优惠券发行  begin-----------------------------------------
    function  storecoupon($nPage=1){
        //设置不读缓存
        $GLOBALS['runtime']['nocache']=microtime();
        $this -> path[] = array('title' => app :: get('business') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('店员管理'), 'link' => '#');
        $GLOBALS['runtime']['path'] = $this -> path;
        
        /* 
        if( $this->sto->isshoper == "false"){
                $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storeinfo'));
                $this -> splash('failed', $url, app :: get('business') -> _('您不是店主，不能管理优惠券。'), 'redirect');
                exit;
        }
        */
        if( $this->sto->isshoper == "false"){
             $storeid = ','. $this->sto->storeinfo[0]['store_id'].','; 
             $store_id= $this->sto->storeinfo[0]['store_id'];
        }else {
             $storeid = ','. $this->sto->storeinfo['store_id'].',';
              $store_id= $this->sto->storeinfo['store_id'];
        }

      

        $mobj_coupon = app :: get('b2c') -> model('coupons');

       

        $filter = array(
            'filter_sql'=>' locate(\''.$storeid.'\',sdb_b2c_coupons.store_id) > 0   OR  TRIM(sdb_b2c_coupons.store_id)=\'\'  ' ,
        );
        
        $pagelimit=10;
        $obj_coupon=$mobj_coupon->getlist('*',  $filter,$pagelimit*($nPage-1),$pagelimit);
        $count = $mobj_coupon->count($filter);

        $obj_coupon['cpns_store_id'] = $store_id;

        $this -> pagedata['_PAGE_'] = 'coupon.html';

         $this -> pagedata['rule']['store_id'] = $store_id;

        $this -> pagedata['coupons'] = $obj_coupon;

        
         //////////////////////////// 会员等级 //////////////////////////////
        $mMemberLevel = &$this->app->model('member_lv');
        $this->pagedata['member_level'] = $mMemberLevel->getList('member_lv_id,name', array(), 0, -1, 'member_lv_id ASC');


         //////////////////////////// 过滤条件模板 //////////////////////////////
        $this->pagedata['promotion_type'] = 'order'; // 促销规则过滤条件模板类型
        $oSOP = kernel::single('b2c_sales_order_process');
        $condi=  $oSOP->getTemplateList();
        //去除用户自定义 方案 2013-07-08 
        unset( $condi['proundefined_promotion_conditions_order_userdefined']);
        $this->pagedata['pt_list'] = $condi;
       

          //////////////////////////// 优惠方案模板 //////////////////////////////
        $oSSP = kernel::single('b2c_sales_solution_process');
        $arry=$oSSP->getTemplateList();
        
        //去除有关赠品 方案 
        unset($arry['goods']['gift_promotion_solutions_gift']);
        unset($arry['order']['gift_promotion_solutions_gift']);

        

        $this->pagedata['stpl_list'] = $arry;

        
        $this->pagedata['pager'] = array(
            'current'=>$nPage,
            'total'=>ceil($count/$pagelimit) ,
            'link' =>$this->gen_url(array('app'=>'business', 'ctl'=>'site_store','act'=>'storecoupon','args'=>array(($tmp = time())))),
            'token'=>$tmp,
         );
        /*
        print_r('<pre>');
        print_r( $arry);
        print_r('</pre>');exit;
        */

        $this -> output();

    }

    //添加保存优惠券
    function addcoupon(){

        $url = $this -> gen_url(array('app' => 'business', 'ctl' => 'site_store', 'act' => 'storecoupon'));
        $this->begin($url);
        $aData = $this->_prepareData($_POST);

        if( $this->sto->isshoper == "false"){
            $comma_separated = ','. $this->sto->storeinfo[0]['store_id'].','; 
        }else {
            $comma_separated  = ','. $this->sto->storeinfo['store_id'].','; 
        }


        $aData['rule']['store_id']=  $comma_separated;
        $aData['coupon']['store_id'] = $comma_separated;
        // 1 => app::get('b2c')->_('店铺发行'),
        $aData['coupon']['issue_type'] ='1';
        
        /*
        print_r("<pre>");
        print_r($aData);
        print_r("</pre>");
        exit;
        */
       

        /////////////////////////////  保存促销规则  ///////////////////////////////
        $aRule = $aData['rule'];
        $mSRO = app::get('b2c')->model('sales_rule_order');
        $mSRO->save($aRule);
        //////////////////////////////  保存优惠劵 ////////////////////////////////
        $aCoupon = $aData['coupon'];
        $aCoupon['rule']['rule_id'] = $aRule['rule_id'];
        $oCoupon =app::get('b2c')->model('coupons');

        $this->end($oCoupon->save($aCoupon),app::get('b2c')->_('操作成功'),$url);


    }


     function _prepareData($aData) {
        $this->_checkData($aData);
        $aResult = array();
        ///////////////////////////////// coupon ///////////////////////////////////
        $aResult['coupon'] = $aData['coupon'];
        if(isset($aResult['coupon']['cpns_prefix'])) { // 修改的时候这个是没有的 编辑的话只显示不提交到这里
            $aResult['coupon']['cpns_prefix'] = $this->cup[$aData['coupon']['cpns_type']].$aData['coupon']['cpns_prefix'];
        } else {
            $arr_coupon_info = app::get('b2c')->model('coupons')->dump($aResult['coupon']['cpns_id']);
            $aResult['coupon']['cpns_prefix'] = $arr_coupon_info['cpns_prefix'];
        }

        if( !$aResult['coupon']['cpns_key'] ) $aResult['coupon']['cpns_key'] =  substr( base64_encode(serialize($aData)), rand(0,10),10 );


        ///////////////////////////////// order rule ///////////////////////////////////
        $aResult['rule'] = $aData['rule'];
        $aResult['rule']['rule_id'] = $aData['coupon']['rule_id'];

        // 启用状态
        $aResult['rule']['status'] = empty($aData['coupon']['cpns_status'])?'false' : 'true'; // 和优惠劵的状态一致
        $aResult['rule']['rule_type'] = 'C';            // 规则类型


        $aResult['rule']['name'] = app::get('b2c')->_("优惠劵规则").'-'.$aData['coupon']['cpns_name']; // 名称
        if( !$aResult['rule']['name'] ) $this->end( false,'优惠劵规则名称不能为空！' );

        // 开始时间&结束时间
        foreach ($aData['_DTIME_'] as $val) {
            $temp['from_time'][] = $val['from_time'];
            $temp['to_time'][] = $val['to_time'];
        }
        $aResult['rule']['from_time'] = strtotime($aData['from_time'].' '. implode(':', $temp['from_time']));
        $aResult['rule']['to_time'] = strtotime($aData['to_time'].' '. implode(':', $temp['to_time']));
        if( $aResult['rule']['to_time']<=$aResult['rule']['from_time'] ) $this->end( false,'结束时间不能小于开始时间！' );

        // 会员等级
        $aResult['rule']['member_lv_ids'] = empty($aData['rule']['member_lv_ids'])? null : implode(',',$aData['rule']['member_lv_ids']);

        // 创建时间 (修改时不处理)
        if(empty($aResult['rule']['rule_id'])) $aResult['rule']['create_time'] = time();

        ////////////////////////////// 过滤规则 //////////////////////////////////
        $aResult['rule']['conditions'] = empty($aData['conditions'])? array('type'=>'b2c_sales_order_aggregator_combine','conditions'=>array()) : $aData['conditions'];
        $aResult['rule']['conditions'] = array(
                                            'type' => 'b2c_sales_order_aggregator_combine',
                                            'aggregator' => 'all',
                                            'value' => 1,
                                            'conditions' => array(
                                                               array( // 0
                                                                     'type' => 'b2c_sales_order_item_coupon',
                                                                     'attribute' => 'coupon',
                                                                     'operator' => '=',
                                                                     'value' => $aResult['coupon']['cpns_prefix']
                                                               ),
                                                               $aResult['rule']['conditions'], // 1 将订单的'conditions'放到这里
                                             )
                                         );

        $aResult['rule']['action_conditions'] = empty($aData['action_conditions'])? array('type'=>'b2c_sales_order_aggregator_item','conditions'=>array()) : $aData['action_conditions'];

        ////////////////////////////// 优惠方案 //////////////////////////////////
        $s_template = $aData['rule']['s_template'];
        if(empty($aData['action_solution'][$s_template]['type']))
        {
            $this->end(false,'优惠方案数据正在加载,保存失败！请重新选择优惠方案');
        }
        $aResult['rule']['action_solution'] = empty($aData['action_solution'])? array() : ($aData['action_solution']);
        if( $aData['rule']['sort_order'] ) $aResult['rule']['sort_order'] = (int)$aData['rule']['sort_order'];

        return $aResult;
    }


     /**
     * 检测数据
     */
    function _checkData($aData) {
        // POST数据为空
        if(empty($aData)) $this->end(false, app::get('b2c')->_('数据错误'));

        // 添加的时候检测是否已存在相同的coupon 这个可以放在第一步的ajax验证中处理...
        $oCoupon = app::get('b2c')->model('coupons');
        if(empty($aData['coupon']['cpns_id'])) {
            if($oCoupon->checkPrefix($this->cup[$aData['coupon']['cpns_type']].$aData['coupon']['cpns_prefix'])){
				$this->end(false, app::get('b2c')->_('优惠劵号码已经存在'));
            }
        }
    }

     function del_storecoupon() {
        $cpns_id = $_POST['cpns_id'];
        if(!$cpns_id){
          echo json_encode(array('status' => 'false', 'message' => '<span class="font-red">&nbsp;' . app :: get('b2c') -> _('参数错误。') . '</span>'));
          return;
        }

        $obj_recycle = kernel::single('desktop_system_recycle');
        $filter = array(
            'cpns_id'=>$cpns_id,
        );
        if (!$obj_recycle->dorecycle('b2c_mdl_coupons', $filter))
        {
            echo json_encode(array('status' => 'false', 'message' => '<span class="font-red">&nbsp;' . app :: get('b2c') -> _('优惠劵删除失败。') . '</span>'));

        }
        else
        {
            echo json_encode(array('status' => 'success', 'message' => '<span class="font-green">&nbsp;' . app :: get('b2c') -> _('优惠劵删除成功。') . '</span>')); 

        }
        
    } 

   public function  edit_storecoupon(){

       $render = $this->app_current->render();

       $aCoupon= $_POST['cpns_id'];

       //if(empty($aCoupon))  $this -> splash('failed', 'back', app :: get('business') -> _('参数错误'));

       $this-> xedit($aCoupon);   
       
       echo    $render->fetch('site/store/coupon/frame.html');
      

    }


       /**
     * 修改coupon
     */
    function xedit($coupon_id) {

       
        //////////////////////////// 优惠劵信息 //////////////////////////////
        $mCoupon = $this -> app_b2c->model('coupons');
        $aCoupon = $mCoupon->dump($coupon_id); 
       
        


        if(empty($aCoupon))  $this -> splash('failed', 'back', app :: get('business') -> _('参数错误'));
        $aCoupon['cpns_prefix'] = substr($aCoupon['cpns_prefix'],1);
        $this->pagedata['coupon'] = $aCoupon;

      

        ////////////////////////// 订单促销规则信息 ///////////////////////////
        $mSRO = $this -> app_b2c->model('sales_rule_order');
        $aRule = $mSRO->dump($aCoupon['rule']['rule_id']);

        $aRule['member_lv_ids'] = empty($aRule['member_lv_ids'])? null :explode(',',$aRule['member_lv_ids']);
        $aRule['conditions'] = empty($aRule['conditions'])? null : $aRule['conditions'];
        $aRule['conditions'] = is_null($aRule['conditions'])? null : $aRule['conditions']['conditions'][1];
        $aRule['action_conditions'] = empty($aRule['conditions'])? null : ($aRule['action_conditions']);
        $aRule['action_solutions'] = empty($aRule['action_solutions'])? null : ($aRule['action_solutions']);
        $this->pagedata['rule'] = $aRule;


        ///////////////////////////// 过滤条件 ///////////////////////////////
        $oSOP = kernel::single('b2c_sales_order_process');
       
        $aRule['conditions']['isfront'] ='true';
        $aRule['action_conditions']['isfront'] ='true';
        $aRule['action_solution'][$aRule['s_template']]['isfront'] ='true';


      
        if($aCoupon['store_id']){
            $aRule['conditions']['store_id'] =$aCoupon['store_id'];
            $aRule['action_conditions']['store_id'] =$aCoupon['store_id'];
            $aRule['action_solution'][$aRule['s_template']]['store_id'] =$aCoupon['store_id'];
        }


        $aHtml = $oSOP->getTemplate($aRule['c_template'],$aRule);
        
        if((empty($aHtml)) || ( is_array($aHtml) && (empty($aHtml['conditions']) || empty($aHtml['action_conditions']))) ) {
            $this->pagedata['multi_conditions'] = false;
            $this->pagedata['conditions'] = "<b align=\"center\">".app::get('b2c')->_("模板生成失败")."</b>";
        }
        if(is_array($aHtml)) {
            $this->pagedata['conditions'] = $aHtml['conditions'];
            $this->pagedata['action_conditions'] = $aHtml['action_conditions'];
            $this->pagedata['multi_conditions'] = true;
        } else {
            $this->pagedata['multi_conditions'] = false;
            $this->pagedata['conditions'] = $aHtml;
        }

       
        ///////////////////////////// 优惠方案 ///////////////////////////////
        $aRule['action_solution'] = empty($aRule['action_solution'])? null : ($aRule['action_solution']);
        $oSSP = kernel::single('b2c_sales_solution_process');
        $this->pagedata['solution_type'] = $oSSP->getType($aRule['action_solution'], $aRule['s_template']);

       
        $this->pagedata['action_solution_name'] = $aRule['s_template'];


        $html = $oSSP->getTemplate($aRule['s_template'],$aRule['action_solution'], $this->pagedata['solution_type']);
        $this->pagedata['action_solution'] = $html;

        //////////////////////////// 会员等级 //////////////////////////////
        $mMemberLevel = &$this -> app_b2c->model('member_lv');
        $this->pagedata['member_level'] = $mMemberLevel->getList('member_lv_id,name', array(), 0, -1, 'member_lv_id ASC');

        //////////////////////////// 过滤条件模板 //////////////////////////////
        $this->pagedata['promotion_type'] = 'order'; // 促销规则过滤条件模板类型
        $oSOP = kernel::single('b2c_sales_order_process');
        $condi= $oSOP->getTemplateList();
         //去除用户自定义 方案 
        unset( $condi['proundefined_promotion_conditions_order_userdefined']);
        $this->pagedata['pt_list'] = $condi;

        //////////////////////////// 优惠方案模板 //////////////////////////////
        $oSSP = kernel::single('b2c_sales_solution_process');

        $arry=$oSSP->getTemplateList();
        
        //去除有关赠品 方案
        unset($arry['goods']['gift_promotion_solutions_gift']);
        unset($arry['order']['gift_promotion_solutions_gift']);

        //去除有关赠品 方案 
        unset($arry['goods']['gift_promotion_solutions_gift']);
        unset($arry['order']['gift_promotion_solutions_gift']);



        $this->pagedata['stpl_list'] = $arry;



       


        
    }

     /*
     * 下载优惠券
     */
    function download_storecoupon($cpnsId,$store_id){
        $nums= $_POST['nCount'];

        if( $nums < 1 ) {
            header("Content-type: text/html; charset=UTF-8");
            echo __('<script>alert("'.app::get('b2c')->_("下载数量错误！").'")</script>');exit;

        }

        $result=array();
        $mCoupon =  $this -> app_b2c->model('coupons');
        $cpnsnum = $mCoupon->getList('cpns_gen_quantity', array('cpns_id' => $cpnsId), 0, -1);
        $storemanger_model = &$this -> app_current -> model('storemanger');
        $gradeinfo =  $storemanger_model->getgradebyid($store_id);

        if($gradeinfo['coupons_num']){
            if( ($cpnsnum[0]['cpns_gen_quantity']+ $nums) >  $gradeinfo['coupons_num'] ) {
                header("Content-type: text/html; charset=UTF-8");
                echo __('<script>alert("'.app::get('b2c')->_("发行数量已经超过可允许发行最大数量,请修改数量后再试！").'")</script>');exit;
            }
        }

      

        if ($list = $mCoupon->downloadCoupon($cpnsId,$nums)) {
            //$exporter->download(app::get('b2c')->_('优惠券代码'),'coupon',$nums, $list);
            $this->toCSV($list);
            
        }else{
            header("Content-type: text/html; charset=UTF-8");
           echo __('<script>alert("'.app::get('b2c')->_("当前优惠券未发布/时间未到,暂时不能下载").'")</script>');
           
        }
       
    }


    function toCSV($aData){
        $charset = kernel::single('base_charset');
        header('Pragma: no-cache, no-store');
        header("Expires: Wed, 26 Feb 1997 08:21:57 GMT");
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=advance_".date("Ymd").".csv");
        $out = app::get('b2c')->_("优惠券代码,时间\n");
        foreach($aData as $v){
            $out .= $v.",".date("Y-m-d H:i",$v['mtime'])."\n";
        }
        echo $charset->utf2local($out,'zh');
        exit;
    }

    //支付保证金
    function earnestdeposit(){
        $this -> path[] = array('title' => app :: get('business') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('支付保证金'), 'link' => '#');
        $GLOBALS['runtime']['path'] = $this->path;
        $oCur = app::get('ectools')->model('currency');
        $currency = $oCur->getDefault();
        $this->pagedata['currencys'] = $currency;
        $this->pagedata['currency'] = $currency['cur_code'];
        $opay = app::get('ectools')->model('payment_cfgs');
        $aOld = $opay->getList('*', array('status' => 'true', 'platform'=>'ispc', 'is_frontend' => true));

        #获取默认的货币
        $obj_currency = app::get('ectools')->model('currency');
        $arr_def_cur = $obj_currency->getDefault();
        $this->pagedata['def_cur_sign'] = $arr_def_cur['cur_sign'];

        $aData = array();
       
        foreach($aOld as $val){
            // if(($val['app_id']!='deposit') && ($val['app_id']!='offline'))$aData[] = $val;
            if(($val['app_id']!='offline'))$aData[] = $val;
        }

        //获取店铺信息
        $sto= kernel::single("business_memberstore",$this->member['member_id']);

        //print_r( $sto->storeinfo['store_gradeinfo']['issue_money']);exit;
        //print_r( $sto->storeinfo['earnest']);exit;

         if( $sto->isshoper == "false"){
            $issue_money = $sto->storeinfo[0]['store_gradeinfo']['issue_money'] ;
            $earnest =$sto->storeinfo[0]['earnest'];
        }else {
            $issue_money  = $sto->storeinfo['store_gradeinfo']['issue_money'];
            $earnest =$sto->storeinfo['earnest'];
        }

        $this->pagedata['issue_money'] =  $issue_money;
        $this->pagedata['earnest'] = $earnest;
        $this->pagedata['total'] = $this->member['advance'];
        $this->pagedata['payments'] = $aData;
        $this->pagedata['member_id'] = $this -> member['member_id'];
        $this->pagedata['return_url'] = $this->gen_url(array('app'=>'business','ctl'=>'site_member','act'=>'index'));
        $this -> pagedata['_PAGE_'] = 'deposit.html';
        $bankInfo = kernel::single('b2c_banks_info')->getBank();
		$this->pagedata['bankinfo'] = $bankInfo;
        $this->output();
    }


   




   
    function awardspace(){
        $this -> path[] = array('title' => app :: get('business') -> _('店铺管理'), 'link' => $this -> gen_url(array('app' => 'business', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title'=>app::get('business')->_('图片空间收费扩容'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $oCur = app::get('ectools')->model('currency');
        $currency = $oCur->getDefault();
        $this->pagedata['currencys'] = $currency;
        $this->pagedata['currency'] = $currency['cur_code'];
        $opay = app::get('ectools')->model('payment_cfgs');
        $aOld = $opay->getList('*', array('status' => 'true', 'platform'=>'ispc', 'is_frontend' => true));

        #获取默认的货币
        $obj_currency = app::get('ectools')->model('currency');
        $arr_def_cur = $obj_currency->getDefault();
        $this->pagedata['def_cur_sign'] = $arr_def_cur['cur_sign'];

        $aData = array();
        foreach($aOld as $val){
            if(($val['app_id']!='deposit') && ($val['app_id']!='offline'))$aData[] = $val;
        }
        $this->pagedata['total'] = $this->member['advance']['total'];
        $this->pagedata['payments'] = $aData;
        $this->pagedata['member_id'] = $this->app->member_id;
        $this->pagedata['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'balance'));

        $this->output();
    }



    function checkbrandstore(){

        $issue_type=trim($_POST['issue_type']);

        if($_POST['brandid']){
            $brandid=trim($_POST['brandid']);
        }

        if($_POST['brandname']){
            $brandname=trim($_POST['brandname']);
            $isnew='true';
        }

        $storemanger = &$this -> app_current -> model('storemanger');

        if ($storemanger -> check_brandstore($issue_type, $brandid,$brandname, $message)) {
            echo json_encode(array('status' => 'success', 'message' => '<span class="font-green">&nbsp;' . app :: get('b2c') -> _('可以使用') . '</span>')); 
            // echo '<span id="idcard_success" class="fontcolorGreen">&nbsp;'.app::get('business')->_('可以使用').'</span>';
        } else {
            // echo '<span id="idcard_error" class="fontcolorRed">&nbsp;'.$message.'</span>';
            echo json_encode(array('status' => 'false', 'message' => '<span class="font-red">&nbsp;' . $message . '</span>','isnew'=>$isnew));
        } 




    }

    function earnest_manger($nPage=1){
        //print_r(1111);exit;
        $obj_store = app::get('business')->model('storemanger');
        $obj_log = app::get('business')->model('earnest_log');

        $member_id = $this -> member['member_id'];

        $sto= kernel::single("business_memberstore",$member_id);
        if( $sto->isshoper == "false"){
            $store_id= $sto->storeinfo[0]['store_id'];
            $issue_money=$sto->storeinfo[0]['store_gradeinfo']['issue_money'];
        }else {
            $store_id= $sto->storeinfo['store_id'];
            $issue_money=$sto->storeinfo['store_gradeinfo']['issue_money'];
        }

        $info = $obj_store->dump($store_id,'earnest');
        
        $filter = array('store_id'=>$store_id);
        $count = $obj_log->count($filter);

        $aPage = $this->get_start($nPage,$count);
        $data['log'] = $obj_log->getList('*', $filter,$aPage['start'],$this->pagesize);
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

        $this->pagedata['store'] = $data;

        $this->pagedata['earnest'] = $info['earnest'];

        $data['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$data['page'],'earnest_manger', '', 'business', 'site_store');
        $this->pagedata['advlogs'] = $data['data'];

        $this->pagedata['issue_money'] = $issue_money ;

        $this->output('business');
    }
} 
