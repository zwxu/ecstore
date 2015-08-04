<?php


interface desktop_interface_passportpresent{
    //在登陆页面时，验证码之后调用
    public function handle(&$object);
}
