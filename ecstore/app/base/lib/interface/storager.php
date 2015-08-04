<?php

 
interface base_interface_storager{


    function save($file,&$url,$type,$name,$ext_name="");

    function replace($file,$id);

    function remove($id);

    function getFile($id,$type);


    /*function store($file, $ident, $size='');

    function delete($ident);

    function fetch($ident);*/
}
