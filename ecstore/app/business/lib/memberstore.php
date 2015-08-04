<?php
/**
 * $sto= kernel::single("business_memberstore",$member_id);
 */
class business_memberstore {
    private $storemanger_model;

    private $storemember_model;

    private $storecat_model;

    private $storegrade_model;

    private $storeregion_model;

    private $storebrand_model; 
    // 是否是店主
    public $isshoper; 
    // 店铺信息
    public $storeinfo; 
    // 是否是店员
    public $isshopmember;

    /**
     * 构造方法
     * 
     * @param int $ member_id
     * @return null 
     */
    public function __construct($member_id) {
        $this -> member_id = $member_id; 
        // 店铺管理
        $this -> storemanger_model = &app :: get('business') -> model('storemanger'); 
        // 店员管理
        $this -> storemember_model = &app :: get('business') -> model('storemember'); 
        // 店铺分类
        $this -> storecat_model = &app :: get('business') -> model('storecat'); 
        // 店铺等级
        $this -> storegrade_model = &app :: get('business') -> model('storegrade'); 
        // 经营范围
        $this -> storeregion_model = &app :: get('b2c') -> model('goods_cat'); 
        // 品牌
        $this -> storebrand_model = &app :: get('business') -> model('brand'); 
        //普通会员
        $this -> b2cmembers = &app :: get('b2c') -> model('members'); 

        // 获取相关信息
        $this -> process($this -> member_id);
    } 

   public function process($member_id) {
       $data = $this -> storemanger_model -> getList('*', array('account_id' => $member_id), 0, -1);
       //$data = $this -> storemanger_model -> db->select('select * from sdb_business_storemanger');
       //print_r($data);
       
        if (!empty($data)) {
            // 是店主，显示本店信息。
            $this -> isshoper = "true"; 
            // 获取本店店员getshopmember
            $storemember = $this -> storemember_model -> getshopmember($member_id); 
            // 店主仅有一条记录
            $this -> storeinfo = $this -> preparedata($data[0]);

            $this -> storeinfo['storemembers'] = $storemember;
        } else {
            // 不是店主，获取所在店铺ID。
            $this -> isshoper = "false";

            $storemember = $this -> storemember_model -> getmemberstoreinfo($member_id); 
            // 是店员
            if ($storemember) {
                $this -> isshopmember = "true"; 
                // 可以为多条
                foreach($storemember as $ck => $cv) {
                    $storemember[$ck] = $this -> preparedata($cv);
                } 
                $this -> storeinfo = $storemember[0];
                //2013-08-12 解决店员所属店铺信息数据结构
                $this -> storeinfo[0]=$storemember[0];
                
            } else {
                // 是普通会员
                $this -> isshopmember = "false";
                $this -> storeinfo =  $this -> b2cmembers ->dump($member_id,'*');
                $this -> storeinfo['Title'] = "您是普通会员";

            } 
        } 
    } 

    function preparedata($data) {

       
        // 店主用户名：
        if ($data['account_id']) {
            $member = $this -> storemember_model -> getshopinfo($data['account_id']);
            $data['account_loginname'] = $member[0]['login_name'];
            $data['account_name'] = $member[0]['name'];
            $data['pw_question'] = $member[0]['pw_question'];
            $data['pw_answer'] = $member[0]['pw_answer'];
            $data['mobile'] = $member[0]['mobile'];
            $data['email'] = $member[0]['email'];
            $data['seller'] = $member[0]['seller'];
            if($member[0]['reg_type'] == 'mobile'){
                $data['mobile'] = $member[0]['login_name'];
            }elseif($member[0]['reg_type'] == 'email'){
                $data['email'] = $member[0]['login_name'];
            }
        } 
        // 店铺分类名：
        if ($data['store_cat']) {
            $storecat = $this -> storecat_model -> getList('*', array('cat_id' => $data['store_cat']), 0, -1);
            $data['store_catname'] = $storecat[0]['cat_name'];
        } 
        // 店铺等级名：
        if ($data['store_grade']) {
            $storegrade = $this -> storegrade_model -> getList('*', array('grade_id' => $data['store_grade']), 0, -1); 
            // $data['store_grade'] =array($data['store_grade'] =>$storegrade[0]['grade_name']);
            $data['store_gradename'] = $storegrade[0]['grade_name'];
            
            $data['issue_type'] = $storegrade[0]['issue_type'];

            switch ($data['issue_type']) {
                case '0':
                    $data['issue_typename'] = app :: get('b2c') -> _('卖场型旗舰店');

                    break;
                case '1':
                    $data['issue_typename'] = app :: get('b2c') -> _('专卖店');
                    break;

                case '2':
                    $data['issue_typename'] = app :: get('b2c') -> _('专营店');
                    break;
                case '3':
                    $data['issue_typename'] = app :: get('b2c') -> _('品牌旗舰店');
                    break;

                default:
                    $data['issue_typename'] = app :: get('b2c') -> _('未知类型');
                    break;
            } 

            $data['store_gradeinfo'] = $storegrade[0];

        } 
        // 店铺经营范围名：
        if ($data['store_region']) {
            $region = explode(",", $data['store_region']);

            foreach($region as $key => $val) {
                if ($val == '') unset($region[$key]);
            } 

            $store_region = array();

            foreach ($region as $i => $value) {
                $storeregion = $this -> storeregion_model -> getList('*', array('cat_id' => $value), 0, -1);
                $store_region[$value] = $storeregion[0]['cat_name'];
            } 

            $data['store_region'] = $store_region;
        } 
        // 获取店铺品牌。
        //$storeBrand = $this -> storebrand_model -> getList('*', array('store_id' => $data['store_id'],'status'=>'1','type'=>'1'), 0, -1);

        $storeBrand = $this -> storebrand_model -> getList('*', array('store_id' => $data['store_id']), 0, -1);

        $data['store_brand'] = $storeBrand;

        return $data;
    } 
} 
