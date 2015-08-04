<?php

 
class base_misc_hash{

    var $beginOffset = 20;
    var $maxSize = 33554432;
    var $hdunpacker = 'V1/V1/H*';
    var $hdsize = 32;

    function workat($file){
        if(!file_exists($file)){
            $this->create($file);
        }else{
            $this->hs = fopen($file,'r+');
        }
    }

    function create($file){
        touch($file);
        $this->hs = fopen($file,'r+');
        fputs($this->hs,'<'.'?php exit()?'.'>');
        fseek($this->hs,16*16*16*4+$this->beginOffset); //定位root节点
        fputs($this->hs,"\0");
    }

    function get($key){
        fseek($this->hs,$base = hexdec(substr($key,0,3))*4+$this->beginOffset);
        list(,$offset) = unpack('V',fread($this->hs,4));

        while($offset){
            $info = $this->getInfo($offset);
            if($info['key']==$key){
                fseek($this->hs,$info['data']);
                $data = fread($this->hs,$info['size']);
                return unserialize($data);
            }
            $offset = $info['next'];
        }
        return false;
    }

    function close(){
        fclose($this->hs);
    }

    function set($key,$value){

        //save data
        $data = serialize($value);
        $size = strlen($data);
        $dataoffset = $this->alloc($size);
        fseek($this->hs,$dataoffset);
        fputs($this->hs,$data);

        //get subhome
        fseek($this->hs,$base = hexdec(substr($key,0,3))*4+$this->beginOffset);
        list(,$subhome) = unpack('V',fread($this->hs,4));
        $subhome = $this->getlast($subhome);

        //getsize
        $offset = $this->alloc($this->hdsize);
        fseek($this->hs,$offset);
        fputs($this->hs,$str = pack('V1V1V1V1H*',0,$subhome,$dataoffset,$size,$key));

        if($subhome>0){
            fseek($this->hs,$subhome);
            fputs($this->hs,pack('V',$offset));
        }else{
            fseek($this->hs,$base);
            fputs($this->hs,pack('V',$offset));
        }

    }

    function getInfo($offset){
        fseek($this->hs,$offset);
        return unpack('V1next/V1prev/V1data/V1size/H*key',fread($this->hs,$this->hdsize));
    }

    function getlast($offset){
        if(!$offset)return 0;

        $info = $this->getInfo($offset);

        if($info['next']){
            return $this->getlast($info['next']);
        }else{
            return $offset;
        }
    }

    function alloc($size){
        fseek($this->hs,0,SEEK_END);
        $offset = ftell($this->hs);
        if($this->maxSize < $offset+$size){
            echo 'max';
            exit;
        }else{
            return $offset;
        }
    }

    function dump(){
        fseek($this->hs,$this->beginOffset);
        for($i=0;$i<16;$i++){
            for($j=0;$j<16;$j++){
                for($k=0;$k<16;$k++){
                    $str = dechex($i).dechex($j).dechex($k);
                    fseek($this->hs,hexdec($str)*4+$this->beginOffset);
                    list(,$offset) = unpack('V',fread($this->hs,4));
                    if($offset){
                        echo "<div>$str --> {$offset}<br />".$this->viewchild($offset).'</div><hr />';
                    }
                }
            }
        }
    }

    function viewchild($offset){
        $info = $this->getInfo($offset);
        return "<div>
            next:{$info['next']}<br />
            prev:{$info['prev']}<br />
            data:{$info['data']}<br />
            size:{$info['size']}<br />
            key:{$info['key']}
            <div style=\"padding-left:20px\">".($info['next']?$this->viewchild($info['next']):'')."</div></div>";
    }
}
