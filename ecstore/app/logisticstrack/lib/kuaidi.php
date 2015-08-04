<?php

//快递100接口直连类
class logisticstrack_kuaidi {
    var $skey = '5fe352bf2970d521';
    var $api_url = 'http://api.kuaidi100.com/api?';
    var $show_type = 0; //0->json,1->xml,2->html,3->text文本
    var $muti = '1';

    function __construct(){
		if($confurl = app::get('logisticstrack')->getConf('logiurl')){
			$this->api_url = $confurl;
		}
		if($conkey = app::get('logisticstrack')->getConf('logikey')){
			$this->skey = $conkey;
		}
        $this->net = kernel::single('base_httpclient');
    }

    //快递单号,物流公司编码
    function show($logi_no, $dlycorp_code){
        $param['id'] = $this->skey;
        $param['com'] = $dlycorp_code;
        $param['nu'] = $logi_no;
        $param['show'] = $this->show_type;
        $param['muti'] = $this->muti;

        $sparam = http_build_query($param);

        $url = $this->api_url.$sparam;
        $rs = $this->net->get($url);
        
        switch($show_type){
            case 0:$tmp = $this->fomatJson($rs);break;
            default : $tmp = $this->fomatJson($rs);
        }

        $result['status'] = $tmp['status'];
		$result['message'] = $tmp['message'];
		$result['data'] = $tmp['data'];

		return $result;

    }

    function fomatJson($rs){
        $rs = json_decode($rs,1);
        return $rs;
    }

}