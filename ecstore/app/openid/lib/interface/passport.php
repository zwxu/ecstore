<?php


class openid_interface_passport implements pam_interface_passport{

    var $url;
    var $key;
    var $cert_id;

    function __construct(){
        $this->url = "http://www.ecopen.cn/api/";
        $this->cert_id = base_certificate::get('certificate_id');
    }
    function get_name(){}
    function get_login_form($auth,$appid,$view,$ext_pagedata=array()){}
    function login($auth,&$usrdata){}
    function loginout($auth,$backurl="index.php"){}
    function get_data(){}
    function get_id(){}
    function get_expired(){}


}
