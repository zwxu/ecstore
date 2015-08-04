<?php


/**
 * mdl_coupon
 *
 * @uses base_db_model
 * @package
 * @version $Id: mdl.coupon.php 2057 2010-04-02 08:38:32Z
 * @copyright
 * @author
 * @license Commercial
 */

class b2c_mdl_coupons extends dbeav_model{
var $idColumn = 'cpns_id'; //表示id的列
    var $textColumn = 'cpns_name';
    var $defaultCols = 'cpns_name,cpns_prefix,pmt_time_begin,pmt_time_end,cpns_id_c,cpns_type,cpns_status,cpns_gen_quantity,cpns_point,num_online,num_online_limit';
    var $adminCtl = 'coupons';
    var $defaultOrder = array('cpns_id','desc');
    var $tableName = 'sdb_b2c_coupons';

    var $__all_filter = array('cpns_status'=>'1');

    /*
     * 前台验证使用的优惠券是否正确
     * TODO 1: 是否可以使用优惠券（这是全局的一个配置，可能配置为不能使用优惠券）
     * 2：是否存在该优惠券，即coupons表中是否存在这条记录
     * 3：对于B类优惠券
     *      判断是否已经使用了
     *    判断是否需要是指定用户使用的优惠券
     *    优惠券可能有多张,如果有一张出错继续下去，正确的可以使用
     */
    function verify_coupons(&$aData=array()) {
        //$_SESSION['cart_coupons'] = null;
        $arr_member_info = kernel::single('b2c_cart_objects')->get_current_member();

        $mlvid = $arr_member_info['member_lv'];
        $coupon = $aData['coupon'];
        //foreach ($aData['coupon'] as $couponCode => &$acoupon) {
            //判断此优惠券是否有效。有，则加入到session中
            if ($this->useMemberCoupon($coupon, $mlvid )) {
                return true;
                //TODO 添加到sessions, 暂时只将
                //$_SESSION['cart_coupons'][] = $acoupon;
            } else {
                unset($aData['coupon']);
                return false;
                //TODO 验证失败该如何处理
            }
        //}

        //如果没有coupons
        if (!$_SESSION['cart_coupons']) {
            //return false;
        }
        //return $coupons;
    }
    /*
     * 前台会员使用优惠券的验证
     */
    function useMemberCoupon($couponCode, $mlvid) {

        $couponFlag = $this->getFlagFromCouponCode($couponCode);
        //echo $couponFlag;exit;
        //如果是匿名用户则只可以使用全局优惠券，即A类优惠券
        // if ((!$mlvid) && (strtoupper($couponFlag) != 'A' )) {  原始转换大写   BUG：优惠券开头未小写字母a

        if ((!$mlvid) && ($couponFlag != 'A' )) {
            //trigger_error(__('匿名用户则只可以使用全局优惠券'), E_USER_WARNING);
            return false;
        }

        $coupons = $this->getCouponByCouponCode($couponCode);
        $coupons = $coupons[0];

        if (!$coupons) {
            //trigger_error(__('优惠券无效'), E_USER_WARNING);
            return false;
        } else {
            //如果存在优惠券，且是B类，则需要验证加密码是否正确
            if (strtoupper($couponFlag) == 'B' ) {
                if(!$this->checkCouponUsed($couponCode)) return false;//验证B类优惠券是否在规定的时间内使用过
                $arr = $this->app->model('sales_rule_order')->dump($coupons['rule_id']);

                if( empty($arr) || empty($arr['member_lv_ids']) ) return false;
                if( array_search( $mlvid,explode(',', $arr['member_lv_ids']) )===false ) return false;//会员等级不符

                if ($this->validCheckNum($coupons, $couponCode)) {
                    $arr_mem_coupon = $this->app->model('member_coupon')->getList( '*',array('memc_code'=>$couponCode) );
                    $arr_mem_coupon = $arr_mem_coupon[0];
                    if( isset($arr_mem_coupon) && $arr_mem_coupon['memc_used_times'] ) {
                        return false;
                    }
                    return true;
                } else {
                    //trigger_error(__('B优惠券验证失败'), E_USER_WARNING);
                    return false;
                }
            } else if( $couponFlag !='A' ) {
                return false;
            }
        }
        return true;
    }
    /**
    * 验证B类优惠券是否使用过
    * @param $couponCode
    * @return bool
    * @author zhangxuehui 2011-11-9
    */
    function checkCouponUsed($couponCode) {
        $coupon_mode = app::get('couponlog')->model('order_coupon_user');
        $user_coupon = $coupon_mode->dump(array('memc_code'=>$couponCode),'cpns_id');
        if(isset($user_coupon['cpns_id'])&&$user_coupon['cpns_id']){
            return false;
        }else{
            return true;
        }
    }
    /**
     * 验证B类优惠券加密位是否正确
     * @param $aCoupon
     * @return unknown_type
     */
    function validCheckNum($aCoupon, $couponCode, $prefix = null) {
        if (!$prefix) {
            $prefix = $this->getPrefixFromCouponCode($couponCode);
        }
        $serial_number = substr($couponCode, -$this->app->getConf('coupon.code.count_len'));
        $check_number = substr($couponCode, strlen($prefix), $this->app->getConf('coupon.code.encrypt_len'));
        $new_check_number = strtoupper(substr(md5($aCoupon['cpns_key'].$serial_number.$prefix),0, $this->app->getConf('coupon.code.encrypt_len')));
        if ($check_number == $new_check_number ) {
            return true;
        }
        return false;
    }
    /**
     * 由优惠券号去获得数据库中的优惠券信息，只取一条，这就要保证录入优惠券的时候，优惠券名不能重复
     * @param $couponCode
     * @return unknown_type
     */
    function getCouponByCouponCode ($couponCode) {
        $couponFlag = $this->getFlagFromCouponCode($couponCode);
        $cpns_prefix = $couponCode;
        //对于B类优惠券，cpns_prefix字段的信息不等于优惠券号
        if(strtoupper($couponFlag) == 'B') {
            $cpns_prefix = $this->getPrefixFromCouponCode($couponCode);
        }

        return $this->getCouponByPrefix($cpns_prefix);
    }
    /**
     * 由前缀取数据库中的一条记录
     * @param $prefix
     * @return unknown_type
     */
    function getCouponByPrefix($prefix,$limit=1) {
        $filter = array(
            'cpns_prefix' => trim($prefix),
        );
        
        return $this->getList('*', $filter,0, $limit);
    }
    function getFlagFromCouponCode($couponCode) {
        return substr($couponCode, 0, 1);
    }
    /**
     * 对B类优惠券，返回他的前缀 = 优惠券号  - 序列号（默认5位）- 加密长度（默认五位）
     * @param $couponCode
     * @return unknown_type
     */
    function getPrefixFromCouponCode($couponCode) {
        $prefix = substr($couponCode, 0, strlen($couponCode)-($this->app->getConf('coupon.code.count_len')+$this->app->getConf('coupon.code.encrypt_len')));
        return $prefix;
    }
    /**
     * @param $couponCode
     * @return unknown_type
     */
    function _verifyCouponCode($couponCode) {
        $couponFlag = $this->getFlagFromCouponCode($couponCode);
        if ($this->_verifyCouponType($couponFlag)) {
            switch ($couponFlag) {
            case 'A':
            case 'S':
                return true;
                break;
            case 'B':
                $prefix = $this->getPrefixFromCouponCode($couponCode);
                $aCoupon = $this->getCouponByPrefix($prefix);
                return $this->validCheckNum($aCoupon, $couponCode, $prefix);
            }
        }else{
            return false;
        }
    }
    function _verifyCouponType($couponFlag) {
        //A：通用优惠券 B:使用一次优惠券 S:ShopEx优惠券
        $_allCouponType = array('A', 'B', 'S');
        return in_array($couponFlag, $_allCouponType);
    }

