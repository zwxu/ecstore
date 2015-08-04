<?php



class b2c_mdl_members extends dbeav_model{
    var $has_tag = true;
    var $defaultOrder = array('regtime','DESC');
    var $has_many = array(
        'contact/other'=>'member_addrs:append',
        'advance/event'=>'member_advance:append:member_id^member_id',
        'score/event'=>'member_point:append',
    );

    var $has_parent = array(
        'pam_account' => 'account@pam'
    );

    var $subSdf = array(
        'default' => array(
            'pam_account:account@pam' => array('*'),
         )
    );

    static private $member_info;
    function __construct($app){
        parent::__construct($app);
        $this->use_meta();  //member中的扩展属性将通过meta系统进行存储
    }

    public function modifier_seller($row)
    {
        return $row ? '企业' : '个人';
    }

    function save(&$sdf,$mustUpdate=null){
        if(isset($sdf['member_id']) && !isset($sdf['pam_account']['account_id'] )){
            $sdf['pam_account']['account_id'] = $sdf['member_id'];
        }
        if(isset($sdf['profile']['gender'])){
            if($sdf['profile']['gender']=='male'){
            $sdf['profile']['gender']=1;
            }elseif($sdf['profile']['gender']=='female'){
               $sdf['profile']['gender']=0;
            }else{
                unset($sdf['profile']['gender']);
            }
        }
        if(isset($sdf['profile']['birthday']) && $sdf['profile']['birthday']){
              $data = explode('-',$sdf['profile']['birthday']);
              $sdf['b_year']=intval($data[0]);$sdf['b_month']=intval($data[1]);$sdf['b_day']=intval($data[2]);
            unset($sdf['profile']['birthday']);
        }
        $sdf['contact']['addr'] = htmlspecialchars($sdf['contact']['addr']);
        $info_object = kernel::service('sensitive_information');
        if(is_object($info_object)) $info_object->opinfo($sdf,'b2c_mdl_members',__FUNCTION__);
        parent::save($sdf);
        #$this->save_member_info_kv($sdf['member_id']);
        return true;
    }


    function dump($filter,$field = '*',$subSdf = null){
        if($ret = parent::dump($filter,$field,$subSdf)){
            $ret['profile']['birthday'] = $ret['b_year'].'-'.$ret['b_month'].'-'.$ret['b_day'];
            if($ret['profile']['gender']== 1){
                $ret['profile']['gender'] = 'male';
            }
            elseif($ret['profile']['gender']== 0){
                $ret['profile']['gender'] = 'female';
            }
            else{
                $ret['profile']['gender'] = 'no';
            }
        }
        return $ret;
    }

    function edit($nMemberId,$aMemInfo){
        $sdf=$this->dump($nMemberId,'*');
        $sdf['profile']['gender'] = $aMemInfo['gender'];
        $sdf['contact']['name'] = $aMemInfo['name'];
        $sdf['contact']['area'] = $aMemInfo['area'];
        $sdf['contact']['addr'] = $aMemInfo['addr'];
        $sdf['contact']['zipcode'] = $aMemInfo['zipcode'];
        $sdf['contact']['email'] = $aMemInfo['email'];
        $sdf['contact']['phone']['telephone'] = $aMemInfo['telephone'];
        $sdf['contact']['phone']['mobile'] = $aMemInfo['mobile'];
        $sdf['member_lv']['member_group_id'] = $aMemInfo['member_group_id'];
        $sdf['account']['pw_question'] = $aMemInfo['pw_question'];
        $sdf['account']['pw_answer'] = $aMemInfo['pw_answer'];
        if(is_numeric($aMemInfo['birthday'])){
            $aMemInfo['birthday'] = date('Y-m-d',$aMemInfo['birthday']);
        }
        $sdf['profile']['birthday'] = $aMemInfo['birthday'];

        return $this->save($sdf);

    }

     //密码修改
    function save_security($nMemberId,$aData,&$msg){ 

        $aMem = $this->dump($nMemberId,'*',array(':account@pam'=>array('*')));
        if(!$aMem){
            $msg=app::get('b2c')->_('无效的用户Id');
            return false;
        }
        $member_sdf['member_id'] = $nMemberId;
        //如果密码是空的则进入安全问题修改过程
        if(empty($aData['passwd'])){
            if( !$aData['pw_answer'] || !$aData['pw_question'] ){
                $msg=app::get('b2c')->_('安全问题修改失败！');
                return false;
            }
            $member_sdf = $this->dump($nMemberId,'*');
            $member_sdf['account']['pw_question'] = $aData['pw_question'];
            $member_sdf['account']['pw_answer'] = $aData['pw_answer'];
             $msg=app::get('b2c')->_('安全问题修改成功');
            return $this->save($member_sdf);
        } else{
            $use_pass_data['login_name'] = $aMem['pam_account']['login_name'];
            $use_pass_data['createtime'] = $aMem['pam_account']['createtime'];

            if($aData['passwd'] != $aData['passwd_re']){
                $msg=app::get('b2c')->_('两次输入的密码不一致！');
                return false;
            }

            
            if( strlen($aData['passwd']) < 6 ){
                $msg=app::get('b2c')->_('密码长度不能小于6');
                return false;
            }
          

             if( strlen($aData['passwd']) > 20 ){
                 $msg=app::get('b2c')->_('密码长度不能大于20');
                 return false;
             }

             //同步到ucenter 
            if( $member_object = kernel::service("uc_user_edit")) {
                $aData['member_id'] = $nMemberId;
                if(!$member_object->uc_user_edit_pwd($aData,0)){
                     $msg=app::get('b2c')->_('输入的旧密码与原密码不符,sso密码修改失败！');
                    return false;
                }
            }else{ 
                 if(pam_encrypt::get_encrypted_password(trim($aData['old_passwd']),pam_account::get_account_type($this->app->app_id),$use_pass_data)!= $aMem['pam_account']['login_password']){
                    $msg=app::get('b2c')->_('输入的旧密码与原密码不符！');
                    return false;
                }
            }
            //同步到ucenter yindingsheng

             $aMem['pam_account']['login_password'] = pam_encrypt::get_encrypted_password(trim($aData['passwd']),pam_account::get_account_type($this->app->app_id),$use_pass_data);
             $aMem['pam_account']['account_id'] = $nMemberId;
             if($this->save($aMem)){
                $aData = array_merge($aMem,$aData);
                $data['email'] = $aMem['contact']['email'];
                $data['uname'] = $aMem['pam_account']['login_name'];
                $data['passwd'] = $aData['passwd_re'];
                $obj_account=&$this->app->model('member_account');
                 $obj_account->fireEvent('chgpass',$data,$nMemberId);
                $msg = app::get('b2c')->_("密码修改成功");
                 return true;
             }else{
                $msg=app::get('b2c')->_('密码修改失败！');
                return false;
             }
         }
     }

