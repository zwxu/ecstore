<?php

 
interface base_admin_backup{
    public function start();
    public function end();
    public function next();
    public function get();
}