    function getColumns(){
        $ret = array(
            '_cmd'=>array('label'=>app::get('b2c')->_('操作'),'width'=>120,'html'=>'sale/coupon/command.html'),
            'pmt_time_begin'=>array('label'=>app::get('b2c')->_('开始时间'),'width'=>75,'type'=>'date'),    /* 优惠券起始时间 */
            'pmt_time_end'=>array('label'=>app::get('b2c')->_('结束时间'),'width'=>75,'type'=>'date'),    /* 优惠券截止时间 */
        );
        return array_merge($ret,parent::getColumns());
    }

    function modifier_download(&$rows,$options=array()) {
        foreach($rows as $i=>$key) {
            $aTmp = explode('-', $key);
            $id = $aTmp[0];
            $type = $aTmp[1];
            if ($type==1) {
                $rows[$i] = __('<span onclick="var i=parseInt(prompt(\''.app::get('b2c')->_('请输入需要下载优惠券的数量：').'\',50));if(i)window.open(\'index.php?app=b2c&ctl=admin/sale/coupon&act=download&p[0]=').(string)$id.__('&p[1]=\'+i,\'download\')" class="lnk">'.app::get("b2c")->_("下载").'</span>');
            }else{
                $rows[$i] = '';
            }
        }
    }

    function _filter($filter){
        $where=array(1);
        if ($filter['cpns_name']) {
            $where[] = 'cpns_name like\'%'.$filter['cpns_name'].'%\'';
        }

        if(is_array($filter['cpns_id'])){
            foreach($filter['cpns_id'] as $cpns_id){
                if($cpns_id!='_ANY_'){
                    $coupons[] = 'sdb_b2c_coupons.cpns_id='.intval($cpns_id);
                }
            }
            if(count($coupons)>0){
                $where[] = '('.implode($coupons,' or ').')';
            }
            unset($filter['cpns_id']);
        }

        if(!empty($filter['cpns_type']) && is_string($filter['cpns_type'])){
            $filter['cpns_type'] = explode(',', $filter['cpns_type']);
        }
        if(is_array($filter['cpns_type'])){
            foreach($filter['cpns_type'] as $type){
                if($type!='_ANY_'){
                    $cpns_type[] = 'sdb_b2c_coupons.cpns_type=\''.intval($type).'\'';
                }
            }
            if(count($cpns_type)>0){
                $where[] = '('.implode($cpns_type,' or ').')';
            }
            unset($filter['cpns_type']);
        }

        if (isset($filter['ifvalid'])) {
            if ($filter['ifvalid']==1){
                $curTime = time();
                $where[] = 'cpns_status=\'1\' and pmt_time_begin <= '.$curTime.' and pmt_time_end >'.$curTime;
            }
        }


        return parent::_filter($filter).' and '.implode($where,' and ');
    }

