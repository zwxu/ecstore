<?php

 

class business_mdl_goods_import_tpl extends dbeav_model{
    var $defaultOrder = array('createtime','DESC');
    /**
     * 得到唯一的订单编号
     * @params null
     * @return string 订单编号
     */
    public function gen_id()
    {
        $i = rand(0,9999);
        do{
            if(9999==$i){
                $i=0;
            }
            $i++;
            $tpl_id = date('YmdH').str_pad($i,4,'0',STR_PAD_LEFT);
            $row = $this->db->selectrow('SELECT tpl_id from sdb_business_goods_import_tpl where tpl_id ='.$tpl_id);
        }while($row);
        return $tpl_id;
    }
    var $ioSchema = array(
        'csv' => array(
            'bn:商品编号'=> 'bn',
            'ibn:规格货号' => array('bn','product'),
            'col:品牌' => 'brand/brand_id',
            'keywords:商品关键字' => 'keywords',
            'col:市场价' => array('price/mktprice/price','product'),
            'col:成本价' => array('price/cost/price','product'),
            'col:销售价' => array('price/price/price','product'),
            'col:商品名称' => 'name',
            'col:上架' => 'status',
            'col:规格' => 'spec',
            'col:商品简介' => 'brief',
            'col:运费' => 'freight_bear',
            'col:详细介绍' => 'description',
            'col:重量' => array('weight','product'),
            'col:单位' => 'unit',
            'col:库存' => array( 'store','product' )
        )
    );
    function get_type_info($cat_id){
        
        $app_b2c=app::get('b2c');
        
        $pcat=$app_b2c->model('goods_cat')->dump($cat_id,'*');
        
        $sdf=array('brand' => array('*'),'spec' => array('*'),'props'=>array('*',array('props_value'=>array('*',null, array( 0,-1))) ));
        $gtype = $app_b2c->model('goods_type')->dump($pcat['type_id'],'*',$sdf);
        $brand=$app_b2c->model('brand')->getlist('brand_name,brand_id',array('brand_id'=>array_keys($gtype['brand'])));
        
        $spec_values=$app_b2c->model('spec_values')->getlist('*',array('spec_id'=>array_keys($gtype['spec'])));
        $specification=$app_b2c->model('specification')->getlist('*',array('spec_id'=>array_keys($gtype['spec'])));
        foreach($specification as $key=>$svalue){
            $gtype['spec'][$svalue['spec_id']]['spec_name']=$svalue['spec_name'];
        }
        
        foreach($spec_values as $key=>$value){
            $gtype['spec'][$value['spec_id']]['spec_value'][]=$value['spec_value'];
            $gtype['spec'][$value['spec_id']]['spec_value_id'][]=$value['spec_value_id'];
        }
        $array=array();
        $array[]="========================规格可选值========================\n*注*：多规格用“|”分割 比如鞋子“红色|35”代表该鞋子规格为：红色35码";
        foreach($gtype['spec'] as $key=>$v){
            $array[]='【'.$v['spec_name'].'】：'.implode('，',$v['spec_value']);
        }
        $array[]="\n========================属性可选值========================\n*注*：属性分为选择和手动输入，给出可选值的是选择属性，只能在给出的候选值中选择一个。";
        foreach($gtype['props'] as $key=>$v){
            if($v['type']=='select'){
                $array[]='【props:'.$v['name'].'】：'.implode('，',$v['options']);
            }else{
                $array[]='【props:'.$v['name'].'】：[该属性只能手动输入]';
            }
        }
        $array[]="\n========================运费可选值========================";
        $array[]="【col:运费】：包邮，不包邮";
        $array[]="\n========================品牌可选值========================";
        $brands=array();
        foreach($brand as $key=>$b){
            $brands[]=$b['brand_name'];
        }
        $array[]='【品牌】：'.implode('，',$brands);
        
        return array('result'=>implode("\n\n",$array),'gtype'=>$gtype);
    }
    