    function getMemberByUser($uname)    {
        if($ret = $this->getList('*',array('pam_account'=>array('login_name'=>$uname)) )){
            return $ret[0];
        }
        return false;
     }

     /*根据查询字符串返回UNMAE 数组
     */
     function getUserNameLikeStr($str,$dataType='json'){

         if(!$str||$str !=''){
             $str = $this->db->quote($str);
            $sql  = 'select uname from '.$this->tableName.' where uname like "'.$str.'%" and disabled=false';
         }else if($str == '_ALL_'){
            $sql  = 'select uname from '.$this->tableName.' where disabled=false';
         }
         $data = $this->db->select($sql);

         if($dataType!='json')return $data;

         return json_decode($data,true);

     }


     function getMemberAddr($nMemberId){
            $objMemberAddr = $this->app->model('member_addrs');
            return $objMemberAddr->getList('*',array('member_id'=>$nMemberId));
     }

     function getAddrById($nAddrId){
            $objMemberAddr = $this->app->model('member_addrs');
            return $objMemberAddr->dump($nAddrId);

     }

      function isAllowAddr($nMemberId){
         $objMemberAddr = $this->app->model('member_addrs');
         $aAddr = $objMemberAddr->getList('addr_id',array('member_id'=>$nMemberId));
         if(count($aAddr) < $objMemberAddr->addrLimit){
            return true;
        }else{
            return false;
        }
    }

     //插入收货人地址
    function insertRec($aData,$nMemberId,&$message){
        foreach ($aData as $key=>$val){
            if(is_string($val))
            $aData[$key] = trim($val);
            if(empty($aData[$key])){
                switch ($key){
                case 'name':
                    $message = app::get('b2c')->_('姓名不能为空！');
                    return false;
                    break;
                case 'zipcode':
                    $message = app::get('b2c')->_('邮编不能为空！');
                    return false;
                    break;
                case 'area':
                    $message = app::get('b2c')->_('地区不能为空！');
                    return false;
                    break;
                default:
                    break;
                }
            }
        }
        if(!is_numeric($aData['zipcode'])||strpos($aData['zipcode'],".")!==false){
            $message = app::get('b2c')->_("邮政编码必须是数字");
            return false;
        }
        if($aData['phone']['telephone']){//preg_match('/^[0-9a-zA-Z_\\-\x{4e00}-\x{9fa5}]+$/u', $uname)
            if(!preg_match('/^(0\d{2,3}-?)?[23456789]\d{5,7}(-\d{1,5})?$/',$aData['phone']['telephone'])||strpos($aData['phone']['telephone'],".")!==false){
                $message = app::get('b2c')->_("请输入正确的电话号码！");
                return false;
            }
        }
        if($aData['phone']['telephone'] == '' && $aData['phone']['mobile'] == ''){
            $message = app::get('b2c')->_('联系电话和手机不能都为空！');
            return false;
        }

        $aData['member_id'] = $nMemberId;
        $at = explode(':',$aData['area']);
        $area['area_type'] = $at[0];
        $area['sar'] = explode('/',$at[1]);
        $area['id'] = $at[2];
        $aData['area'] = $area;

        $objMemberAddr = $this->app->model('member_addrs');
        if($objMemberAddr->is_exists_addr($aData,$nMemberId)){
            $message = app::get('b2c')->_('收货地址重复');
            return false;
        }
        $tmp = $aData;
        if($objMemberAddr->save($aData)){
            //同步到b2c
            if($obj = kernel::service('syn_member_addr_add')){
                $obj->member_addr_add($tmp);
            }

            $message = app::get('b2c')->_('保存成功！');
            return true;
        }else{
            $message = app::get('b2c')->_('保存失败！');
            return false;
        }
    }