    function checkPrefix($prefix){
        if($this->db->select('SELECT cpns_id from sdb_b2c_coupons WHERE cpns_prefix="'.$this->db->quote($prefix).'" limit 1')){
            return true;
        }else{
            return false;
        }
    }
    function getCouponByIds($aCoupon) {
        if (is_array($aCoupon) && !empty($aCoupon)) {
            $sSql = 'SELECT * FROM sdb_b2c_coupons WHERE cpns_id in ('.implode(',', $aCoupon).')';
            $aTemp = $this->db->select($sSql);
            return $aTemp;
        }else{
            return false;
        }
    }

    function getUserCouponArr() {
        return $this->db->select('SELECT cpns_id,cpns_name FROM sdb_b2c_coupons WHERE cpns_type=\'1\' and cpns_point is null ORDER BY cpns_id desc');
    }

	function pre_restore(&$data,$restore_type='add'){
         if(!($this->is_exists($data['cpns_prefix']))){
             $data['need_delete'] = true;
             return true;
         }
         else{
             if($restore_type == 'add'){
                    $cpns_prefix = $data['cpns_prefix'].'_1';
                    while($this->is_exists($cpns_prefix)){
                        $cpns_prefix = $cpns_prefix.'_1';
                    }
                    $data['cpns_prefix'] = $cpns_prefix;
                    $data['need_delete'] = true;
                 return true;
             }
             if($restore_type == 'none'){
                 $data['need_delete'] = false;
                 return false;
             }
         }
    }

    function is_exists($cpns_prefix){
        $row = $this->getList('cpns_id',array('cpns_prefix' => $cpns_prefix));
        if(!$row) return false;
        else return true;
    }

/*
    function exchange($userId, $cpnsId) {
        $sSql = 'select cpns_point from sdb_b2c_coupons where cpns_status=\'1\' and cpns_type=\'1\' and cpns_point is not null and cpns_id='.intval($cpnsId);
        if ($aCoupon = $this->db->selectRow($sSql)) {
            $nPoint = $aCoupon['cpns_point'];
            $oCoupon = &$this->app->model('trading/coupon');
            //客户为本.先发优惠券，成功之后再扣用户积分
            $oMemberPoint = &$this->app->model('trading/memberPoint');
            if ($oMemberPoint->chgPoint($userId, -abs($nPoint), 'exchange_coupon')) {
                return $oCoupon->generateCoupon($cpnsId, $userId, 1);
            }else{
                return false;
            }
        }else {
            return false;
        }
    }
*/

