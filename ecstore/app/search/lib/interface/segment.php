<?php

interface search_interface_segment 
{
    public function set($input, $encode='');

    public function reset();

    public function next();

    public function tokenize($input, $encode='');

    public function pre_filter(search_interface_filter $obj);

    public function token_filter(search_interface_filter $obj);
    
}