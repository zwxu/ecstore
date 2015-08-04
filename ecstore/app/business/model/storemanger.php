<?php
class business_mdl_storemanger extends dbeav_model {
    var $has_tag = true;
    var $defaultOrder = array('store_id', ' DESC');
    var $has_many = array('images' => 'image_attach@image:contrast:store_id^target_id^target_type',
        'attach' => 'brand@business:contrast:store_id^store_id',
        'attachmember' => 'storemember@business:contrast:store_id^store_id',
        );
    var $has_one = array();
    var $subSdf = array('default' => array('images' => array('*', array(':image' => array('*')
                    )
                ),
            'attach' => array('*', array(':brand@business' => array('*')
                    )
                ),
            'attachmember' => array('*', array(':members@b2c' => array('*')
                    )
                ),

            ),
        'delete' => array('images' => array('*'),
            'attach' => array('*'),
            'attachmember' => array('*'),
            )
        );

    public function count_finder($filter = null) {
        $row = $this -> db -> select('SELECT count( DISTINCT store_id) as _count FROM ' . $this -> table_name(1) . ' WHERE ' . $this -> _filter($filter));
        return intval($row[0]['_count']);
    }

    public function count_finder_approved($filter = null) {
        // 默认显示已审核店铺
        $filter = array_merge(array('approved' => '1'), $filter);
        $row = $this -> db -> select('SELECT count( DISTINCT store_id) as _count FROM ' . $this -> table_name(1) . ' WHERE ' . $this -> _filter($filter));
        return intval($row[0]['_count']);
    }


    public function count_finder_approve($filter = null) {
        // 默认显示未审核店铺
        $filter = array('approved' => array('0', '2'));
        $row = $this -> db -> select('SELECT count( DISTINCT store_id) as _count FROM ' . $this -> table_name(1) . ' WHERE ' . $this -> _filter($filter));
        return intval($row[0]['_count']);
    }


    public function get_list_approved($cols = '*', $filter = array(), $offset = 0, $limit = -1, $orderType = null) {


        // 默认显示已审核店铺
        $filter = array_merge(array('approved' => '1'), $filter);
        //$orderType = 'store_id desc';
        if($orderType){
            $orderType = 'approve_time desc'.','.$orderType;
        }else{
            $orderType = 'approve_time desc';

        }

        return $this -> get_list_finder($cols, $filter, $offset, $limit, $orderType);
    }

    public function get_list_approve($cols = '*', $filter = array(), $offset = 0, $limit = -1, $orderType = null) {
        // 默认显示未审核店铺

        $filter = array_merge(array('approved' => array('0', '2')), $filter);
        return $this -> get_list_finder($cols, $filter, $offset, $limit, $orderType);
    }

    public function get_list_finder($cols = '*', $filter = array(), $offset = 0, $limit = -1, $orderType = null) {
        $tmp = $this -> getList($cols, $filter, 0, -1, $orderType);

        $cat = &app :: get('b2c') -> model('goods_cat');

        $storegrade = &$this -> app -> model('storegrade');

        $storecat = &$this -> app -> model('storecat');

        foreach($tmp as $key => &$row) {
            if ( $row['certification']) {
                // 认证
                $cert = unserialize($row['certification']);

                if ($cert['uname'] == 'on') {
                    $uname = app :: get('business') -> _('实名认证');
                } else {
                    $uname = '';
                }

                if ($cert['ushop'] == 'on') {
                    $ushop = app :: get('business') -> _('实体认证');
                } else {
                    $ushop = '';
                }
                $row['certification'] = $uname . " " . $ushop;
            }

            $storeregion='';
            // 经营范围 store_region
            if ($row['store_region']) {
                $regionid = explode(",", $row['store_region']);
                foreach($regionid as $key => $value) {
                    if ($value) {
                        $catname = $cat -> getList('cat_name', array('cat_id' => $value));
                        $storeregion .= $catname['0']['cat_name'] . "|";
                    }
                }
                $row['store_region'] = $storeregion;
            }

            // 店铺等级  store_grade
            if( $row['store_grade']){
                $gradename = $storegrade -> getList('grade_name', array('grade_id' => $row['store_grade']));
                $row['store_grade'] = $gradename['0']['grade_name'];
            }
            // 所属分类  store_cat
            if($row['store_cat']){
                $storecatname = $storecat -> getList('cat_name', array('cat_id' => $row['store_cat']));
                $row['store_cat'] = $storecatname['0']['cat_name'];
            }

            if( $row['last_time']){
                $row['last_time'] = date('Y-m-d', $row['last_time']);
            }


            if( $row['approve_time']){
                $row['approve_time'] = date('Y-m-d H:i:s', $row['approve_time']);
            }

            if( $row['approved_time']){
                $row['approved_time'] = date('Y-m-d H:i:s', $row['approved_time']);
            }

            if( $row['apply_time']){
                $row['apply_time'] = date('Y-m-d H:i:s', $row['apply_time']);
            }

        }

        if ($limit < 0) {
            return $tmp;
        } else {
            return array_slice($tmp, $offset, $limit);
        }
    }

