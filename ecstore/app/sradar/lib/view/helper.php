<?php
 
class sradar_view_helper{

    /**
     * 在后台页面尾部加入商品雷达需要的验证信息
     * @param $params
     * @param $smarty
     */
    function function_desktop_footer($params, &$smarty){
        $Certi = base_certificate::get('certificate_id');
        $token = base_certificate::get('token');
        //计算商品雷达所需sign_key-start-
        $data = array(
        'radar_lincense_id' =>  $Certi,
        'radar_product_key' =>  'shopex_zxpt',
        );
        $sign_key = $this->sign($data,$token);
        //-end-

        $html = "<input type='hidden' id='radar_lincense_id' value=$Certi >
                 <input type='hidden' id='radar_product_key' value='shopex_zxpt' >
                 <input type='hidden' id='radar_sign_key' value=$sign_key >";
        return $html;
    }

    function assemble($params){
        if(!is_array($params))  return null;
        ksort($params,SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            $sign .= $key . (is_array($val) ? assemble($val) : $val);
        }
        return $sign;
    }
    
    //计算商品雷达需要的sign值
    function sign($data,$token){
        $rs = strtoupper(md5(strtoupper(md5($this->assemble($data))).$token));
        return $rs;
    }


}
