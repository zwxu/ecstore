<?php
class base_rpc_check{
    
    function handshake(){
        if(base_kvstore::instance('ecos')->fetch('net.handshake',$value)){
            echo $value;
        }
        base_kvstore::instance('ecos')->store('net.handshake',md5(microtime()));
    }
    
    function login_hankshake()
    {
        if(base_kvstore::instance('ecos')->fetch('net.login_handshake',$value)){
            echo $value;
        }
        base_kvstore::instance('ecos')->store('net.login_handshake',md5(microtime()));
    }
}