      //设为默认收获地址
    function set_to_def($addrId,$nMemberId,&$message,$disabled){
        $disabled = intval($disabled);
        if($addrId){
           $objMemberAddr = $this->app->model('member_addrs');
           $row = $objMemberAddr->getList('addr_id',array('addr_id' => $addrId));
           if(!$row){
                   $message = app::get('b2c')->_('参数错误！');
                   return false;
               }
           if( ($row = $objMemberAddr->getList('addr_id',array('member_id'=>$nMemberId,'def_addr'=>1))) and $disabled === 2){
                $data['def_addr'] =  0;
                $filter = array('addr_id'=> $row[0]['addr_id']);
                if(!$objMemberAddr->update($data,$filter)){
                    $message = app::get('b2c')->_('设置失败！');
                    return false;
                }
            }
            $data['def_addr'] = $disabled === 2 ? 1 : 0;
            $filter = array('addr_id'=> $addrId);
            if($objMemberAddr->update($data,$filter)){
                return true;
            }else{
               $message = app::get('b2c')->_('设置失败！');
                return false;
            }
        }else{
            $message = app::get('b2c')->_('参数错误！');
            return false;
        }
    }

      //保存修改
    function save_rec($aData,$nMemberId,&$message){

        if($aData['phone']['telephone'] == '' && $aData['phone']['mobile'] == ''){
            $message = app::get('b2c')->_('联系电话和手机不能都为空！');
            return false;
        }
        if(!is_numeric($aData['zipcode'])||strpos($aData['zipcode'],".")!==false){
            $message = app::get('b2c')->_("邮政编码必须是数字");
            return false;
        }
        if($aData['phone']['telephone']){
            if(!preg_match('/^(0\d{2,3}-?)?[23456789]\d{5,7}(-\d{1,5})?$/',$aData['phone']['telephone'])||strpos($aData['phone']['telephone'],".")!==false){
                $message = app::get('b2c')->_("请输入正确的电话号码！");
                return false;
            }
        }
        #print_r($aData);exit;
        $objMemberAddr = $this->app->model('member_addrs');
        if($aData['default'] ){
             $row = $objMemberAddr->getList('addr_id',array('member_id'=>$nMemberId,'def_addr'=>1));
             $defaultAddrId = $row['0']['addr_id'];
             //关闭当前默认地址
             if($defaultAddrId != $aData['addr_id']){
                $addr_sdf['addr_id'] = $defaultAddrId;
                $addr_sdf['default'] = 0;
                $objMemberAddr->save($addr_sdf);
             }
        }
        $at = explode(':',$aData['area']);
        $area['area_type'] = $at[0];
        $area['sar'] = explode('/',$at[1]);
        $area['id'] = $at[2];
        $aData['area'] = $area;
        if($objMemberAddr->is_exists_addr($aData,$nMemberId)){
            $message = app::get('b2c')->_('收货地址重复');
            return false;
        }
         $tmp = $aData;
         if($objMemberAddr->save($aData)){
             //同步到b2c
            if($obj = kernel::service('syn_member_addr_edit')){
                $tmp['member_id'] = $nMemberId;
                $obj->member_addr_edit($tmp);
            }
            return true;
        }else{
            $message = app::get('b2c')->_('保存地址失败');
            return false;
        }
    }
    //删除
    function del_rec($addrId=null,&$message,$member_id=null){
        if($addrId && $member_id){
            $member_addr = &$this->app->model('member_addrs');
             $filter = array('addr_id'=>$addrId,'member_id' => $member_id);
             if($member_addr->delete($filter)){
                  //同步到b2c
                  if($obj = kernel::service('syn_member_addr_delete')){
                       $tmp['member_foreign_id'] = $member_id;
                       $tmp['foreign_id'] = $addrId;
                       $obj->member_addr_delete($tmp);
                  }
                  $meesage = app::get('b2c')->_("删除成功");
                   return true;
             }
             else{
                 $meesage = app::get('b2c')->_("删除失败");
                   return true;
             }

        }else{
            $message = app::get('b2c')->_("参数有误");
             return false;
        }

    }

     public function check_addr($addrId=null,$member_id=null){
         $member_addr = &$this->app->model('member_addrs');
        if(!$addrId && !$member_id) return false;
        $row = $member_addr->getList('addr_id',array('addr_id' => $addrId, 'member_id' => $member_id));
        if($row) return true;
        else return false;
    }

    function checkUname($uname,&$message){
        $uname = trim($uname);
        $len = strlen($uname);
        if($len<3){
            $message = app::get('b2c')->_('用户名过短!');
            return false;
        }elseif($len>20){
            $message = app::get('b2c')->_('用户名过长!');
            return false;
        }elseif(!preg_match('/^[0-9a-zA-Z_\\-\x{4e00}-\x{9fa5}]+$/u', $uname)){
            $message = app::get('b2c')->_('用户名包含非法字符!');
            return false;
        }elseif(preg_match('/^[0-9]+$/', $uname)){
            $message = app::get('b2c')->_('用户名不能全为数字!');
            return false;
        }else{
            $uname = $this->db->quote($uname);
            $row = $this->db->selectrow("select uname from sdb_b2c_members where uname='{$uname}'");
            if($row['uname']){
                $message = app::get('b2c')->_('重复的用户名!');
                return false;
            }else{
                return true;
            }
        }
    }

    function get_id_by_uname($uname){
        $pam_account = app::get('pam')->model('account');
        if($ret = $pam_account->getList('account_id',array('login_name'=>$uname)) ){
            return $ret[0]['account_id'];
        }

        return false;
    }


    function getOrderByMemId($nMemberId=null,$start=0,$limit=10){
        if(!$nMemberId) return array();
        $objOrder = $this->app->model('orders');
        $aOrderList = $objOrder->getList('order_id,status,pay_status,ship_status,total_amount,createtime ',array('member_id'=>$nMemberId ),$start,$limit);
        return $aOrderList;
    }

