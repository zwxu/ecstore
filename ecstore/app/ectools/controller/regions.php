<?php


class ectools_ctl_regions extends desktop_controller{

    var $workground = 'ectools_ctl_regions';

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $this->showarea();
        /*
        $this->finder('ectools_mdl_regions',array(
            'title'=>'地区管理',
            'actions'=>array(array(
                'label'=>'安装新支付接口',
                'target'=>'_blank',
            ))));*/
    }

    /**
     * 展示所有地区
     * @params null
     * @return null
     */
    private function showarea()
    {
        //$dArea = $this->app->model('regions');
        $obj_regions_op = kernel::service('ectools_regions_apps', array('content_path'=>'ectools_regions_operation'));
        $this->path[]=array('text'=>app::get('ectools')->_('配送地区列表'));

        if ($obj_regions_op->getTreeSize())
        {
            //超过100条
            $this->pagedata['area'] = $obj_regions_op->getRegionById();
            $this->page('delivery/area_treeList.html');
        }
        else
        {
            $obj_regions_op->getMap();
            $this->pagedata['area'] = $obj_regions_op->regions;
            $this->page('delivery/area_map.html');
        }
    }

    public function showRegionTreeList($serid,$multi=false,$textid=null,$hiddenid=null)
    {
         if ($serid)
         {
            $this->pagedata['sid'] = $serid;
         }
         else
         {
            $this->pagedata['sid'] = substr(time(),6,4);
         }
         if($textid && $hiddenid){
         	$this->pagedata['textid'] = $textid;
         	$this->pagedata['hiddenid'] = $hiddenid;
         }
         $this->pagedata['multi'] =  $multi;
         $this->singlepage('delivery/regionSelect.html');
    }

    public function getChildNode()
    {
        //$dArea = $this->app->model('regions');
        $obj_regions_op = kernel::service('ectools_regions_apps', array('content_path'=>'ectools_regions_operation'));
        $this->pagedata['area'] = $obj_regions_op->getRegionById($_POST['regionId']);
        $this->display('delivery/area_sub_treeList.html');
    }

    public function getRegionById($pregionid)
    {
        //$oDlyType = &$this->app->model('regions');
        $obj_regions_op = kernel::service('ectools_regions_apps', array('content_path'=>'ectools_regions_operation'));
        echo json_encode($obj_regions_op->getRegionById($pregionid));
    }

    /**
     * 添加新地区界面
     * @params string 父级region id
     * @return null
     */
    public function showNewArea($pRegionId=null)
    {
        if ($pRegionId){
            $dArea = $this->app->model('regions');
            $this->pagedata['parent'] = $dArea->getRegionByParentId($pRegionId);
        }
        $this->path[] = array('text'=>app::get('ectools')->_('添加配送地区'));
        $this->display('delivery/area_new.html');
    }

    /**
     * 添加新地区
     * @params null
     * @return null
     */
    public function addDlArea()
    {
        $this->begin('index.php?app=ectools&ctl=regions&act=index');
        $obj_regions_op = kernel::service('ectools_regions_apps', array('content_path'=>'ectools_regions_operation'));
        if(!$obj_regions_op->insertDlArea($_POST,$msg)){
            $this->end(false, app::get('ectools')->_('新建失败，').$msg);
        }else
            $this->end(true, app::get('ectools')->_('新建成功，地区名称：').$_POST['local_name']);

    }

    /**
     * 修改地区信息的入口
     * @params null
     * @return null
     */
    public function saveDlArea()
    {
        $this->begin('index.php?app=ectools&ctl=regions&act=index');
        $obj_regions_op = kernel::service('ectools_regions_apps', array('content_path'=>'ectools_regions_operation'));
        if(!$obj_regions_op->updateDlArea($_POST,$msg)){
            $this->end(false, app::get('ectools')->_('修改失败，').$msg);
        }
        else
        {
            $this->end(true, app::get('ectools')->_('修改成功，地区名称：').$_POST['local_name']);
        }
    }

    /**
     * 编辑显示页面
     * @params string 地区的regions id
     * @return null
     */
    public function detailDlArea($aRegionId)
    {
        $this->path[] = array('text'=>app::get('ectools')->_('配送地区编辑'));
        $oObj = $this->app->model('regions');
        $this->pagedata['area'] = $oObj->getDlAreaById($aRegionId);
        $this->display('delivery/area_edit.html');
    }

    /**
     * 删除对应regions id 的地区
     * @params string region id
     * @return null
     */
    public function toRemoveArea($regionId)
    {
        $this->begin('index.php?app=ectools&ctl=regions&act=index');
        $obj_regions_op = kernel::service('ectools_regions_apps', array('content_path'=>'ectools_regions_operation'));
        if ($obj_regions_op->toRemoveArea($regionId))
            $this->end(true,app::get('ectools')->_('删除地区成功！'));
        else
            $this->end(false,app::get('ectools')->_('删除地区失败！'));
    }

    /**
     * 更新地区排序数据
     * @params null
     * @return null
     */
    public function updateOrderNum()
    {
        $this->begin('index.php?app=ectools&ctl=regions&act=index');

        $is_update = true;
        $dArea = $this->app->model('regions');
        $arrPOdr = $_POST['p_order'];

        $arrRegions = array();
        if ($arrPOdr)
        {
            foreach ($arrPOdr as $key=>$strPOdr)
            {
                $arrdArea = $dArea->dump($key, 'region_id,ordernum');
                $arrdArea['ordernum'] = $strPOdr ? $strPOdr : '50';
                $arrRegions[] = $arrdArea;
            }
        }

        if ($arrRegions)
        {
            foreach ($arrRegions as $arrRegionsinfo)
            {
                $is_update = $dArea->save($arrRegionsinfo);
            }
        }

        if($is_update) {
            $is_update = kernel::single('ectools_regions_operation')->updateRegionData();
        }

        $this->end($is_update,app::get('ectools')->_('排序成功！'));
    }

    public function selRegion()
    {
        $path = $_GET['path'];
        $depth = $_GET['depth'];

        header('Content-type: text/html;charset=utf8');
        //$local = $this->app->model('regions');
        $local = kernel::single('ectools_regions_select');
        $ret = $local->get_area_select($this->app,$path,array('depth'=>$depth));

        if($ret)
        {
            echo '&nbsp;-&nbsp;'.$ret;
        }
        else
        {
            echo '';
        }
    }
}
