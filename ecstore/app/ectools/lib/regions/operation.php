<?php
/**
 
  * 围绕地区包的一系列的操作
 
  * @version 0.1
 
  * @package ectools.lib.regions
 
  */
class ectools_regions_operation
{
    /**

      * @var object 应用实例对象

      */
    private $app;
    
    /**

      * @var object 模型实例

      */ 
    private $model;
    
    /**
      * @var array 地区数组

      */ 
    public $regions;
    
    /**

      * 构造方法

      * @param object 当前app的

      * @return null
      */ 
    public function __construct($app)
    {
        $this->app = $app;
        $this->model = $this->app->model('regions');
    }
    
     /**
     * 主要用于后台显示，判断当前的数据是否超过100，显示方式不同
     * @params null
     * @return boolean
     */
    public function getTreeSize()
    {
        $cnt = $this->model->count();
        
        if ($cnt > 100)
            return true;
        else
            return false;
    }
    
    /**
     * 获取指定parent region id的下级地区数量
     * @params string region id
     * @return int 数量
     */
    private function getChildCount($region_id)
    {
        //$row = $this->db->selectrow('select count(*) as childCount from '.$this->table_name(1).' where p_region_id='.intval($region_id));
        $cnt = $this->model->count(array('p_region_id' => intval($region_id)));
        //return $row['childCount'];
        return $cnt;
    }
    
    /**
     * 得到地区信息 - parent region id， 层级，下级地区
     * @params string region id
     * @return array 指定信息的数组
     */
    public function getRegionById($regionId='')
    {
        //$sql='select region_id,p_region_id,local_name,ordernum,region_path from '.$this->table_name(1).' as r where r.p_region_id'.($regionId?('='.intval($regionId)):' is null').' order by ordernum asc,region_id asc';
        //$aTemp=$this->db->select($sql);
        if ($regionId)
            $aTemp = $this->model->getList('region_id,p_region_id,local_name,ordernum,region_path', array('p_region_id' => $regionId), 0, -1, 'ordernum ASC,region_id ASC');
        else
            $aTemp = $this->model->getList('region_id,p_region_id,local_name,ordernum,region_path', array('region_grade' => '1'), 0, -1, 'ordernum ASC,region_id ASC');
        
        if (is_array($aTemp)&&count($aTemp) > 0)
        {
            foreach($aTemp as $key => $val)
            {
                $aTemp[$key]['p_region_id']=intval($val['p_region_id']);
                $aTemp[$key]['step'] = intval(substr_count($val['region_path'],','))-1;
                $aTemp[$key]['child_count'] = $this->getChildCount($val['region_id']);
            }
        }
        
        return $aTemp;
    }
    
    /**
     * 得到地区的结构图
     * @params int parent region id
     * @return array 结构图数组
     */
    public function getMap($prId='')
    {
        if ($prId)
            $sql="select region_id,region_grade,local_name,ordernum,(select count(*) from ".$this->model->table_name(1)." where p_region_id=r.region_id) as child_count from ".$this->model->table_name(1)." as r where r.p_region_id=".intval($prId)." order by ordernum asc,region_id";
        else
            $sql="select region_id,region_grade,local_name,ordernum,(select count(*) from ".$this->model->table_name(1)." where p_region_id=r.region_id) as child_count from ".$this->model->table_name(1)." as r where r.p_region_id is null order by ordernum asc,region_id";
            
        $row = $this->model->db->select($sql);
        
        if (isset($row) && $row)
        {
            foreach ($row as $key => $val)
            {
                $this->regions[] = array(
                    "local_name"=>$val['local_name'],
                    "region_id"=>$val['region_id'],
                    "region_grade"=>$val['region_grade'],
                    "ordernum"=>$val['ordernum']
                );
                
                if ($val['child_count'])
                    $this->getMap($val['region_id']);
            }
        }
    }
    
