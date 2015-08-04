<?php

 
class dev_finder_apps{

    var $actions = array(
        array('label'=>'维护','href'=>"index.php?app=desktop&ctl=appmgr&act=maintenance",'target'=>'command::{title:\'维护\'}')
        );

/*
    var $addon_cols='local_ver,remote_ver,status,app_id';
    var $column_tools='操作';
    var $column_tools_width='150';
    function column_tools($row){
       
    }*/

    var $detail_service='Services';
    function detail_service($app){
        $html = '<table><tr style="border-bottom:1px solid #9DAFC3"><th width="35px">&nbsp;</th><th width="100px">Service</th><th>Class</th></tr>';
        foreach(kernel::single('base_application_service')->detect($app) as $name=>$item){
            foreach((array)$item->current['class'] as $class){
                $bgcolor = ($i++ %2 ==0 )?'#F4F7FB':'#fff';
                $html.="<tr style='background:{$bgcolor}'><td>&nbsp;{$i}</td><td style='padding-right:10px'>{$name}</td><td>{$class}</td></tr>";
            }
        }
        return $html.'</table>';
    }

    var $detail_dbtable='DB tables';
    function detail_dbtable($app){
        $db = kernel::database();
        $rows = $db->select('SHOW TABLE STATUS like "'.$db->prefix.$app.'%"');
        $len = strlen($db->prefix.$app)+1;
        foreach($rows as $row){
            $tableinfo[substr($row['Name'],$len)] = array(
                'Engine'=>$row['Engine'],
                'Type'=>$row['Row_format'],
                'Rows'=>$row['Rows'],
                'Data'=>$row['Data_length'],
                'Index'=>$row['Index_length'],
                );
        }
        
        $html = '<table><tr style="border-bottom:1px solid #9DAFC3"><th width="35px">&nbsp;</th><th width="250px">Table</th><th width="100px">'.app::get('dev')->_('查看').'</th>
                <th width="70px">Engine</th>
                <th width="70px">Type</th>
                <th width="50px">Rows</th>
                <th width="50px">Data</th>
                <th width="50px">Index</th>
                <th>&nbsp;</th></tr>';
                
        foreach(kernel::single('base_application_dbtable')->detect($app) as $name=>$item){
            $bgcolor = ($i++ %2 ==0 )?'#F4F7FB':'#fff';
            $html.="<tr style='background:{$bgcolor}'><td>&nbsp;{$i}</td>
            <td style='padding-right:10px'>".$item->real_table_name().'</td>'
            ."<td><span class=\"lnk\" onclick=\"new Dialog('index.php?app=dev&ctl=tools&act=tablesql&tableapp={$app}&tablename={$name}')\">SQL</span>
            &nbsp;&nbsp;<span class=\"lnk\" onclick=\"new Dialog('index.php?app=dev&ctl=tools&act=tablesrc&tableapp={$app}&tablename={$name}')\">PHP</span>
            </td>"
            ."<td>{$tableinfo[$name]['Engine']}</td>
            <td>{$tableinfo[$name]['Type']}</td>
            <td>{$tableinfo[$name]['Rows']}</td>
            <td>{$tableinfo[$name]['Data']}</td>
            <td>{$tableinfo[$name]['Index']}</td>"
            .'<td></td></tr>';
        }
        return $html.'</table>';
    }
    
/*
    var $detail_info='service';
    function detail_info($id){
        echo 'service';
    }

    var $detail_info='service';
    function detail_info($id){
        echo 'service';
    }*/

}
