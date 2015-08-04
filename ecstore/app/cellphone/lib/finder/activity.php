<?php
class cellphone_finder_activity{
    var $column_start_time = '开始时间';
    var $column_end_time = '结束时间';
    var $detail_basic = '基本信息';
    var $column_control = '操作';

    function __construct($app){
        $this->app = $app;
        $this->app_list = array();
    }
    
    function column_start_time($row){      
        $aData = $this->app->model('activity')->get_detail($row['act_id']);
        if($aData['start_time'])$aData['start_time'] = Date('Y-m-d H:m:s', $aData['start_time']);
        return $aData['start_time'];
    }
    
    function column_end_time($row){      
        $aData = $this->app->model('activity')->get_detail($row['act_id']);
        if($aData['end_time'])$aData['end_time'] = Date('Y-m-d H:m:s', $aData['end_time']);
        return $aData['end_time'];
    }
    
    function detail_basic($act_id){
        $render = $this->app->render();
        $aData = app::get('cellphone')->model('activity')->dump(array('act_id'=>$act_id),'*','default');
        $aData['source'] = unserialize($aData['source']);
        $aData['original_id'] = $aData['original_id']?intval($aData['original_id']):-1;
        $aActInfo = array();
        foreach((array)$aData['relation'] as $row){
            $aActInfo[] = intval($row['rel_id']);
        }
        $aData['relation'] = array();
        $aActInfo = !empty($aActInfo)?$aActInfo:array(-1);
        $app = @app::get($aData['source']['app']);
        $activity = @$app->model($aData['source']['m1']);
        $actapply = @$app->model($aData['source']['m2']);
        if($app && $activity && $actapply){
            $aAct = $activity->getRow('*',array($activity->idColumn=>$aData['original_id']));
            cellphone_misc_exec::get_change($aAct);
            $aAct['act_id'] = $aAct['act_id']?intval($aAct['act_id']):-1;
            $aApply = $actapply->getList('*',array('aid'=>$aAct['act_id'],$actapply->idColumn=>$aActInfo),0,-1);
            cellphone_misc_exec::get_change($aApply);
            
            $aAct['start_time'] = date('Y-m-d',$aAct['start_time']);
            $aAct['end_time'] = date('Y-m-d',$aAct['end_time']);
            $render->pagedata['activity'] = $aAct;
            
            foreach((array)$aApply as $key => $value){
                $gids = array_filter(explode(',',$value['gid']));
                if($gids){
                    $temp = app::get('b2c')->model('goods')->getList('goods_id,name,bn',array('goods_id|in'=>$gids));
                    foreach((array)$temp as $ckey => $cvalue){
                        $temp[$ckey]['url'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg'=>$cvalue['goods_id']));
                    }
                    $aApply[$key]['goods_info'] = $temp;
                }
                if($value['member_id']){
                    $sto= kernel::single("business_memberstore",$value['member_id']);
                    $aApply[$key]['store_info'] = array(
                        'login_name' => $sto->storeinfo['account_loginname'],
                        'store_gradename' => $sto->storeinfo['store_gradename'],
                        'issue_typename' => $sto->storeinfo['issue_typename'],
                        'store_name' => $sto->storeinfo['store_name'],
                    );
                }
            }
            $render->pagedata['detail'] = $aApply;
        }
        return $render->fetch('admin/activity/detail.html');
    }
    
    function column_control($row){
        $returnValue = '<a href="index.php?app=cellphone&ctl=admin_activity&act=edit&p[0]='.$row['act_id'].'&finder_id='.$_GET['_finder']['finder_id'].'"  target="blank">'.app::get('b2c')->_('编辑').'</a>';
        return $returnValue;
    }
}