<?php


interface base_interface_kvstore_extension{

    function increment($key, $offset=1);

    function decrement($key, $offset=1);
}