    function turn_to_sdf($contents,&$sdfContents,&$errorContents,$type_id,$cat_id){
    
        $app_b2c=app::get('b2c');
        reset($contents);
        if(empty($contents[0][0])){
            $msg = array( 'error'=>$app_b2c->_('导入商品为空') );
            $msgList['error'][] = $msg['error'];
            return $msgList;
        }
        $lineNo = 0;
        $tmpl = array();
        $tTmpl = array();
        $gTitle = array();
        $data = array();
        $tObjContent = array();
        $errorObj = false;
        while( true ){
            $curContent = array_shift( $contents );
            $lineNo++;
           
            $newObjFlag = false;
            $msg = '';
            //取得该行数据。
            $rowData = $this->prepared_import_csv_row( $curContent,$data['title'],$tmpl,$mark,$newObjFlag,$msg,$type_id,$lineNo);
          
            if( $msg['error'] ){
                $msgList['error'][] = $msg['error'];
            }
            if( $msg['warning'] ){
                foreach( $msg['warning'] as $mk => $mv ){
                    $msgList['warning'][] = $mv;
                }
            }
            if( $newObjFlag ){
                if( $errorObj ){
                    $errorList[] = $tObjContent;
                    $errorObj = false;
                }

                $tObjContent = array();
                if( $mark != 'title' ){
                    $msg ='';

                    $saveData = $this->prepared_import_csv_obj( $data,$mark,$tmpl,$msg,$cat_id,$lineNo);
                     
                    if( $msg['error'] ){
                        $msgList['error'][] = $msg['error'];
                    }
                    if( $msg['warning'] ){
                        foreach( $msg['warning'] as $mk => $mv ){
                            $msgList['warning'][] = $mv;
                        }
                    }
                   if( $saveData === false ){
                       return $msgList;
                        $errorContents[] = $gTitle;
                        foreach( $tObjContent as $ck => $cv ){
                            $errorContents[] = $cv;
                        }
                    
                    }
                    if( $saveData ){
                        $saveData['type']=array('type_id'=>$type_id);
                        $saveData['category']=array('cat_id'=>$cat_id);
                        $sdfContents[] = $saveData;
                    }
                    if( $mark ){
                        eval('$data["'.implode('"]["',explode('/',$mark)).'"] = array();');
                    }

                }else{
                    $tTmpl = $rowData;
                    $gTitle = $curContent;
                }
                $tObjContent[] = $curContent;
                if( $rowData === false ){
                    return $msgList;
                    $errorObj = true;
                }
            }
            if( $mark ){
                if( $mark == 'title' ){
                    eval('$data["'.implode('"]["',explode('/',$mark)).'"] = $rowData;');
                }else{
                    eval('$data["'.implode('"]["',explode('/',$mark)).'"][] = $rowData;');
                }
            }       
            if( !current($contents) && current( $data['contents'] )){
                $saveData= $this->prepared_import_csv_obj( $data,$mark,$tmpl,$msg,$cat_id,$lineNo);
               if( $msg['error'] ){
                   $msgList['error'][] = $msg['error'];
               }
               if( $msg['warning'] ){
                   foreach( $msg['warning'] as $mk => $mv ){
                        $msgList['warning'][] = $mv;
                   }
               }
               
                if( $saveData === false ){
                    return $msgList;
                    $errorContents[] = $gTitle;
                    foreach( $tObjContent as $ck => $cv ){
                        $errorContents[] = $cv;
                    }
                }
                if( $saveData ){
                    $saveData['type']=array('type_id'=>$type_id);
                    $saveData['category']=array('cat_id'=>$cat_id);
                    $sdfContents[] = $saveData;
                }
                if( $mark ){
                    eval('$data["'.implode('"]["',explode('/',$mark)).'"] = array();');
                }
            }
            if( !$curContent ) break;

       }
       if($contents ){
            $contents = array_unshift( $contents,$gTitle );
       }
       if( $msgList['error'] || $msgList['warning'] ){
           return $msgList;
       }
       return true;
    }
    function prepared_import_csv_row($row,$title,&$goodsTmpl,&$mark,&$newObjFlag,&$msg,$type_id,$lineNo){
        $app_b2c=app::get('b2c');
        if( substr($row[0],0,3) == 'bn:' ){
            $mark = 'title';
            $newObjFlag = true;

            $oGType = $app_b2c->model('goods_type');
            $goodsTmpl['gtype'] = $oGType->dump($type_id,'*',array('brand' => array('*'),'spec' => array('*'),'props'=>array('*',array('props_value'=>array('*',null, array( 0,-1,'order_by ASC' ))) )) );
            if( !$goodsTmpl['gtype'] ){
                $msg = array('error'=>'第'.$lineNo.'行&nbsp;&gt;&nbsp;'.$app_b2c->_('商品类型:').ltrim( $row[0],'*:' ).$app_b2c->_(' 不存在'));
                return false;
            }

            if( $goodsTmpl['gtype']['props'] ){
                foreach( $goodsTmpl['gtype']['props'] as $propsk => $props ){
                    $this->ioSchema['csv']['props:'.$props['name']] = 'props/p_'.$propsk.'/value';
                    foreach( $props['options'] as $p => $v ){
                        $goodsTmpl['props_hash'][$props['name']][$v] = $p;
                    }
                }
            }
            $oMlv = $app_b2c->model('member_lv');
            foreach( $oMlv->getList('member_lv_id,name','',0,-1) as $mlv ){
                $this->ioSchema['csv']['price:'.$mlv['name']] = array('price/member_lv_price/'.$mlv['member_lv_id'].'/price','product');
            }
            return array_flip($row);
        }else{
            $mark = 'contents';
            $rIndex=$title[$app_b2c->_('ibn:规格货号')];
            if( $row[$rIndex] ){
                if( $this->io->proBn && array_key_exists( $row[$rIndex] , $this->io->proBn ) ){
                    $msg = array( 'error'=>'第'.$lineNo.'行&nbsp;&gt;&nbsp;'.$app_b2c->_('规格货号:').$row[$rIndex].$app_b2c->_(' 文件中有重复') );
                    return false;
                }
                $this->io->proBn[$row[$rIndex]] = null;
            }
            if( !$row[$rIndex] || in_array($row[$title[$app_b2c->_('col:规格')]],array('','-')) ){
                $newObjFlag = true;
            }
            return $row;
        }
    }
    function prepared_import_csv_obj($data,&$mark,$goodsTmpl,&$msg = '',$cat_id,$lineNo){
        $app_b2c=app::get('b2c');
        if( !$data['contents'] )return null;
        $mark = 'contents';
        $gData = &$data['contents'];
        $gTitle = $data['title'];
        $rs = array();
        //id
        if( $this->io->goodsBn && array_key_exists( $gData[0][$gTitle[$app_b2c->_('bn:商品编号')]] , $this->io->goodsBn ) ){
            $msg = array( 'error'=>'第'.$lineNo.'行&nbsp;&gt;&nbsp;'.$app_b2c->_('商品编号:').$gData[0][$gTitle[$app_b2c->_('bn:商品编号')]].$app_b2c->_(' 文件中有重复') );
            return false;
        }

        $goodsId = $app_b2c->model('goods')->dump(array('bn'=>$gData[0][$gTitle[$app_b2c->_('bn:商品编号')]]),'goods_id');
        if( $goodsId['goods_id'] )
            $gData[0]['col:goods_id'] = $goodsId['goods_id'];

        $gData[0][$gTitle[$app_b2c->_('col:上架')]] = (in_array( trim( $gData[0][$gTitle[$app_b2c->_('col:上架')]] ), array('Y','TRUE') )?'true':'false');

        $gData[0][$gTitle[$app_b2c->_('col:运费')]] = (in_array( trim( $gData[0][$gTitle[$app_b2c->_('col:运费')]] ), array('包邮') )?'business':'member');
        
        foreach( $gTitle as $colk => $colv ){
            if( substr( $colk, 0,6 ) == 'props:' ){
                if( !$this->ioSchema['csv'][$colk] )
                    $msg['warning'][] = '第'.$lineNo.'行&nbsp;&gt;&nbsp;'.$app_b2c->_('属性：').ltrim($colk,'props:').$app_b2c->_('不存在');
                else{
                    if( $goodsTmpl['props_hash'][ltrim($colk,'props:')] && $gData[0][$gTitle[$colk]] && !array_key_exists( $gData[0][$gTitle[$colk]], $goodsTmpl['props_hash'][ltrim($colk,'props:')] ) )
                        $msg['warning'][] = '第'.$lineNo.'行&nbsp;&gt;&nbsp;'.$app_b2c->_('属性值：').$gData[0][$gTitle[$colk]].$app_b2c->_('不存在');
                    if( $goodsTmpl['props_hash'][ltrim($colk,'props:')] )
                    $gData[0][$gTitle[$colk]] = $goodsTmpl['props_hash'][ltrim($colk,'props:')][$gData[0][$gTitle[$colk]]];
                }
            }
            if( (substr( $colk,0,6 ) == 'price:' || in_array( $colk , array($app_b2c->_('col:市场价'),$app_b2c->_('col:成本价'),$app_b2c->_('col:销售价')) ) ) && $gData[0][$gTitle[$colk]] !== 0 && !$gData[0][$gTitle[$colk]] ){
                unset($gData[0][$gTitle[$colk]]);
            }
        }

        //品牌
        $oBrand = $app_b2c->model('brand');
        if( !$gData[0][$gTitle[$app_b2c->_('col:品牌')]] ){
            $brandId = array('brand_id'=>0);
        }else{
            $brandId = $oBrand->dump(array('brand_name'=>$gData[0][$gTitle[$app_b2c->_('col:品牌')]]),'brand_id');
            if( !$brandId['brand_id'] && $gData[0][$gTitle[$app_b2c->_('col:品牌')]] )
                $msg['warning'][] = '第'.$lineNo.'行&nbsp;&gt;&nbsp;'.$app_b2c->_('品牌：').$gData[0][$gTitle[$app_b2c->_('col:品牌')]].$app_b2c->_('不存在');
        }
        $gData[0][$gTitle[$app_b2c->_('col:品牌')]] = intval( $brandId['brand_id'] );

        //货品 处理return值
        $rs = $gData[0];
        $oPro = $app_b2c->model('products');
        $spec = array();
        if( count( $gData ) == 1 ){
            unset($rs[$gTitle[$app_b2c->_('col:规格')]] );
            if( !$gData[0][$gTitle[$app_b2c->_('ibn:规格货号')]] )
                $gData[0][$gTitle[$app_b2c->_('ibn:规格货号')]] = $gData[0][$gTitle[$app_b2c->_('bn:商品编号')]];
            $proId = $oPro->dump( array('bn'=>$gData[0][$gTitle[$app_b2c->_('ibn:规格货号')]] ),'product_id,goods_id' );

            if( ( !$rs['col:goods_id'] && $proId['product_id'] ) || ( $rs['col:goods_id'] && $rs['col:goods_id'] != $proId['goods_id'] ) ){
                $msg = array( 'error'=>'第'.$lineNo.'行&nbsp;&gt;&nbsp;'.$app_b2c->_('规格货号:').$gData[0][$gTitle[$app_b2c->_('bn:商品编号')]].$app_b2c->_(' 已存在' ));
                return false;
            }

            $rs['product'][0] = $gData[0];
            if( $proId['product_id'] )
                $rs['product'][0]['col:product_id'] = $proId['product_id'];
        }else{

            reset($gData);
            $oSpec = $app_b2c->model('specification');
            foreach( explode('|',$gData[0][$gTitle[$app_b2c->_('col:规格')]] ) as $speck => $specName ){
                $spec[$speck] = array(
                    'spec_name' => $specName,
                    'option' => array(),
                );
            }
            
            while( ( $aPro = next($gData) ) ){
                $aProk = key( $gData );
                $proId = $oPro->dump( array('bn'=>$aPro[$gTitle[$app_b2c->_('ibn:规格货号')]]),'product_id,goods_id' );

                if( ( !$rs['col:goods_id'] && $proId['product_id'] ) || ( $rs['col:goods_id'] && $rs['col:goods_id'] != $proId['goods_id'] ) ){
                    $msg = array( 'error'=>'第'.$lineNo.'行&nbsp;&gt;&nbsp;'.$app_b2c->_('规格货号:').$aPro[$gTitle[$app_b2c->_('ibn:规格货号')]].$app_b2c->_(' 已存在' ));
                    return false;
                }
                $aPro['col:product_id'] = $proId['product_id'];
                $rs['product'][$aProk] = $aPro;
                foreach( explode('|',$aPro[$gTitle[$app_b2c->_('col:规格')]]) as $specvk => $specv ){
                    $spec[$specvk]['option'][$specv] = $specv;
                }
            }
            foreach($spec as $sk => $aSpec){
                $specIdList = $oSpec->getSpecIdByAll($aSpec);
                foreach( $specIdList as $sv ){
                    if( array_key_exists($sv['spec_id'],(array)$goodsTmpl['gtype']['spec'] ) ){
                        $spec[$sk]['spec_id'] = $sv['spec_id'];
                    }
                }
                if( !$spec[$sk]['spec_id'] )
                    $spec[$sk]['spec_id'] = $specIdList[0]['spec_id'];
                if( !$spec[$sk]['spec_id'] ){
                    $msg = array('error'=>'第'.$lineNo.'行&nbsp;&gt;&nbsp;'.$app_b2c->_('规格：').$aSpec['spec_name'].$app_b2c->_('出现错误 请检查') );
                    return false;
                }
                $spec[$sk]['option'] = $oSpec->getSpecValuesByAll($spec[$sk]);
            }
            $pItem = 0;

            foreach( $rs['product'] as $prok => $prov ){

                if( !($pItem++) )$rs['product'][$prok]['col:default'] = 1;
                $proSpec = explode('|',$prov[$gTitle[$app_b2c->_('col:规格')]]);
                $rs['product'][$prok]['col:spec_info'] = implode(',',$proSpec);

                foreach( $proSpec as $aProSpeck => $aProSpec ){
                    $rs['product'][$prok]['col:spec_desc']['spec_value'][$spec[$aProSpeck]['spec_id']] = $spec[$aProSpeck]['option'][$aProSpec]['spec_value'];
                    $rs['product'][$prok]['col:spec_desc']['spec_private_value_id'][$spec[$aProSpeck]['spec_id']] = $spec[$aProSpeck]['option'][$aProSpec]['private_spec_value_id'];
                    $rs['product'][$prok]['col:spec_desc']['spec_value_id'][$spec[$aProSpeck]['spec_id']] = $spec[$aProSpeck]['option'][$aProSpec]['spec_value_id'];
                }
            }

            unset( $rs[$gTitle[$app_b2c->_('col:规格')]] );
            foreach( $spec as $sk => $sv ){
                foreach( $sv['option'] as $psk => $psv ){
                    $rs[$gTitle[$app_b2c->_('col:规格')]][$sv['spec_id']]['option'][$psv['private_spec_value_id']] = $psv;
                }
            }
       }

        $return =  $this->ioSchema2sdf( $rs,$gTitle, $this->ioSchema['csv'] );        

        if( trim( $gData[0][$gTitle[$app_b2c->_('keywords:商品关键字')]] ) ){
            $return['keywords'] = array();
            foreach( explode( '|', $gData[0][$gTitle[$app_b2c->_('keywords:商品关键字')]] ) as $kwk => $kwv ){
                $return['keywords'][] = array(
                    'keyword' => $kwv,
                    'res_type' => 'goods'
                );
            }
        }

        foreach( $rs['product'] as $prok => $prov ){
            if($prov[$gTitle[$app_b2c->_('col:上架')]] == 'N'){
                $return['product'][$prok-1]['status'] = 'false';
            }
            if($prov[$gTitle[$app_b2c->_('col:上架')]] == 'Y'){
                $return['product'][$prok-1]['status'] = 'true';
            }
        }
        foreach( $return['product'] as $pk => $pv ){
            $return['product'][$pk]['name'] = $return['name'];
            foreach( $pv['price']['member_lv_price'] as $lvk => $lvv ){
                if( $lvv['price'] === null || $lvv['price'] === '' ){
                    unset( $return['product'][$pk]['price']['member_lv_price'][$lvk] );
                    continue;
                }
                $return['product'][$pk]['price']['member_lv_price'][$lvk]['level_id'] = $lvk;
            }
        }

        $return['type']['type_id'] = intval( $goodsTmpl['gtype']['type_id'] );

        $this->io->goodsBn[$return['bn']] = null;

        return $return;
    }
    function ioSchema2sdf($data,$title,$csvSchema,$key = null){
        $rs = array();
        $subSdf = array();
        foreach( $csvSchema as $schema => $sdf ){
            $sdf = (array)$sdf;
            if( ( !$key && !$sdf[1] ) || ( $key && $sdf[1] == $key ) ){
                eval('$rs["'.implode('"]["',explode('/',$sdf[0])).'"] = $data[$title[$schema]];');
                unset($data[$title[$schema]]);
            }else{
                $subSdf[$sdf[1]] = $sdf[1];
            }
        }
        if(!$key){
            foreach( $subSdf as $k ){
                foreach( $data[$k] as $v ){
                    $rs[$k][] = $this->ioSchema2sdf($v,$title,$csvSchema,$k);
                }
            }
        }
        foreach( $data as $orderk => $orderv ){
            if( substr($orderk,0,4 ) == 'col:' ){
                $rs[ltrim($orderk,'col:')] = $orderv;
            }
        }
        return $rs;
    }
    
}
