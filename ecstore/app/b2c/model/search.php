<?php


/**
 * mdl_search
 *
 * @uses modelFactory
 * @package
 * @license Commercial
 */

class b2c_mdl_search {

    var $map = array(
            'brand_id'=>'b',
            'cat_id'=>'c',
            'price'=>'p',
            'tag'=>'t',
            'name'=>'n',
            'bn'=>'f',
            'type_id'=>"tp",
            'store_id'=>"i",//店铺
            'freight_bear'=>'fb',//包邮
            'goods_state'=>'gs'//是否二手
        );

    function join($j){
        $v = array();
        foreach((array)$j as $n){
            $n = trim($n);
            if($n!=='')$v[] = rawurlencode($n);
        }
        return count($v)>0?implode(',',$v):false;
    }

    function encode($filter){
        $ret = array();
        $tmpSpec = array();
        if( $filter ){
            foreach($filter as $k=>$j){
                if($p = $this->map[$k]){
                    if(false!==($v = $this->join($j)))
                        $ret[$p] = $p.','.$v;

                }elseif(substr($k,0,2)=='p_'){
                    if(false!==($v = $this->join($j)))
                        $ret[$n = substr($k,2)] = $n.','.$v;
                }
                elseif(substr($k,0,2)=="s_"){
                   //$ret[$k]="s,".substr($k,2)."|".$this->join($j);
                   $ret[$k]="s".substr($k,2).",".$this->join($j);
                }
            }
        }
        return implode('_',$ret);
    }
    function decode($str,&$path,&$system){
        $data = array();
        if($str){
            $str = htmlspecialchars(urldecode($str));
            foreach(explode('_',$str) as $substr){
                $data[] = $substr;
                $columns = explode(',',$substr);
                $part1 = array_shift($columns);
                $map = array_flip($this->map);
                if(is_numeric($part1)){
                    $filter['p_'.$part1] = $columns;
                    $title = '';
                    $p = $part1;
                }elseif (substr($part1,0,1)=="s"){
                    /*$tmp=explode("|",$columns[0]);
                    $filter['s_'.$tmp[0]]=array($tmp[1]);
                    $p='s_'.$tmp[0];*/
                    $filter['s_'.substr($part1,1)] = $columns;
                    $p="s_".substr($part1,1);
                    $columns[0]=substr($part1,1).",".$columns[0];
                }elseif($p = $map[$part1]){
                    $filter[$p] = $columns;
                }else{
                    $title='';
                }
                $path[] = array('type'=>$p,'data'=>$columns,'str'=>implode('_',$data));
            }
            return $filter;
        }
    }
}
