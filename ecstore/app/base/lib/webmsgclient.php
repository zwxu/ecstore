<?php
/**
 * 本类使用推荐new class，不应使用kernel:single方法
 */

 require_once('nusoap/lib/nusoap.php');
class base_webmsgclient{

    var $nusoap_client;
    function setSoapCilent($wsdl_url){
        $this->nusoap_client = new nusoap_client($wsdl_url,'wsdl');
        if($this->nusoap_client->getError()){
            return false;
        }else{
            $this->init();
        }
    }

    function init(){
        $this->nusoap_client->soap_defencoding = 'UTF-8';
        $this->nusoap_client->decode_utf8 = false;
        $this->nusoap_client->xml_encoding = 'UTF-8';
    }

    //调用webservice发送短信  yindingsheng
//  function sendMsg($param){
//      $rs = $this->nusoap_client->call('SMSSend',$param);
//
//      if($rs['SMSSendResult'] === '0'){
//          return true;
//      }else{
//          return false;
//      }
//  }

    function sendMsg($param){
          $sparam['userPhones'] = $param['strMobile'];
          $sparam['smsContent'] = strval($param['strContent']);
          $sparam['loginAccount'] = $param['strUserName'];
          $sparam['loginPwd'] = strval($param['strPassword']);
          $sparam['publicKey'] = strval($param['publicKey']);
          $sparam['convertKey'] = md5($sparam['loginAccount']. $sparam['loginPwd'].$sparam['publicKey']);
		  $sparam['sendType'] = $param['sendType'];

          $rs = $this->nusoap_client->call('sendSmsInfo',$sparam);

         if(isset($rs['return'])){
			$return  = json_decode($rs['return'],true);
			if(isset($return['p_status']) && $return['p_status'] === "0"){
				return true;
			}
		}
			return false;
        }


}

