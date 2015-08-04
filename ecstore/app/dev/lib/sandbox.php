<?php

 
class dev_sandbox{

    function show($result){
        print_r($result->get_callback_params());
        print_r($result->get_status());
        print_r($result->get_data());
        print_r($result->get_result());
        print_r($result->get_pid());
    }

}
