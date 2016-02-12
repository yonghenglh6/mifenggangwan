<?php
/**
 * Created by PhpStorm.
 * User: liuhao
 * Date: 2016/1/29
 * Time: 7:25
 */
//header("Content-Type: text/html;charset=utf-8");

include_once("model/basesql.php");


$tablename="carpart";


function getData(){
	
	
}

function filterData(){
	
}

function mapData(){
	
}

function operateEdit(){
	
}
function operateSave(){
	
}
function operateDelete(){
	
}


$link = getSql();
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if ($_GET['action'] == 'edit') {
        if ($_POST['id'] == '-1') {
            echo "添加成功";

        } else {

            $sqlstr = "update ".$tablename." set ";
            $begin=true;;
            foreach($_POST as $key => $value){

                if($begin){
                    $begin=false;
                    $sqlstr=$sqlstr."".$key."='".$value."'";
                }else{
                    $sqlstr=$sqlstr.",".$key."='".$value."'";
                }
            }
            $sqlstr.=" where id='".$_POST['id']."'";
            $result = mysqli_query($link, $sqlstr);
            if(FALSE == $result)
            {
                echo "Querry failed!";
            }
        }

        return;
    } else if ($_GET['action'] == 'del') {
        $sqlstr = "delete from ".$tablename." where id = '".$_POST['id']."'";
        $result = mysqli_query($link, $sqlstr);
        if(FALSE == $result)
        {
            echo "Querry failed!";
        }
        echo "删除成功";
        return;
    }
    return;
}



$page = 1;
if (isset($_GET["page"])) {
    $page = intval($_GET["page"]);
}

//获取总页数


$sqlstr = "select * from ".$tablename." limit " . (($page - 1) * 1000) . ",1000";

$result = mysqli_query($link, $sqlstr);
$data = getRs($result);
$content = array();
foreach ($data as $orow) {
    $nrow = $orow;
//    $nrow[7] = $orow[7] == '1' ? '是' : '否';
//    $nrow[8] = $orow[8] == '1' ? '是' : '否';
//    $nrow[9] = $orow[9] == '1' ? '是' : '否';

    $content[] = $nrow;
}

$head = array(array('name' => 'id', 'editable' => 0, 'display' => 1, 'showname' => 'ID', 'type' => ''),
    array('name' => 'oe', 'editable' => 1, 'display' => 1, 'showname' => 'OE号', 'type' => ''),
    array('name' => 'name', 'editable' => 1, 'display' => 1, 'showname' => '名字', 'type' => ''),
    array('name' => 'ename', 'editable' => 1, 'display' => 1, 'showname' => '标准名字', 'type' => ''),
    array('name' => 'defaultimg', 'editable' => 1, 'display' => 1, 'showname' => '默认图片', 'type' => ''),
    array('name' => 'cateid', 'editable' => 1, 'display' => 1, 'showname' => '类别id', 'type' => ''),
    array('name' => 'source', 'editable' => 1, 'display' => 1, 'showname' => '来源', 'type' => ''),
    array('name' => 'isfrom_orifactory', 'editable' => 1, 'display' => 1, 'showname' => '是否原厂件', 'type' => ''),
    array('name' => 'isfrom_homofactory', 'editable' => 1, 'display' => 1, 'showname' => '是否同质件', 'type' => ''),
    array('name' => 'isfrom_abroad', 'editable' => 1, 'display' => 1, 'showname' => '是否海外件', 'type' => ''),
    array('name' => 'factory_href', 'editable' => 1, 'display' => 1, 'showname' => '原厂链接', 'type' => ''),
);
$columnNum = count($head);

$edit_url = "carpart_houtai.php?model=".$tablename."&action=edit";
$del_url =  "carpart_houtai.php?model=".$tablename."&action=del";

//$edit_url = "".$tablename.".php?action=edit";
//$del_url = "".$tablename.".php?action=del";


include("table.php");