    function  getRemarkByMemId($nMemberId){
        $row = $this->getList('remark,remark_type',array('member_id'=>$nMemberId ));
        return $row[0];
    }

    function gen_secret_str($member_id){
        $row=app::get('pam')->model('account')->dump($member_id);
        $row['login_name'] = md5($row['login_name']);
        $row['login_password'] = md5($row['login_password'].STORE_KEY);
        return $member_id.'-'.utf8_encode($row['login_name']).'-'.$row['login_password'].'-'.time();
    }

    function create($data){
        $arrDefCurrency = app::get('ectools')->model('currency')->getDefault();
        $data['currency'] = $arrDefCurrency['cur_code'];
        $data['pam_account']['account_type'] = pam_account::get_account_type($this->app->app_id);
        $data['pam_account']['createtime'] = time();
        $data['reg_ip'] = base_request::get_remote_addr();
        $data['regtime'] = time();
        $data['pam_account']['login_name'] = strtolower($data['pam_account']['login_name']);
        $use_pass_data['login_name'] = $data['pam_account']['login_name'];
        $use_pass_data['createtime'] = $data['pam_account']['createtime'];
        $data['pam_account']['login_password'] = pam_encrypt::get_encrypted_password(trim($data['pam_account']['login_password']),pam_account::get_account_type($this->app->app_id),$use_pass_data);
        $this->save($data);
        return $data['member_id'];
    }

    function is_exists($uname){
        //同步到ucenter yindingsheng
        if($uc = kernel::service("uc_user_checkname")){
            $rs = $uc->uc_user_checkname($uname);
            return $rs;
        }else{
            $account_type = pam_account::get_account_type($this->app->app_id);
            $obj_pam_account = new pam_account($account_type);
            return $obj_pam_account->is_exists($uname);
        }
    }

    function is_exists_email($email=null,$member_id=null){
        if(!$email) return true;
        //同步到ucenter yindingsheng
        if($member_id == null && $uc = kernel::service("uc_user_checkemail")){
            $rs = $uc->uc_user_checkemail($email);
            $aEmail = $rs;
        }else{
            $aEmail = $this->getList('member_id',array('email' => $email));
        }
        if($aEmail && !$member_id) return true;
        if($aEmail && ($member_id != $aEmail[0]['member_id'])) return true;
        return false;
    }
    ####修改经验值
    function change_exp($member_id,$experience,&$msg=''){
        $aMem = $this->dump($member_id,'*',array('contact'=>array('*')));
        if(!$aMem) return null;
        if(!is_numeric($experience)||strpos($experience,".")!==false){
            $msg = app::get('b2c')->_("请输入整数值");
            return false;
        }
        if($experience<0){
            if($aMem['experience']<-$experience){
                $msg = app::get('b2c')->_('经验值不足!');
                return false;
            }
        }
        $experience += $aMem['experience'];
        $aMem['experience'] = $experience;
        if($this->app->getConf('site.level_switch')==1){
            $aMem['member_lv']['member_group_id'] = $this->member_lv_chk($aMem['member_lv']['member_group_id'],$experience);
        }
        $aMem['member_id'] = $member_id;
        if($aMem['member_id'] && $this->save($aMem)){
                return true;
        }else{
                $msg = app::get('b2c')->_('保存失败!');
                return false;
         }
        }

     ###根据经验值修改会员等级

    function member_lv_chk($member_lv_id,$experience){
        $current_member_lv_id = $member_lv_id;
        $objmember_lv = $this->app->model('member_lv');
        $objmember_lv->defaultOrder = array('experience', ' ASC');
        $sdf_lv = $objmember_lv->getList('*');
        foreach($sdf_lv as $sdf){
            if($experience>=$sdf['experience']) $member_lv_id = $sdf['member_lv_id'];
        }
        $current_row = $objmember_lv->getList('experience',array('member_lv_id' => $current_member_lv_id));
        $after_row = $objmember_lv->getList('experience',array('member_lv_id' => $member_lv_id));
        if($current_row[0]['experience']>=$after_row[0]['experience'])
        return $current_member_lv_id;
        return $member_lv_id;
    }
    ##进回收站前操作
     function pre_recycle($data){
        $falg = true;
        $obj_pam = app::get('pam')->model('account');
        foreach($data as $val){
            if($val['advance']>0)
            {
                $this->recycle_msg = app::get('b2c')->_('会员存在预存款,不能删除');
                $falg = false;
            break;
            }
        }
        return $falg;
   }

    function pre_restore(&$data,$restore_type='add'){
         if(!$this->is_exists($data['pam_account']['login_name'])){
            $data[$this->schema['idColumn']] = $data['pam_account']['account_id'];
            $data['need_delete'] = true;
            return true;
         }
         else{

             if($restore_type == 'add'){
                    $new_name = $data['pam_account']['login_name'].'_1';
                    while($this->is_exists($new_name)){
                        $new_name = $new_name.'_1';
                    }
                    $data['pam_account']['login_name'] = $new_name;
                    $data['need_delete'] = true;
                 return true;
             }
             if($restore_type == 'none'){
                    $data['need_delete'] = false;
                    return true;
             }
         }
    }

    function title_modifier($id){
        if ($id === 0 || $id == '0'){
            return app::get('b2c')->_('非会员顾客');
        }
        else{
            $obj_member = app::get('pam')->model('account');
            $sdf = $obj_member->dump($id);
            return $sdf['login_name'];
        }

    }

