<?php

 
class b2c_mdl_goods_entity_items extends dbeav_model{

    public function save(&$sdf)
    {
        return parent::save($sdf);
    }
    

    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null)
    {
        if ($filter)
            return parent::getList($cols, $filter, $offset, $limit, $orderby);
        else
            return parent::getList($cols, null, $offset, $limit, $orderby);
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
}