    /**
     * 新建修改信息
     * @params array - 请求的数据信息
     * @params string - message
     */
    public function insertDlArea($aData,&$msg)
    {
        
        if (!trim($aData['local_name']))
        {
            $msg = app::get('ectools')->_('地区名称不能为空！');
            return false;
        }
        
        $aData['ordernum'] = $aData['ordernum'] ? $aData['ordernum'] : '50';
        if ($this->model->checkDlArea($aData['local_name'], $aData['p_region_id']))
        {
            $msg = app::get('ectools')->_('该地区名称已经存在！');
            return false;
        }
        
        //$tmp = $this->model->db->selectrow('select region_path from '.$this->model->table_name(1).' where region_id='.intval($aData['p_region_id']));
        $tmp = $this->model->dump(intval($aData['p_region_id']), 'region_path');
        if (!$tmp)
            $tmp['region_path'] = ",";
        
        $region_path = $tmp['region_path'];        
        $aData = array_filter($aData);
        
        if ($this->model->save($aData))
        {
            $regionId = $this->model->db->lastInsertId();
            $tmp = $this->model->dump($regionId, '*');
            $tmp['region_path'] = $region_path . $regionId . ',';
            $tmp['region_grade'] = count(explode(",", $tmp['region_path'])) - 2;
            if($this->model->save($tmp)) {
                return $this->updateRegionData();
            }
            return false;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * 更改地区信息
     * @params array - 新的地区的信息的数组
     * @params string - 更新结果消息
     */
    public function updateDlArea($aData,&$msg)
    {
        $is_save = false;
        
        if ($aData['region_id'] == $aData['p_region_id'])
        {
            $msg = app::get('ectools')->_('上级地区不能为本地区！');
            return false;
        }
        else
        {
            $idGroup = $this->model->getGroupRegionId($aData['region_id']);
            if ($idGroup)
            {
                if (in_array($aData['p_region_id'],$idGroup)){
                    $msg = app::get('ectools')->_('上级地区不能为本地区的子地区！');
                    return false;
                }
            }
        }
        
        if(!$aData['region_id'])
        {
            $msg = app::get('ectools')->_('参数丢失！');
            return false;
        }
        else
        {
            $cPath = $this->model->dump(intval($aData['region_id']), 'region_path');
        }
        
        if (!trim($aData['local_name']))
        {
            $msg = app::get('ectools')->_('地区名称不能为空！');
            return false;
        }
        
        if (intval($aData['p_region_id']))
        {
            $tmp = $this->model->dump(intval($aData['p_region_id']), 'region_path');
            $aData['region_path'] = $tmp['region_path'].$aData['region_id'].",";
        }
        else
        {
            $aData['region_path'] = ",".$aData['region_id'].",";
        }
        
        $aData['ordernum'] = isset($aData['ordernum']) ? $aData['ordernum'] : '50';
        $aData['region_grade'] = count(explode(",",$aData['region_path'])) - 2;
        $aData = array_filter($aData);
        $is_save = $this->model->save($aData);
        
        if(($is_save && $this->updateSubPath($cPath['region_path'],$aData['region_path']))) {
            return $this->updateRegionData();
        }
        return false;
    }
    
    /** 
     * 删除指定id的地区信息
     * @params int region id
     * @return boolean 删除成功与否
     */
    public function toRemoveArea($regionId)
    {
        //$tmpRow = $this->model->db->selectrow("select region_path from ".$this->table_name(1)." where region_id=".intval($regionId));
        $tmpRow = $this->model->dump(intval($regionId), 'region_path');
        
        //$this->db->exec("DELETE FROM ".$this->table_name(1)." where region_id=".intval($regionId));
        $this->model->delete(array('region_id' => intval($regionId)));
        // 删除相应的所有的下级地区
        if($this->toRemoveSubArea($tmpRow['region_path'])) {
            return $this->updateRegionData();
        }
        return false;
    }
    
    /**
     * 删除指定的级别的区域
     * @params string 层级字符串
     * @return boolean 删除是否成功
     */
    private function toRemoveSubArea($path)
    {
        if ($path)
        {
            return $this->model->db->exec("DELETE FROM ".$this->model->table_name(1)." where region_path LIKE '".$path."%'");
        }
    }
    
    /**
     * 更新下级地区的path值
     * @params string 上级的region_path
     * @params string 下一级地区的region_path
     * @return boolean 更新结果
     */
    private function updateSubPath($Opath,$Npath)
    {
        $offset = count(explode(",",$Npath)) - count(explode(",",$Opath));
        
        return $this->model->db->exec("update ".$this->model->table_name(1)." set region_path=replace(region_path,".$this->model->db->quote($Opath)
            .",".$this->model->db->quote($Npath)."),region_grade=region_grade + "
            .intval($offset)." where region_path LIKE '%".$Opath."%'");
    }

    private function getAllRegions($p_regionid='', $pkey='', &$region_count=array()) {
        $show_depth = $this->app->getConf('system.area_depth');
        if ($p_regionid)
            $sql="select region_id,region_grade,local_name,ordernum,(select count(*) from ".$this->model->table_name(1)." where p_region_id=r.region_id) as child_count from ".$this->model->table_name(1)." as r where r.p_region_id=".intval($p_regionid)." order by ordernum asc,region_id";
        else
            $sql="select region_id,region_grade,local_name,ordernum,(select count(*) from ".$this->model->table_name(1)." where p_region_id=r.region_id) as child_count from ".$this->model->table_name(1)." as r where r.p_region_id is null order by ordernum asc,region_id";
            
        $row = $this->model->db->select($sql);
        
        if (isset($row) && $row)
        {
            $cur_row = current($row);
            if(!$region_count[$cur_row['region_grade']]) {
                $start_index = 0;
            }
            else {
                $start_index = $region_count[$cur_row['region_grade']];
            }
            foreach ($row as $key => $val)
            {
                $tmp = array(
                    $val['local_name'],
                    $val['region_id'],
                );
                $index = $pkey!==''?$pkey:$key;
                if($val['child_count']) {
                    if($val['region_grade']<$show_depth) {
                        $tmp[] = $start_index;
                    }
                    $start_index++;
                    $region_count[$cur_row['region_grade']] = $start_index;
                }
                if($val['region_grade'] != 1) {
                    $this->regions[$val['region_grade']][$index][] = implode(":", $tmp);
                }
                else {
                    $this->regions[$val['region_grade']][$index] = implode(":", $tmp);
                }
                if ($val['child_count'] && $val['region_grade']<$show_depth) {
                    $this->getAllRegions($val['region_id'], $start_index-1, $region_count);
                }
            }
        }
    }
    
    /**
     * 更新region控件JS数据
     * @reutrn boolean
     */
    public function updateRegionData() {
        $this->getAllRegions();
        $this->regions = (array)$this->regions;
        
        $regions = array();
        foreach($this->regions as $v) {
            $regions[] = $v;
        }
        unset($this->regions);
        
        // 文件数据
        $str = "var region_Data='".json_encode($regions)."';\r\n";
        $str .= "region_Data = JSON.decode(region_Data);";
        if(ECAE_MODE){
            $this->app->setConf('system.region_data',$str);
        }else{
            // 文件名字
            $filename = dirname(__FILE__).'/../../statics/js/region_data.js';
            $handle = fopen($filename, "w");
            if(!$handle) {
                fclose($handle);
                return false;
            }
            if(!fwrite($handle, $str)) {
                fclose($handle);
                return false;
            }
            fclose($handle);
        }
        return true;
    }
}