    function getMemberCoupon($userId,$nPage){
        $aData = $this->db->selectPager('SELECT * FROM sdb_b2c_member_coupon as mc
                                            left join sdb_b2c_coupons as c on c.cpns_id=mc.cpns_id
                                            left join sdb_b2c_promotion as p on c.pmt_id=p.pmt_id
                                            WHERE member_id='.intval($userId).' ORDER BY mc.memc_gen_time DESC',$nPage,PERPAGE);
        return $aData;
    }

/*
    function isLevelAllowUse($pmtId, $mLvId,&$cpnspoint) {
        if ($this->db->select('select pmt_id from sdb_b2c_pmt_member_lv where member_lv_id='.intval($mLvId).' and pmt_id='.intval($pmtId))) {
            $member=$this->app->model('member/member');
            $row=$member->getMemberByUser($_COOKIE['UNAME']);
            if ($row['point']>=$cpnspoint)
                return true;
            else
                return false;
        }else{
            return false;
        }
    }
*/






    function getCouponById($cpnsId) {
        $filter = array(
            'cpns_id' => intval($cpnsId),
        );
        $coupons = $this->getList('*', $filter, -1, -1);
        //TODO 如果没有找到相应的数据，返回值的设定，已经ctl_coupon中程序的进行怎么处理
        return $coupons[0];
    }

    /**
     * 增加coupon
     * @param $aData
     * @return unknown_type
     */
    function addCoupon($aData) {
        //TODO 保存的方法使用对象形式
        switch($aData['cpns_type']) {
        case 0:
            $flag = 'A';
            break;
        case 1:
            $flag = 'B';
            break;
        case 2:
            break;
        }
        $aData['cpns_prefix'] = $flag.$aData['cpns_prefix'];
        if ($aData['cpns_id']){
            $aRs = $this->db->query('SELECT * FROM sdb_b2c_coupons WHERE cpns_id='.intval($aData['cpns_id']));
            $sSql = $this->db->getUpdateSql($aRs,$aData);
            return (!$sSql || $this->db->exec($sSql));
        }else{
            $aData['cpns_key'] = $this->generate_key();
            $aData['cpns_gen_quantity'] = intval($aData['cpns_gen_quantity']);
            $aRs = $this->db->query('SELECT * FROM sdb_b2c_coupons WHERE 0');

            $sSql = $this->db->getInsertSql($aRs,$aData);
            if ($this->db->exec($sSql)){
                return $this->db->lastInsertId();
            }else{
                return false;
            }
        }
    }

    /**
     * 生成加密的字符串附加到优惠券后面
     * @return unknown_type
     */
    function generate_key()
    {
        $n = rand(4,7);
        $str = '';
        for ($j=0; $j<$n; ++$j)
        {
            $str .= chr(rand(21,126));
        }
        return $str;
    }

    function generateCoupon($cpnsId, $userId, $nums,$orderId='') {
        //原则,只要能使用就允许生成,
        $curTime = time();
		$cpnsId = intval($cpnsId);
        $sSql = 'select * from sdb_b2c_coupons as c
            left join sdb_b2c_promotion as p on c.pmt_id=p.pmt_id
            where cpns_status=\'1\' and cpns_type=\'1\' and c.cpns_id='.$cpnsId.' and
            pmt_time_begin <= '.$curTime.' and pmt_time_end >'.$curTime;

        if ($aCoupon = $this->db->selectRow($sSql)) {
            for($i=1; $i<=$nums; $i++) {
                if ($couponCode = $this->_makeCouponCode($aCoupon['cpns_gen_quantity']+$i, $aCoupon['cpns_prefix'], $aCoupon['cpns_key'])) {
                    $aData = array('memc_code' => $couponCode,
                        'cpns_id' => $cpnsId,
                        'member_id' => $userId,
                        'memc_gen_orderid' => $orderId,
                        'memc_gen_time' => time());


                    $rRs = $this->db->query('SELECT * FROM sdb_b2c_member_coupon WHERE 0=1');
                    $sSql = $this->db->GetInsertSQL($rRs, $aData);
                    $this->db->exec($sSql);

                    $aData = array('cpns_gen_quantity' => $aCoupon['cpns_gen_quantity']+$i);
                    $rRs = $this->db->query('SELECT * FROM sdb_b2c_coupons WHERE cpns_id='.intval($cpnsId));
                    $sSql = $this->db->GetUpdateSQL($rRs, $aData);
                    if ($sSql) {
                        $this->db->exec($sSql);
                    }
                }else{
                    return false;
                }
            }
            return true;
        }else{
            return false;
        }
    }

    function downloadCoupon($cpnsId, $nums,$cpns_status='1'){
        //TODO sql语句应该使用改造后的语句不要使用这种直接的sql语句
        $curTime = time();
        $aRes = array();
/**
        $sSql = 'select * from sdb_b2c_coupons as c
            left join sdb_b2c_promotion as p on c.pmt_id=p.pmt_id
            where cpns_status=\'1\' and cpns_type=\'1\' and c.cpns_id='.$cpnsId.' and
            pmt_time_begin <= '.$curTime.' and pmt_time_end >'.$curTime;
        echo $sSql;exit;
        if ($aCoupon = $this->db->selectRow($sSql)) {
*/

        //$sSql = "SELECT * FROM `sdb_b2c_coupons` WHERE cpns_status='1'";
        if ($aCoupon = $this->getList("*", array("cpns_status"=>$cpns_status, 'cpns_id'=>intval($cpnsId)))) {
            $aCoupon = $aCoupon[0];
            for($i=1; $i<=$nums; $i++) {
                if ($couponCode = $this->_makeCouponCode($aCoupon['cpns_gen_quantity']+$i, $aCoupon['cpns_prefix'], $aCoupon['cpns_key'])) {

                    $aRes[] = $couponCode;
                }else{
                    return false;
                }
            }
            $aData = array('cpns_gen_quantity' => $aCoupon['cpns_gen_quantity']+$nums);
            //$rRs = $this->db->query('SELECT * FROM sdb_b2c_coupons WHERE cpns_id='.intval($cpnsId));

            $aData['cpns_gen_quantity'] = $aCoupon['cpns_gen_quantity'] + $nums;
            $aData['cpns_id'] = intval($cpnsId);


            $this->save($aData);

            return $aRes;
        }else{
            return false;
        }
    }

    function _makeCouponCode($iNo, $prefix, $key) {
        if ($this->app->getConf('coupon.code.count_len') >= strlen(strval($iNo))) {
            $iNo = str_pad($this->dec2b36($iNo), $this->app->getConf('coupon.code.count_len'), '0', STR_PAD_LEFT);
            $checkCode = md5($key.$iNo.$prefix);
            $checkCode = strtoupper(substr($checkCode, 0, $this->app->getConf('coupon.code.encrypt_len')));
            $memberCoupon = $prefix.$checkCode.$iNo;
            return $memberCoupon;
        }else{
            return false;
        }
    }

    function dec2b36($int)
    {
        $b36 = array(0=>"0",1=>"1",2=>"2",3=>"3",4=>"4",5=>"5",6=>"6",7=>"7",8=>"8",9=>"9",10=>"A",11=>"B",12=>"C",13=>"D",14=>"E",15=>"F",16=>"G",17=>"H",18=>"I",19=>"J",20=>"K",21=>"L",22=>"M",23=>"N",24=>"O",25=>"P",26=>"Q",27=>"R",28=>"S",29=>"T",30=>"U",31=>"V",32=>"W",33=>"X",34=>"Y",35=>"Z");
        $retstr = "";
        if($int>0)
        {
            while($int>0)
            {
                $retstr = $b36[($int % 36)].$retstr;
                $int = floor($int/36);
            }
        }
        else
        {
            $retstr = "0";
        }

        return $retstr;
    }



    //后台会员优惠券应用
    function applyMemberCoupon($cpnsId, $couponCode, $orderId, $userId) {
        //todo验证
        //1.验证是否是匿名用户 1.验证是否 有效优惠券 2.判断是何种优惠券,分别处理
        if (!$userId) {
            return false;
        }

        $couponFlag = $this->getFlagFromCouponCode($couponCode);
        if (!$this->_verifyCouponCode($couponCode)) {
            return false;
        }

        switch ($couponFlag) {
        case 'A':
            break;
        case 'B':
            $aMeberCoupon = $this->db->selectRow('select *  from sdb_b2c_member_coupon where memc_code=\''.$this->db->quote($couponCode).'\'');

            if ($aMeberCoupon) {
                if ($aMeberCoupon['memc_enabled']=='true'&&$aMeberCoupon['memc_used_times']<$this->app->getConf('coupon.mc.use_times')) {
                    $aRs = $this->db->query('SELECT * FROM sdb_b2c_member_coupon where memc_code=\''.$this->db->quote($couponCode).'\'');
                    $aData['memc_used_times'] = $aMeberCoupon['memc_used_times']+1;
                    $sSql = $this->db->getUpdateSql($aRs,$aData);
                    return (!$sSql || $this->db->exec($sSql));
                }else{
                    trigger_error(__('此优惠券已被取消/使用次数已经用满'),E_USER_NOTICE);
                    return false;
                }
            }else{
                $aData['memc_code'] = $couponCode;
                $aData['cpns_id'] = $cpnsId;
                $aData['member_id'] = $userId;
                $aData['memc_used_times'] = 1;
                $aData['memc_gen_time'] = time();
                $aRs = $this->db->query('SELECT * FROM sdb_b2c_member_coupon WHERE 0');

                $sSql = $this->db->getInsertSql($aRs,$aData);
                return (!$sSql || $this->db->exec($sSql));
            }
            break;
        case 'S':
            break;
        }

    }


    public function pre_recycle($data=array()) {

        $oGPR = $this->app->model('sales_rule_order');
        $param = array('status'=>'false');
        if( is_array($data) ) {
            $filter = array();
            foreach ($data as $key => $value) {
                if( !$value['cpns_id'] ) continue;
                $filter['cpns_id'][] = $value['cpns_id'];
                $tmp = $this->getList('rule_id', $filter);
            }
            $this->app->model('member_coupon')->update( array('disabled'=>'true'),$filter );
            $filter = array();
            if( $tmp && is_array($tmp) )
                $filter['rule_id'] = array_map('current',$tmp);

            if( $filter['rule_id'] ) {
                $param = array('status'=>'false');
                return $oGPR->update($param, $filter);
            }

            return false;
        }

        return false;
    }



    public function suf_restore($sdf=0) {
        $id = $sdf['cpns_id'];
        if(!$sdf) return false;
        $oGPR = $this->app->model('sales_rule_order');
        $param = array('status'=>'true');
        $filter  = array('cpns_id'=>$id);
        $this->app->model('member_coupon')->update( array('disabled'=>'false'),$filter );
        $tmp = $this->getList('rule_id', $filter);
        $filter = $tmp[0];
        $rule = $oGPR->getList('conditions' , $filter);
        if(!$rule) return false;
        $conditions = isset($rule[0]['conditions']) ? $rule[0]['conditions'] : array();
        //同时更新sales_rule_order表里的优惠券号码
        foreach($conditions['conditions'] as &$condition){
        	if(isset($condition['attribute']) && $condition['attribute'] == 'coupon'){
        		$condition['value'] = $sdf['cpns_prefix'];
        	}
        }
        $param['conditions'] = $conditions;
        return $oGPR->update($param, $filter);
    }


    public function pre_delete($id=0) {
        if(!$id) return false;
        $o = app::get('desktop')->model('recycle');
        $rows = $o->getList('*',array('item_id'=>$_POST['item_id']),0,-1);
        $tmp = is_array($rows[0]['item_sdf']) ? $rows[0]['item_sdf'] : @unserialize($rows[0]['item_sdf']);

        $filter = $tmp['rule'];
        $oGPR = $this->app->model('sales_rule_order');
        return $oGPR->delete($filter);
    }


    function suf_delete($arrId) {
        is_array($arrId) or $arrId = array($arrId);
        if ($arrId) {
            $sSql = 'delete from sdb_b2c_coupons where  cpns_id in ('.implode($arrId, ',').')';
            if ($this->db->exec($sSql)) {
                //$related_tables = array('sdb_b2c_member_coupon', 'sdb_b2c_pmt_gen_coupon');
                $related_tables = array('sdb_b2c_member_coupon');
                foreach($related_tables as $table) {
                    $this->db->exec('delete from '.$table.' where  cpns_id in ('.implode($arrId, ',').')');
                }
                return true;
            } else {
                $msg = __('数据删除失败！');
                return false;
            }
        }else{
            $msg = 'no select';
            return false;
        }
    }




    /**
     * 判断是否可以下载，只针对B类优惠券
     * @param $couponsCode：数据中保存的优惠券的前缀号码
     * @return bool ture : 可以下载
     */
    function isDownloadAble($couponsCode) {
        $couponsFlag = $this->getFlagFromCouponCode($couponsCode);
        if ($couponsFlag == 'B') {
            //TODO 以后这里要加载下载是否可以的逻辑，现在只有是B类优惠券就显示下载的按钮
            return true;
        }
        return false;
    }



    public function getlist_exchange() {
        $sql = "SELECT * FROM `sdb_b2c_coupons` WHERE cpns_point is not null AND cpns_status='1'";
        return $this->db->select($sql);
    }
    public function getlist_exchange_count() {
        $sql = "SELECT * FROM `sdb_b2c_coupons` WHERE cpns_point is not null AND cpns_status='1'";
        return $this->db->count($sql);
    }
}
