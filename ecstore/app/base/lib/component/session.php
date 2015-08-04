<?php

 
class base_component_session{

    function sess_id(){
        return session_id();
    }

    function start(){
        return session_start();
    }

    function close(){
        return session_write_close();
    }

}
