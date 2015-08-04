<?php

class base_storage_ttprosystem implements base_interface_storager{

    function base_storage_ttprosystem(){
        $this->memcache=new Memcache;
        $host_mirrors = preg_split('/[,\s]+/',constant('STORAGE_MEMCACHED'));
        if(is_array($host_mirrors) && isset($host_mirrors[0])){
            foreach($host_mirrors as $k =>$v){
                list($host,$port) = explode(":",$v);
                $this->memcache->addServer($host,$port);
            }
        }
        if(defined('HOST_MIRRORS')){
            $host_mirrors = preg_split('/[,\s]+/',constant('HOST_MIRRORS'));
            if(is_array($host_mirrors) && isset($host_mirrors[0])){
                $this->host_mirrors = &$host_mirrors;
                $this->host_mirrors_count = count($host_mirrors)-1;
            }
        }
    }

    function save($file,&$url,$type,$addons,$ext_name=""){
        if($type=='public'){
            $base_dir = '/public/files'; 
        }elseif($type=='private'){
            $base_dir = '/data/private'; 
        }else{
            $base_dir = '/public/images';
        }
        $this->_get_ident($type,$url,$ident,$ext_name,$base_dir);
        $mkey = $base_dir.$ident;
        if($ident && $this->memcache->set($mkey,file_get_contents($file),0,0)){
            return $ident;
        }else{
            return false;
        }
    }

    function replace($file,$id){
        $base_dir = '/public/images';
        if($this->memcache->set($base_dir.$id,file_get_contents($file),0,0)){
            return $id;
        }else{
            return false;
        }
    }

    function _get_ident($type,&$url,&$ident,$ext_name,$base_dir){    
        $ident = $this->_ident().$ext_name;
        if($this->host_mirrors){
            $url = $this->host_mirrors[rand(0,$this->host_mirrors_count)].$base_dir.$ident;
        }
        return $ident;
    }


    function remove($id){
        $base_dir = '/public/images';
        if($id){
            return $this->memcache->delete($base_dir.$id,10);
        }else{
            return true;
        }
    }

    function _ident(){
        $id = md5(microtime().base_certificate::get());
        $id = '/'.substr($id,0,2).'/'.substr($id,2,2).'/'.$id;
        return $id;
    }

    function getFile($id,$type){
        if($type=='public'){
            $base_dir = '/public/files'; 
        }elseif($type=='private'){
            $base_dir = '/data/private'; 
        }else{
            $base_dir = '/public/images';
        }
        $tmpfile = tempnam('/tmp','ttprosystem');
        if($id && file_put_contents($tmpfile,$this->memcache->get($base_dir.$id))){
            return $tmpfile;
        }else{
            return true;
        }
    }
}
