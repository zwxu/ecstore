<?php

class cellphone_base_misc_activities extends cellphone_cellphone
{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }

    // 获取活动首页信息
    public function get_activity(){
        $params = $this->params;
        $picSize = in_array(strtolower($params['pic_size']), array('cl', 'cs'))?strtolower($params['pic_size']):'cl';
        $limit = $params['pageLimit']?intval($params['pageLimit']):10;
        $offset = $params['nPage']?(intval($params['nPage'])-1)*$limit:0;

        $objAct = app::get('cellphone')->model('activity');
        $aData = $objAct->getList('act_id,act_name,banner,logo',null,$offset,$limit,'p_order asc');
        foreach((array)$aData as $key => $value){
            $temp = $objAct->get_detail($value['act_id']);
            $aData[$key]['start_time'] = $temp['start_time'];
            $aData[$key]['end_time'] = $temp['end_time'];
            $aData[$key]['banner'] = $this->get_img_url($value['banner'],'s');
            $aData[$key]['logo'] = $this->get_img_url($value['logo'],$picSize);
        }
        $this->send(true,$aData,'sucess');
    }

    // 获取活动列表页信息
    public function get_gallery(){
        $params = $this->params;
        $must_params = array(
            'act_id'=>'活动标识',
        );
        $this->check_params($must_params);
        $picSize = in_array(strtolower($params['pic_size']), array('cl', 'cs'))?strtolower($params['pic_size']):'cl';
        $limit = $params['pageLimit']?intval($params['pageLimit']):10;
        $offset = $params['nPage']?(intval($params['nPage'])-1)*$limit:0;

        $objAct = app::get('cellphone')->model('activity');
        $aData = $objAct->dump(array('act_id'=>$params['act_id']),'act_name,banner,source,original_id','default');
        $aData['banner'] = $this->get_img_url($aData['banner'],'s');

        $aData['source'] = unserialize($aData['source']);
        if($aData['source'] && is_array($aData['source'])){
            $act_id = $aData['original_id']?$aData['original_id']:'-1';
            $time = time();
            $app = @app::get($aData['source']['app']);
            $activity = @$app->model($aData['source']['m1']);
            $actapply = @$app->model($aData['source']['m2']);
            if($app && $activity && $actapply){
                $aData['gallery'] = array();
                $objGoods = app::get('b2c')->model('goods');
                $aAct = $activity->getRow('*',array('act_id'=>$aData['original_id']));
                cellphone_misc_exec::get_change($aAct);
                $aAct['act_id'] = $aAct['act_id']?intval($aAct['act_id']):-1;
                $aApply = $actapply->getList('*',array('aid'=>$aAct['act_id']),$offset,$limit);
                cellphone_misc_exec::get_change($aApply);
                if($aAct['start_time'] && $aAct['end_time'] && $aAct['start_time']<=time() && $aAct['end_time']>time()){
                    $goods_id = array();
                    foreach((array)$aApply as $row){
                        $temp = explode(',', $row['gid']);
                        $temp = array_filter($temp);
                        $temp = !empty($temp)?$temp:array(-1);

                        if($app->app_id == 'package'){
                            $price = 0;
                            foreach((array)$objGoods->getList('price',array('goods_id'=>$temp)) as $item){
                                $price += floatval($item['price']);
                            }
                            $aData['gallery'][] = array(
                                'object_type' => $aData['source']['app'],
                                'goods_id' => $row['act_id'],
                                'image' => $this->get_img_url($row['image'],$picSize),
                                'name' => $row[($actapply->textColumn?$actapply->textColumn:'name')],
                                'real_price' => isset($row['last_price'])?$row['last_price']:$row['price'],
                                'price' => $price,
                                'freight_bear' => $row['freight_bear'],
                            );
                        }else{
                            $tData = $objGoods->getRow('goods_id,name,freight_bear,price,udfimg,thumbnail_pic,image_default_id',array('goods_id'=>$temp));
                            $aData['gallery'][] = array(
                                'object_type' => $aData['source']['app'],
                                'goods_id' => $tData['goods_id'],
                                'image' => $tData['udfimg']=='true'?$this->get_img_url($tData['thumbnail_pic'],$picSize):$this->get_img_url($tData['image_default_id'],$picSize),
                                'name' => $tData['name'],
                                'real_price' => isset($row['last_price'])?$row['last_price']:$row['price'],
                                'price' => $tData['price'],
                                'freight_bear' => $tData['freight_bear'],
                            );
                        }
                    }
                }
            }
        }
        unset($aData['source'], $aData['original_id']);
        $this->send(true,$aData,'sucess');
    }
}