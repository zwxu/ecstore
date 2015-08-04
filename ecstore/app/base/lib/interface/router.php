<?php

 
interface base_interface_router{

    function __construct($app);

    function gen_url($params=array());

    function dispatch($query);

}