    function getBatchEditInfo($filter) {
        $r = $this -> db -> selectrow('select count( store_id ) as count from sdb_business_storemanger where ' . $this -> _filter($filter));
        return $r;
    }

    function getmemberidbyloginname($loginname) {
        $r = $this -> db -> selectrow("SELECT sdb_pam_account.account_id,sdb_b2c_members.seller FROM sdb_pam_account LEFT JOIN sdb_b2c_members ON sdb_pam_account.account_id= sdb_b2c_members.member_id WHERE  sdb_pam_account.login_name ='" . $loginname . "'");
        if ($r['account_id']) {
            if($r['seller'] !='seller'){
               return array('result' => 'false', 'msg' => __('此用户不是企业账户!'), 'account_id' => $r['account_id']);
            }
            if ($this -> check_acountid($r['account_id'], $message)) {
                return array('result' => 'true', 'msg' => $r['account_id']);
            } else {
                return array('result' => 'false', 'msg' => $message, 'account_id' => $r['account_id']);
            }
        }
    }

function check_acountid($account_id, &$message) {
        $account_id = trim($account_id);

        $row = $this -> db -> selectrow("SELECT account_id  FROM sdb_business_storemanger
                                                        WHERE  sdb_business_storemanger.account_id ='{$account_id}'
                                                        ");
        if ($row['account_id']) {
            $message = __('此用户已经申请过店铺了!');
            return false;
        } else {
            return true;
        }
    }

    function check_id($account_id, &$message) {
        $idcard = trim($idcard);
        $account_id = trim($account_id);

        $row = $this -> db -> selectrow("SELECT account_id  FROM sdb_business_storemanger
                                                        WHERE  sdb_business_storemanger.account_id ='{$account_id}'
                                                        ");
        if ($row['account_id']) {
            $message = __('您已经申请过店铺了!');
            return false;
        } else {
            return true;
        }
    }

    function check_idcard($idcard, $account_id, &$message) {
        $idcard = trim($idcard);
        $account_id = trim($account_id);

        $row = $this -> db -> selectrow("SELECT account_id  FROM sdb_business_storemanger
                                                        WHERE  sdb_business_storemanger.store_idcard ='{$idcard}'
                                                        ");
        if ($row['account_id'] &&  $account_id != $row['account_id']) {
            $message = __('此身份证已经申请过店铺了!');
            return false;
        } else {
            return true;
        }
    }

    function getgradebyid($store_id) {
        $store_id = trim($store_id);
        $row = $this -> db -> selectrow("SELECT * from sdb_business_storegrade
                                              LEFT JOIN sdb_business_storemanger
                                                     ON sdb_business_storegrade.grade_id =  sdb_business_storemanger.store_grade
                                                 WHERE  sdb_business_storemanger.store_id ='{$store_id}'
                                                        ");
        if ($row) {
            return $row;
        }
    }
    
    function _filter($filter, $tbase = '') {

        // 如果filter条件是直接可以只用的则在条件中增加 str_where参数,直接返回
        if (isset($filter['str_where']) && $filter['str_where']) {
            return $filter['str_where'];
        }
        $store_region=array();
        $cat=kernel::single('desktop_user')->get_user_cat(true);
        if($cat!==false && !empty($cat['topCat'])){
            if(isset($filter['store_region'])){
                if(!is_array($filter['store_region'])){
                   $filter['store_region']=explode(',',trim($filter['store_region'],','));
                }
                foreach($filter['cat_id'] as $key=>$v){
                   if(!in_array($v,$cat['topCat'])){
                      unset($filter['store_region'][$key]);
                   }
               }
               if(empty($filter['store_region'])){
                   $filter['store_region']=$cat['topCat'];
               }
            }else{
                $filter['store_region']=$cat['topCat'];
            }
            foreach($filter['store_region'] as $key=>$v){
              $store_region[]=" sdb_business_storemanger.store_region like '%,".$v.",%'";
            }
            $store_region[]=" sdb_business_storemanger.store_region IS NULL";
            $store_region[]=" sdb_business_storemanger.store_region =''";
            $store_region[]=" sdb_business_storemanger.store_region =',,'";

            unset($filter['store_region']);
        }

          //增加店铺类型筛选
        if($filter['issue_type'])
        {
            $sap=" sdb_business_storemanger.store_grade in (select sdb_business_storegrade.grade_id FROM sdb_business_storegrade WHERE sdb_business_storegrade.issue_type='{$filter['issue_type']}')";
            unset($filter['issue_type']);
        }

        $where =parent :: _filter($filter, $tbase);
        if(!empty($store_region)){
            $where.=" and (".implode(' or ',$store_region).")";
        }

        if($sap)
        {
            $where.=" and (".$sap.")";


        }

        return $where;
    }
    function _getSearchFilter($filter = array()) {
        $str_where = '';
        if (isset($filter['loc']) && !empty($filter['loc'])) {
            if (is_string($filter['loc'])) {
                $str_where = ' and ((`sdb_business_storemanger`.area  LIKE "%' . implode('%") or (`sdb_business_storemanger`.area LIKE "%' , explode(',', $filter['loc'])) . '%"))';
            } else {
                $str_where = ' and ((`sdb_business_storemanger`.area  LIKE "%' . implode('%") or (`sdb_business_storemanger`.area LIKE "%' , $filter['loc']) . '%"))';
            }
            unset($filter['loc']);
        }
        if (!isset($filter['approved'])) {
            $filter['approved'] = '1'; //审核状态 0:'0'=>'待审核', '1'=>'审核通过','2'=>'审核未通过',
        }
        if (!isset($filter['status'])) {
            $filter['status'] = '1'; //状态
        }
        $filter['filter_sql'] = "( {table}last_time is null or {table}last_time >=" . mktime(0, 0, 0, date("m")  , date("d"), date("Y")) . ")";
        if (!isset($filter['disabled'])) {
            $filter['disabled'] = 'false'; //状态
        }
        return $this -> _filter($filter) . $str_where;
    }
    function getList_Search($cols = '*', $filter = array(), $offset = 0, $limit = -1, $orderType = null) {
        $filter['str_where'] = $this -> _getSearchFilter($filter);
        // print_r($filter['str_where']);
        return $this -> getList($cols, $filter, $offset, $limit, $orderType);
    }
   
    function add($member_id, $money, $message, &$errMsg, $payment_id = '', $order_id = '' , $paymenthod = '' , $memo = '', $type = 0, $is_frontend = true) {
        if (!$member_id) {
            $errMsg .= app :: get('b2c') -> _('前台支付保证金账户失败');
            return false;
        }

        if ($money) {
            // 取到已支付金额
            $advance = $this -> get($member_id);
            $total = $advance + $money;
            // $member['advance'] = $total;
            /**
             * $adjmember = &$this->app->model('members');
             * $result = $adjmember->update($member,array('member_id'=>$member_id));
             * $member_advance = $this->get($member_id);
             *
             *
             *
             * $current_shop_advance = $this->get_shop_advance();
             * $shop_advance = $current_shop_advance+$money;
             *
             * $data = array(
             * 'member_id'=>$member_id,
             * 'money'=>$money,
             * 'message'=>$message,
             * 'mtime'=>time(),
             * 'payment_id'=>$payment_id,
             * 'order_id'=>$order_id,
             * 'paymethod'=>$paymenthod,
             * 'memo'=>$memo,
             * 'import_money'=>$money,
             * 'explode_money'=>0,
             * 'member_advance'=>$member_advance,
             * 'shop_advance'=>$shop_advance,
             * );
             */

            $tmp = $this -> getList('*', array('account_id' => $member_id), 0, -1, $orderType);

            if ($tmp) {
                $store_id = $tmp[0]['store_id'];
                $earnest=$tmp[0]['earnest'];
            }



            $data = array('store_id' => $store_id,
                'earnest' => $total,
                //'remark' => $message . '|' . time() . '|' . $payment_id . '|' . $order_id . '|' . $paymenthod . '|' . $memo . '|' . $money,
                );
            // $store =app::get('business')->model('storemanger');
            if ($this -> save($data)) {

                //记录日志：
                $obj_log = app :: get('business') ->model('earnest_log');
                $logdata['store_id'] = $store_id;
                //$logdata['origin_value'] = $earnest;
                //$logdata['change_value'] = $money;
                $logdata['earnest_value']= $money;
                $logdata['last_modify']=time();
                $logdata['reason'] = app :: get('b2c') -> _('前台支付保证金');
                $logdata['remark'] =$message . '|' . time() . '|' . $payment_id . '|' . $order_id . '|' . $paymenthod . '|' . $memo . '|' . $money;
                $logdata['orders']= $order_id ;
                $logdata['type']='earnest' ;
                $logdata['source']='3' ;
                $logdata['operator']=$member_id ;

                $obj_log->save($logdata);



                if (!$type) {
                    $data['member_id'] = $member_id;
                }

                $objAdvance = app :: get('b2c') -> model("member_advance");
                $status = $objAdvance -> deduct($member_id, $money, $message, $errMsg, $payment_id, $order_id , $paymethod , $memo, $is_frontend);

                /**
                 * * 监听预存款变化 *
                 */

                foreach(kernel :: servicelist('member_advance_listener') as $service) {
                    $arr_params = array('member_id' => $member_id,
                        'doadd' => true,
                        'is_frontend' => ($is_frontend) ? true : false,
                        );
                    $service -> listener_advance($arr_params);
                }

                return true;
            } else {
                $errMsg .= app :: get('b2c') -> _('更新保证金失败');
                return false;
            }
        } else {
            $errMsg .= app :: get('b2c') -> _('更新保证金失败');
            return false;
        }
    }

    function get($member_id) {
        // $member = &$this->app->model('members');
        $store = app :: get('business') -> model('storemanger');
        $result = $store -> get_list_finder('*', array('account_id' => $member_id), 0, -1);
        $advance = $result[0]['earnest'];
        return $advance;
    }

    function adj_amount($nMemberId, $aAdvanceInfo, &$errMsg = '', $is_frontend = true) {
        $user = kernel :: single('desktop_user');
        $username = $user -> user_data['account']['login_name'];
        $advance = $aAdvanceInfo['modify_advance'];
        if (!$advance) return ;
        $memo = $aAdvanceInfo['modify_memo'];
        $operator = substr($advance, 0, 1);
        $operand = substr($advance, 0);
        if ($operator == '-' && is_numeric($operand)) {
            $message = $username . app :: get('b2c') -> _('管理员后台扣款');
            return $this -> deduct($nMemberId, - $advance, $message, $errMsg, $payment_id = '', $order_id = '' , $paymethod = '' , $memo, $is_frontend);
        } elseif (is_numeric($operand)) {
            $message = $username . app :: get('b2c') -> _('管理员代充值');
            return $this -> add($nMemberId, $advance, $message, $errMsg, $payment_id = '', $order_id = '' , $paymethod = '' , $memo, $type = 0, $is_frontend);
        }
    }

    public function ys_sign($member_id, &$errmsg) {
        $sto = kernel :: single("business_memberstore", $member_id);

        //解决不能取得当前保存的记录
        $sto ->process($member_id);
        $data = $sto -> storeinfo;

        $tmp = $this -> getList('*', array('account_id' => $member_id), 0, -1, $orderType);

        if ($tmp) {
            $store_id = $tmp[0]['store_id'];
            if ($tmp[0]['ysactived'] > 0) {
                return true;
            }
        }


        /*
        if(!is_array($data)){
            $data = $sto->preparedata($tmp[0]);
        }
        */


        $info['LoginName'] = trim($data['account_loginname']); //必填
        $info['CustType'] = 'B' ; //C：个人；B：企业；分别对应商城的普通个人（消费者）、企业
        $info['CustName'] = trim($data['company_name']); //必填 个人：真实姓名；企业：企业名称
        $info['Question'] = trim($data['pw_question']) ;//必填
        $info['Answer'] = trim($data['pw_answer']) ;//必填

        //默认为 欢迎您。
        // 小于10位
        if($data['ys_welcome']){
          $info['Welcome'] = $data['ys_welcome']; //必填
        }else {
          $info['Welcome'] = app :: get('business') -> _('欢迎您。');
        }

        $info['Legalname'] = $data['company_idname']; //企业必填 法人姓名
        $info['Certifitype'] = '00'; //企业必填：00：身份证；01：护照；02：军官证；03：港澳台居民大陆通行证；
        $info['Certifino'] = $data['company_idcard']; //企业必填  身份证号码
        $info['Buslicense'] = $data['company_no']; //企业必填   营业执照注册号码
        $info['Taxno'] = $data['company_taxno']; //企业必填 税务登记证号
        $info['Companycode'] = $data['company_codename'] ; //企业可空  企业组织机构代码
        $info['Contactman'] = $data['company_cname'] ; //企业必填  企业联系人
        $info['Contactphone'] = $data['company_ctel'] ; //企业必填 企业联系电话


        $info['Mobile'] = $data['mobile']?$data['mobile']:$data['tel']; //Email或Mobile 用户名方式注册，通知邮箱或手机，两者必选其一
        $info['Email'] = $data['email']?$data['email']:$data['zip']; //Email或Mobile 用户名方式注册，通知邮箱或手机，两者必选其一

        //print_r($info);exit;
        foreach(kernel :: servicelist('ysepay_tools') as $services) {
            if (is_object($services)) {
                if (method_exists($services, 'register')) {
                    $sresult = $services -> register($info);
                    if ($sresult && $sresult[0] == 'true') {
                        $usrercode = $sresult[1];
                        $xdata = array('store_id' => $store_id,
                            'ysusercode' => $usrercode,
                            'ysactived' => '1',
                            );

                        if ($this -> save($xdata)) {
                            return true;
                        } else {
                            $errmsg ='保存失败';
                            return false;
                        }

                    } else {

                        //print_r($sresult);exit;
                        $errmsg = $sresult[1];
                        return false;
                    }
                }
            }
        }
    }

    public function ys_actived($store_id = null, &$errmsg) {
        $tmp = $this -> getList('*', array('store_id' => $store_id), 0, -1, $orderType);

        if ($tmp) {
            switch ($tmp[0]['ysactived']) {
                case 0:
                    $errmsg = __("该账号尚未注册。");
                    return false;

                case 2:
                    $errmsg = __("该账号已经激活。");
                    return false;
            }

            if (!$tmp[0]['ysusercode']) {
                $errmsg = __("银盛激活码错误。");
                return false;
            } else {
                $usercode = trim($tmp[0]['ysusercode']);
            }
        }
        $info['usercode'] = $usercode;
        foreach(kernel :: servicelist('ysepay_tools') as $services) {
            if (is_object($services)) {
                if (method_exists($services, 'activate')) {
                    $result = $services -> activate($info);
                    if ($result && $result[0] == 'true') {
                        $usrercode = $result[1];
                        $data = array('store_id' => $store_id,
                            'ysactived' => '2',
                            );

                        if ($this -> save($data)) {
                            return true;
                        }
                    } else {
                        $errmsg = $result[1];
                        return false;
                    }
                    // error_log($result[0].$result[1].$result[2], 3,"d:/1122.log");
                }
            }
        }
    }

   function save(&$data,$mustUpdate = null){
       $obj_members = &app :: get('b2c') -> model('members');

       if($data['pw_question']){
            $member['pw_question']=$data['pw_question'];
            $member['pw_answer']=$data['pw_answer'];
       }

       if($data['tel']){
           $member['mobile']=$data['tel'];
       }

       if($data['zip']){
          $member['email']=$data['zip'];
       }

       if($member){
           if($obj_members ->update($member,array('member_id'=>$data['account_id']),$mustUpdate)){
                 return   parent::save($data,$mustUpdate);
            } else {
                return false;
            }

       } else {

            return   parent::save($data,$mustUpdate);
       }
   }

    
      function change_spece($store_id=0,$value=0,&$msg,$operator,$remark=''){
          if(!is_numeric($value)||strpos($value,".")!==false){
              $msg = app::get('business')->_("请输入整数值");
              return false;
          }
          $value = $value*1024*1024*1024;
          $obj_store = app::get('business')->model('storemanger');
          $obj_log = app::get('business')->model('store_log');
          if(!($store_info = $obj_store->dump($store_id,'*'))){
              return null;
          }
          $log_data = array();
          if(($store_info['store_space']+$value)<0){
              $msg = app::get('b2c')->_("空间扣除超过店铺已有图片空间值");
              return false;
          }
          $log_data['reason'] = app::get('business')->_("管理员改变图片空间值");
          $log_data['store_id'] = $store_id;
          $log_data['origin_value'] = $store_info['store_space'];
          $log_data['change_value'] = $value;
          $log_data['last_modify'] = time();
          $log_data['type'] = 'space';
          $log_data['source'] = '2';
          $log_data['operator'] = $operator;
          if ($obj_log->insert($log_data)){
              $obj_store->update(array('store_space'=>($store_info['store_space']+$value)), array('store_id'=>$store_id));
              $msg = app::get('b2c')->_("修改成功");
              return true;
          }else{
              $msg = app::get('b2c')->_("修改失败");
              return false;
          }
      }

      function change_experience($store_id=0,$value=0,&$msg,$operator,$source='1',$orders=null,$remark=''){
          if(!is_numeric($value)||strpos($value,".")!==false){
              $msg = app::get('business')->_("请输入整数值");
              return false;
          }
          $obj_store = app::get('business')->model('storemanger');
          $obj_log = app::get('business')->model('store_log');
          if(!($store_info = $obj_store->dump($store_id,'*'))){
              return false;
          }
          $log_data = array();
          if($source == '1' && !!$orders){
              $sql = " select o.order_id,o.store_id,o.member_id,i.goods_id,o.createtime from sdb_b2c_orders as o join (select concat(',',group_concat(convert(goods_id,char) separator  ','),',') as goods_id,order_id from sdb_b2c_order_items where order_id='{$orders}' group by order_id) as i  on o.order_id=i.order_id where o.store_id='{$store_id}' and o.member_id='{$operator}' and o.order_id='{$orders}' ";
              $first_day = mktime(0,0,0,date('n'),1,date('Y'));
              foreach((array)$obj_store->db->select($sql) as $items){
                  $first_day = mktime(0,0,0,date('n', intval($items['createtime'])),1,date('Y', intval($items['createtime'])));
                  $log_data['orders'] = $items['order_id'];
                  $log_data['goods'] = $items['goods_id'];
                  $log_data['addtime'] = $items['createtime'];
                  $log_data['reason'] = app::get('business')->_("店铺评分获得经验值");
              }
              $goods_id = array();
              foreach((array)explode(',',(array)$log_data['goods']) as $items){
                  if(!intval($items)) $goods_id[] = intval($items);
              }
              $last_day = strtotime('+1 month', $first_day);
              $sql = " select change_value,goods from sdb_business_store_log where operator='{$operator}' and store_id='{$store_id}' and addtime>={$first_day} and addtime<{$last_day} ";
              $exp_value = 0;
              $goe_value = array();
              foreach((array)$obj_store->db->select($sql) as $items){
                  $exp_value += $items['change_value']+0;
                  if(($exp_value+$value) > 9){
                      $msg = app::get('business')->_("相同买家和卖家之间的计分不超过9分,不累计经验值");
                      return false;
                  }

                  foreach((array)explode(',', $items['goods']) as $g){
                      if($g){
                          $goe_value[$g] += $items['change_value']+0;
                      }
                  }
              }
              $goe_count = 0;
              foreach((array)$goe_value as $itmes){
                  if(($itmes+$value) > 3){
                      $goe_count += 1;
                  }
              }
              if(count($goe_value)>0 && $goe_count == count($goe_value)){
                  $msg = app::get('business')->_("同件商品同一买家的计分不超过3分,不累计经验值");
                  return false;
              }
          }else{
              if(($store_info['experience']+$value)<0){
                  $msg = app::get('b2c')->_("经验扣除超过店铺已有经验值");
                  return false;
              }
              $log_data['reason'] = app::get('business')->_("管理员改变经验值");
          }
          $log_data['store_id'] = $store_id;
          $log_data['origin_value'] = $store_info['experience'];
          $log_data['change_value'] = $value;
          $log_data['last_modify'] = time();
          $log_data['type'] = 'experience';
          $log_data['source'] = $source;
          $log_data['operator'] = $operator;
          $log_data['remark'] = $remark;
          if ($obj_log->insert($log_data)){
              $obj_store->update(array('experience'=>($store_info['experience']+$value)), array('store_id'=>$store_id));
              $msg = app::get('b2c')->_("修改成功");
              $this->change_store_lv($store_id);
              return true;
          }else{
              $msg = app::get('b2c')->_("修改失败");
              return false;
          }
      }

      function change_store_lv($store_id){
          $obj_grade = app::get('business')->model('storegrade');
          $obj_manger = app::get('business')->model('storemanger');
          $sql = "select g.experience as grade,m.experience from {$obj_manger->table_name(1)} as m join {$obj_grade->table_name(1)} as g on g.grade_id=m.store_grade where m.store_id= ".intval($store_id);
          $data = $obj_grade->db->selectrow($sql);
          /*if(intval($data['experience']) < intval($data['grade'])){
              return true;
          }*/
          $sql = "select grade_id from {$obj_grade->table_name(1)} where experience <= ".intval($data['experience'])." order by experience desc";
          $grade_id = $obj_grade->db->selectrow($sql);
          $obj_store = app::get('business')->model('storemanger');
          $obj_store->update(array('store_grade'=>$grade_id['grade_id']), array('store_id'=>$store_id));
      }
     


       function check_brandstore($issue_type, $brandid,$brandname, &$message) {
        $issue_type = trim($issue_type);
        $brandid = trim($brandid);
        $brandname = trim($brandname);
        if($brandid){
           $sql= ' AND sdb_business_brand.brand_id =' . $brandid;
        }elseif($brandname){
           $sql= ' AND sdb_business_brand.sdb_business_brand.brand_name =\'' . $brandid.'\'';
        }

        $exSql= "SELECT sdb_business_storemanger.store_id
                                        from sdb_business_storemanger
                                        LEFT JOIN sdb_business_storegrade ON sdb_business_storemanger.store_grade = sdb_business_storegrade.grade_id
                                        LEFT JOIN sdb_business_brand ON sdb_business_storemanger.store_id = sdb_business_brand.store_id
                                        WHERE  sdb_business_storemanger.approved='1' AND sdb_business_storegrade.issue_type='{$issue_type}'
                                              ".$sql;

        $row = $this -> db -> selectrow($exSql);
        if ($row['store_id']) {
            $message = __('此品牌旗舰店已经通过审核,请重新申请其他品牌!');
            return false;
        } else {
            return true;
        }
    }

    /**
     * fireEvent 触发事件
     *
     * @param mixed $event
     * @access public
     * @return void
     */
    function fireEvent($action , &$object, $member_id=0){
         $trigger = &app::get('b2c')->model('trigger');
         return $trigger->object_fire_event($action,$object, $member_id,$this);
    }


    function change_earnest($store_id=0,$value=0,&$msg,$operator,$source='1',$orders=null,$remark='',$order_id){
        if($store_id != 0){
            $earnest = $this->dump($store_id,'earnest');
            $n_earnest = $earnest['earnest'] + $value;

            $data['earnest'] = $n_earnest;

            $obj_log = app::get('business')->model('earnest_log');
            $log_data['store_id'] = $store_id;
            $log_data['earnest_value'] = $value;
            $log_data['last_modify'] = time();
            $log_data['source'] = $source;
            $log_data['operator'] = $operator;
            $log_data['remark'] = $remark;
            if ($obj_log->insert($log_data)){
                $this->update($data, array('store_id'=>$store_id));
                $msg = app::get('b2c')->_("修改成功");
                return true;
            }else{
                $msg = app::get('b2c')->_("修改失败");
                return false;
            }
        }else{
            $msg = app::get('business')->_("未得到相应店铺！");
            return false;
        }
    }


    public function getcounteridbystoreid($store_id){
        $regionary= $this->getList('store_region',array('store_id'=>$store_id));
        $aryregion= split(',',$regionary[0]['store_region']) ;

        foreach ($aryregion as $key => $value) {
            if($value){
              $str .=' locate(\','.$value.',\',sdb_business_storemanger.store_region) > 0   Or';
            }
        }

        $str =  substr($str,0,strlen($str)-2);

        $filter = array(
            'filter_sql'=>$str,
        );

        $regionary= $this->getList('store_id',$filter );
        foreach ($regionary as $key => $value) {
            if($value['store_id']!=$store_id){
                 $result[$key] =$value['store_id'];
            }


        }

        return  $result;

    }

    //下架店铺所有的商品
    public function marketabledAllgoods($store_id){
        $sql="UPDATE  sdb_b2c_goods SET sdb_b2c_goods.marketable='false' WHERE sdb_b2c_goods.store_id='{$store_id}'";
        return  $this->db->exec($sql);

    }




}