    function _filter($filter,$tableAlias=null,$baseWhere=null){

        foreach(kernel::servicelist('b2c_mdl_members.filter') as $k=>$obj_filter){
            if(method_exists($obj_filter,'extend_filter')){
                $obj_filter->extend_filter($filter);
            }
        }

        if($filter['member_key']){
            $aData = app::get('pam')->model('account')->getList('account_id',array('login_name|has' => $filter['member_key']));
            if($aData){
                foreach($aData as $key=>$val){
                    $member[$key] = $val['account_id'];
                }
                $filter['member_id'] = $member;
            }
            else{
                return 0;
            }
            unset($filter['member_key']);
        }
        $info_object = kernel::service('sensitive_information');
        if(is_object($info_object)) $info_object->opinfo($filter,'b2c_mdl_members',__FUNCTION__);
        $filter = parent::_filter($filter);

        return $filter;
    }

    /**
     * 重写搜索的下拉选项方法
     * @param null
     * @return null
     */
    public function searchOptions(){
        $columns = array();
        foreach($this->_columns() as $k=>$v){
            if(isset($v['searchtype']) && $v['searchtype']){
                if ($k == 'member_id')
                {
                    $columns['member_key'] = $v['label'];
                }
                else
                    $columns[$k] = $v['label'];
            }
        }

        return $columns;
    }

    /**
     * @根据会员ID获取会员等级名称
     * @access public
     * @param $cols 查询字段
     * @param $sLv 会员等级id
     * @return void
     */
    public function get_lv_name($member_id)
    {
        if(empty($member_id) || $member_id < 0){
            return null;
        }
        $row = $this->db->selectrow('SELECT mlv.name FROM sdb_b2c_members AS m
                                                        LEFT JOIN sdb_b2c_member_lv  AS mlv ON m.member_lv_id=mlv.member_lv_id
                                                        WHERE mlv.disabled = \'false\' AND m.member_id = '.intval($member_id));
        return $row['name'];
    }

    /**
     * 重写getList方法
     * @param string column
     * @param array filter
     * @param int offset
     * @param int limit
     * @param string order by
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        $arr_member = parent::getList($cols, $filter, $offset, $limit, $orderType);
        $mem_point = $this->app->model('member_point');

        foreach ($arr_member as $key=>$arr)
        {
            if ($arr['member_id'])
                $arr_member[$key]['point'] = $mem_point->get_total_count($arr['member_id']);
        }
        $info_object = kernel::service('sensitive_information');
        if(is_object($info_object)) $info_object->opinfo($arr_member,'b2c_mdl_members',__FUNCTION__);

        return $arr_member;
    }

    public function title_recycle($sdf)
    {
        if(!$sdf) return ;
        return $sdf['pam_account']['login_name'] ? $sdf['pam_account']['login_name']:'';
    }

    /**
     * 得到当前登陆用户的信息
     * @param null
     * @return array 用户信息
     */
    public function get_current_member()
    {
    /*echo pam_account::get_account_type($this->app->app_id),'<br>';
        if (!$this->app->member_id){
            kernel::single('base_session')->start();
            $this->app->member_id = $_SESSION['account'][pam_account::get_account_type($this->app->app_id)];
        }
        if($this->member_info) return $this->member_info;
        #获取会员基本信息 jiaolei
        return $this->get_member_info( $this->app->member_id );
        */
        //每次都取session中的member_id  huoxh 2013-09-04
        kernel::single('base_session')->start();
        $this->app->member_id = $_SESSION['account'][pam_account::get_account_type($this->app->app_id)];
        if($this->member_info){
           if($this->app->member_id == $this->member_info['member_id']){
                return $this->member_info;
            }
        }
        #获取会员基本信息 jiaolei
        return $this->get_member_info( $this->app->member_id );
        
    }

