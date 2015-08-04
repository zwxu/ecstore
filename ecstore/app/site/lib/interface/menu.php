<?php


interface site_interface_menu
{
    public function inputs($config=array());

    public function handle($post);

    public function get_params();

    public function get_config();
}