    //入参 会员id return会员信息
    public function get_member_info( $member_id ) {
        #$member_sdf = $this->dump($member_id,"*",array(':account@pam'=>array('*')));
        $member_sdf = $this->db->selectrow("select p.login_name,m.member_id,m.name,m.sex,m.point,m.experience,m.email,m.member_lv_id,cur,advance,m.seller
        from sdb_b2c_members as m left join sdb_pam_account as p on m.member_id = p.account_id where m.member_id=".intval($member_id));
        $service = kernel::service('pam_account_login_name');
        if(is_object($service)){
            if(method_exists($service,'get_login_name')){
                $member_sdf['pam_account']['login_name'] = $service->get_login_name($member_sdf['pam_account']);
            }
        }
        if( !empty($member_sdf) ) {
            $this->member_info['member_id'] = $member_sdf['member_id'];
            $this->member_info['uname'] =  $member_sdf['login_name'];
            $this->member_info['name'] = $member_sdf['name'];
            $this->member_info['sex'] =  $member_sdf['sex'] == 1 ?'男':'女';
            $this->member_info['point'] = $member_sdf['point'];
            $this->member_info['usage_point'] = $this->member_info['point'];
            $obj_extend_point = kernel::service('b2c.member_extend_point_info');
            if ($obj_extend_point)
            {
                // 当前会员拥有的积分
                $obj_extend_point->get_real_point($this->member_info['member_id'], $this->member_info['point']);
                // 当前会员实际可以使用的积分
                $obj_extend_point->get_usage_point($this->member_info['member_id'], $this->member_info['usage_point']);
            }
            $this->member_info['experience'] = $member_sdf['experience'];
            $this->member_info['email'] = $member_sdf['email'];
            $this->member_info['member_lv'] = $member_sdf['member_lv_id'];
            $this->member_info['member_cur'] = $member_sdf['cur'];
            $this->member_info['advance'] = $member_sdf['advance'];
            #获取会员等级
            $obj_mem_lv = &$this->app->model('member_lv');
            $levels = $obj_mem_lv->getList("name",array("member_lv_id"=>$member_sdf['member_lv_id']));
            if($levels['disabled']=='false'){
                $this->member_info['levelname'] = $levels[0]['name'];
            }
            
            //添加企业用户。
            if($member_sdf['seller']=='seller'){
                 $this->member_info['seller'] ='seller';
            }
        }
        return $this->member_info;
    }

    /**
    *直接调用父类UPDATE方法 完全是为了把会员信息存入KV 
    */
    public function update($data,$filter,$mustUpdate = null)
    {
        return parent::update($data,$filter,$mustUpdate);
        #return $this->save_member_info_kv($filter['member_id']);
    }
    /*会员数据存入KV */
    //function save_member_info_kv($member_id)
    //{
    //    $member_sdf = $this->dump($member_id,"*",array(':account@pam'=>array('login_name')));
    //    if( !empty($member_sdf) ) {
    //        $member_info['member_id'] = $member_id;
    //        $member_info['uname'] =  $member_sdf['pam_account']['login_name'];
    //        $member_info['name'] = $member_sdf['contact']['name'];
    //        $member_info['sex'] =  $member_sdf['profile']['gender'];
    //        $member_info['point'] = $member_sdf['score']['total'];
    //        $member_info['usage_point'] = $this->member_info['point'];
    //        $obj_extend_point = kernel::service('b2c.member_extend_point_info');
    //        if ($obj_extend_point)
    //        {
    //            // 当前会员拥有的积分
    //            $obj_extend_point->get_real_point($member_info['member_id'], $member_info['point']);
    //            // 当前会员实际可以使用的积分
    //            $obj_extend_point->get_usage_point($member_info['member_id'], $member_info['usage_point']);
    //        }
    //        $member_info['experience'] = $member_sdf['experience'];
    //        $member_info['email'] = $member_sdf['contact']['email'];
    //        $member_info['member_lv'] = $member_sdf['member_lv']['member_group_id'];
    //        $member_info['member_cur'] = $member_sdf['currency'];
    //        $member_info['advance'] = $member_sdf['advance'];
    //        #获取会员等级
    //        $obj_mem_lv = &$this->app->model('member_lv');
    //        $levels = $obj_mem_lv->getList('name',array('member_group_id'=>$member_sdf['member_lv']['member_group_id']));
    //        if($levels[0]['disabled']=='false'){
    //            $member_info['levelname'] = $levels[0]['name'];
    //        }
    //    }
    //    return base_kvstore::instance('b2c_member_info')->store('member_info_'.$member_id,$member_info);
    //}
    /*从kvstore里获取会员信息*/
    //function get_member_info_kv($member_id)
    //{
    //    if(base_kvstore::instance('b2c_member_info')->fetch('member_info_'.$member_id, $contents)=== false)
    //        $contents = $this->get_member_info($member_id);
    //    return $contents;
    //}

    /* 会员注册验证项  */
    function validate(&$data, &$msg){
        $flg = 1;

        if($data['reg_type'] == 'email'){
            if(trim($data['contact']['email']) == ''){
                $msg = app::get('b2c')->_('邮箱为空！');
                $flg = 0;
            }else{
                //验证邮箱格式
                if(!preg_match('/^\\w+((-\\w+)|(\\.\\w+))*\\@[A-Za-z0-9]+((\\.|-)[A-Za-z0-9]+)*\\.[A-Za-z0-9]+$/', trim($data['contact']['email']))){
                    $msg = app::get('b2c')->_('邮箱格式错误！');
                    $flg = 0;
                }else{
                    if($this->is_exists_email(trim($data['contact']['email']))){
                        $msg = app::get('b2c')->_('邮箱已被占用！');
                        $flg = 0;
                    }
//                    elseif($this->is_exists(trim($data['contact']['email']))){
//                        $msg = app::get('b2c')->_('邮箱已被占用！');
//                        $flg = 0;
//                    }
                }
            }
        }

        if($data['reg_type'] == 'mobile'){
            if(trim($data['contact']['phone']['mobile']) == ''){
                $msg = app::get('b2c')->_('手机号码为空！');
                $flg = 0;
            }else{
                if(!preg_match('/^(13\d|144|15[012356789]|18[056789])-?\d{8}$/', trim($data['contact']['phone']['mobile']))){
                    $msg = app::get('b2c')->_('手机号码格式错误！');
                    $flg = 0;
                }else{
//                    if($this->is_exists(trim($data['contact']['phone']['mobile']))){
//                        $msg = app::get('b2c')->_('手机号码已被占用！');
//                        $flg = 0;
//                    }else
                    if($this->is_exists_mobile(trim($data['contact']['phone']['mobile']))){
                        $msg = app::get('b2c')->_('手机号码已被占用！');
                        $flg = 0;
                    }
                }
            }
        }

        if($data['reg_type'] == 'username'){
            if(trim($data['pam_account']['login_name']) == ''){
                $msg = app::get('b2c')->_('用户名为空！');
                $flg = 0;
            }else{
                if(strlen(trim($data['pam_account']['login_name'])) < 3 || strlen(trim($data['pam_account']['login_name'])) > 20){
                    $msg = app::get('b2c')->_('用户名长度只能在3-20位字符之间！');
                    $flg = 0;
                }

                if(!preg_match('/^[0-9a-zA-Z_\\-\x{4e00}-\x{9fa5}]+$/u', trim($data['pam_account']['login_name']))){
                    $msg = app::get('b2c')->_('用户名包含非法字符！');
                    $flg = 0;
                }else{
                    if(preg_match('/^[0-9]+$/', trim($data['pam_account']['login_name']))){
                        $msg = app::get('b2c')->_('用户名不能全为数字！');
                        $flg = 0;
                    }

                    if($this->is_exists(trim($data['pam_account']['login_name']))){
                        $msg = app::get('b2c')->_('用户名已被占用！');
                        $flg = 0;
                    }
//                    elseif($this->is_exists_email(trim($data['pam_account']['login_name']))){
//                        $msg = app::get('b2c')->_('用户名已被占用！');
//                        $flg = 0;
//                    }elseif($this->is_exists_mobile(trim($data['pam_account']['login_name']))){
//                        $msg = app::get('b2c')->_('用户名已被占用！');
//                        $flg = 0;
//                    }
                }
            }


            /*
            if(trim($data['contact']['email']) == ''){
                $msg = app::get('b2c')->_('常用邮箱为空！');
                $flg = 0;
            }else{
                //验证邮箱格式 
                if(!preg_match('/^\\w+((-\\w+)|(\\.\\w+))*\\@[A-Za-z0-9]+((\\.|-)[A-Za-z0-9]+)*\\.[A-Za-z0-9]+$/', trim($data['contact']['email']))){
                    $msg = app::get('b2c')->_('邮箱格式错误！');
                    $flg = 0;
                }else{
                    if($this->is_exists_email(trim($data['contact']['email']))){
                        $msg = app::get('b2c')->_('邮箱已被占用！');
                        $flg = 0;
                    }
//                    elseif($this->is_exists(trim($data['contact']['email']))){
//                        $msg = app::get('b2c')->_('邮箱已被占用！');
//                        $flg = 0;
//                    }
                }
            }

            */
        }

        if(trim($data['pam_account']['login_password']) == ''){
            $msg = app::get('b2c')->_('密码为空！');
            $flg = 0;
        }else{
            if(strlen(trim($data['pam_account']['login_password'])) < 6){
                $msg = app::get('b2c')->_('密码长度不能小于6位！');
                $flg = 0;
            }

            if(strlen(trim($data['pam_account']['login_password'])) > 20){
                $msg = app::get('b2c')->_('密码长度不能大于20位！');
                $flg = 0;
            }
        }

        if(trim($data['pam_account']['psw_confirm']) == ''){
            $msg = app::get('b2c')->_('确认密码为空！');
            $flg = 0;
        }else{
            if($data['pam_account']['login_password'] != $data['pam_account']['psw_confirm']){
                $msg = app::get('b2c')->_('确认密码与密码不一致！');
                $flg = 0;
            }
        }

        return $flg;
    }
    

    /* 验证手机号码  */
    function check_mobile($mobile, &$message, $member_id=null){
        $mobile = trim($mobile);

        if(empty($mobile)){
            $message = app::get('b2c')->_('请输入手机号码！');
            return false;
        }elseif(!preg_match("/^(13\d|144|15[012356789]|18[056789])-?\d{8}$/", $mobile)){
            $message = app::get('b2c')->_('手机号码格式错误！');
            return false;
        }else{
//            if($this->is_exists($mobile)){
//                $message = app::get('b2c')->_('该手机号码已被使用，请更换！');
//                return false;
//            }else{
                if($this->is_exists_mobile($mobile, $member_id)){
                    $message = app::get('b2c')->_('该手机号码已被使用，请更换！');
                    return false;
                }else{
                    return true;
                }
//            }
        }
    }
  

    /* 验证手机短信验证码 */
    function check_mobilecode($code, &$msg){
        if(trim($code) == ''){
            $msg = app::get('b2c')->_('短信验证码为空！');
            return false;
        }elseif(trim($code) != trim($_SESSION['MOBILE_CODE'])){
            $msg = app::get('b2c')->_('短信验证码错误！');
            return false;
        }
//        elseif(intval(time()) - intval($_COOKIE['MOBILE_CODE_TIMER']) >= 120){
//            $msg = app::get('b2c')->_('短信验证码失效，请重新获取！');
//            return false;
//        }
        else{
            return true;
        }
    }
  
    /* 通过用户名获取会员信息  */
    function getMemberByUname($uname){
        $account = app::get('pam')->model('account');
        $member = $account->getList('account_id', array('login_name' => $uname));
        if($ret = $this->getList('*', array('member_id' => $member[0]['account_id']))){
            return $ret[0];
        }
        return false;
    }
 

    /* 使用手机号码登录时验证手机号码  */
    function check_login_mobile($mobile, &$message){
        $mobile = trim($mobile);

        if(empty($mobile)){
            $message = app::get('b2c')->_('请输入用户名！');
            return false;
        }elseif(!preg_match("/^(13\d|144|15[012356789]|18[056789])-?\d{8}$/", $mobile)){
            $message = app::get('b2c')->_('手机号码格式错误！');
            return false;
        }else{
            if(!$this->is_exists_mobile($mobile)){
                $message = app::get('b2c')->_('用户名不存在！');
                return false;
            }else{
                return true;
            }
        }
    }
    

    /* 验证手机号码是否存在  */
    function is_exists_mobile($mobile=null, $member_id=null){
        if(!$mobile) return true;

        
        if($member_id == null && $uc = kernel::service("uc_user_checkmobile")){
            $rs = $uc->uc_user_checkmobile($mobile);
            $aMobile = $rs;
        }else{
            $aMobile = $this->getList('member_id', array('mobile' => $mobile));
        }
        if($aMobile && !$member_id) return true;
        if($aMobile && ($member_id != $aMobile[0]['member_id'])) return true;
        return false;
    }
    

    /*
    * @method : getLoginNamePrefix
    * @description : 获取随机生成的用户名的前缀
    * @author : zlj
    * @date : 2013-5-7 16:07:55
    */
    function getLoginNamePrefix(){
        return defined('LOGIN_NAME_PREFIX') ? LOGIN_NAME_PREFIX : 'es_';
    }


    public function ys_sign($member_id, &$errmsg) {
        //$data['uname']   $data['email']

      

        $member_model = &$this->app->model('members');
        
        $member = $member_model->dump($member_id,'*',array(':account@pam'=>array('*')));


       

       

        if ($member) {
            if ($member['ysusercode']) {
                $errmsg =app::get('b2c')->_('该用户已注册！');
                return false;
            } else {
                $obj_store = &app::get('business')->model('storemanger');
                $xtmp = $obj_store -> getList('*', array('account_id' => $member_id), 0, -1);
                if ($xtmp) {
                    if ($xtmp[0]['ysusercode']) {
                        $errmsg =app::get('b2c')->_('该用户已注册企业账号！');
                        return false;
                    }
                }


            }
        }

        $info['LoginName'] = trim($member['pam_account']['login_name']); //必填
        $info['CustType'] = 'C' ; //C：个人；B：企业；分别对应商城的普通个人（消费者）、企业
        $info['CustName'] =$member['contact']['name']? $member['contact']['name']:$member['pam_account']['login_name']; //必填 个人：真实姓名；企业：企业名称
        $info['Question'] =$member['account']['pw_question'] ;//必填
        $info['Answer'] = $member['account']['pw_answer'] ;//必填
        // 小于10位
        $info['Welcome'] = $member['ys_welcome']; //必填

        $info['Mobile'] = $member['contact']['phone']['mobile']; //Email或Mobile 用户名方式注册，通知邮箱或手机，两者必选其一
        $info['Email'] = $member['contact']['email']; //Email或Mobile 用户名方式注册，通知邮箱或手机，两者必选其一
   
        foreach(kernel :: servicelist('ysepay_tools') as $services) {
            if (is_object($services)) {
                if (method_exists($services, 'register')) {
                    $result = $services -> register($info); 
                    if ($result && $result[0] == 'true') {
                        $usrercode = $result[1];
                        $data = array('pam_account'=>array('account_id' =>$member_id),
                            'ysusercode' => $usrercode,
                            );

                        if ($this ->save($data)) {
                          
                            return true;
                        } else{
                            $errmsg = app::get('b2c')->_('保存银盛码失败。');
                             return false;
                        }
                    } else {
                        $errmsg = $result[1];
                        return false;
                    }
                }
            }
        }


       
    }

    /*
    * @method : reg_bind_validate
    * @description : 信任登录后绑定账号验证
    * @author : zlj
    * @date : 2013-6-5 18:13:03
    */
    function reg_bind_validate($data, &$msg){
        $flg = 1;

        if(trim($data['contact']['email']) == ''){
            $msg = app::get('b2c')->_('邮箱为空！');
            $flg = 0;
        }else{
            //验证邮箱格式
            if(!preg_match('/^\\w+((-\\w+)|(\\.\\w+))*\\@[A-Za-z0-9]+((\\.|-)[A-Za-z0-9]+)*\\.[A-Za-z0-9]+$/', trim($data['contact']['email']))){
                $msg = app::get('b2c')->_('邮箱格式错误！');
                $flg = 0;
            }else{
                if($this->is_exists_email(trim($data['contact']['email']))){
                    $msg = app::get('b2c')->_('邮箱已被占用！');
                    $flg = 0;
                }
            }
        }

        if(trim($data['contact']['phone']['mobile']) == ''){
            $msg = app::get('b2c')->_('手机号码为空！');
            $flg = 0;
        }else{
            if(!preg_match('/^(13\d|144|15[012356789]|18[056789])-?\d{8}$/', trim($data['contact']['phone']['mobile']))){
                $msg = app::get('b2c')->_('手机号码格式错误！');
                $flg = 0;
            }else{
                if($this->is_exists_mobile(trim($data['contact']['phone']['mobile']))){
                    $msg = app::get('b2c')->_('手机号码已被占用！');
                    $flg = 0;
                }
            }
        }

        if(trim($data['pam_account']['login_password']) == ''){
            $msg = app::get('b2c')->_('密码为空！');
            $flg = 0;
        }else{
            if(strlen(trim($data['pam_account']['login_password'])) < 6){
                $msg = app::get('b2c')->_('密码长度不能小于6位！');
                $flg = 0;
            }

            if(strlen(trim($data['pam_account']['login_password'])) > 20){
                $msg = app::get('b2c')->_('密码长度不能大于20位！');
                $flg = 0;
            }
        }

        if(trim($data['pam_account']['psw_confirm']) == ''){
            $msg = app::get('b2c')->_('确认密码为空！');
            $flg = 0;
        }else{
            if($data['pam_account']['login_password'] != $data['pam_account']['psw_confirm']){
                $msg = app::get('b2c')->_('确认密码与密码不一致！');
                $flg = 0;
            }
        }

        return $flg;
    }

}
