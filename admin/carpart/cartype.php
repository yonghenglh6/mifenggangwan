<?php
/**
 * Created by PhpStorm.
 * User: liuhao
 * Date: 2016/1/29
 * Time: 10:13
 */
header("Content-Type: text/html;charset=utf-8");
include_once("model/basesql.php");


$tablename="shopnc_cartype_detail";
$id_field="did";
$link = getSql();
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if ($_GET['action'] == 'edit') {
        if ($_POST[$id_field] == '-1') {
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
            $sqlstr.=" where ".$id_field."='".$_POST[$id_field]."'";
            $result = mysqli_query($link, $sqlstr);
            if(FALSE == $result)
            {
                echo "Querry failed!";
            }
        }

        return;
    } else if ($_GET['action'] == 'del') {
        $sqlstr = "delete from ".$tablename." where ".$id_field." = '".$_POST[$id_field]."'";
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

$head = array(array('name' => 'did', 'editable' => 0, 'display' => 1, 'showname' => 'ID', 'type' => ''),
    array('name' => 'uid', 'editable' => 1, 'display' => 1, 'showname' => '子标识', 'type' => ''),
    array('name' => 'spid', 'editable' => 1, 'display' => 1, 'showname' => '父标识', 'type' => ''),
    array('name' => 'serial', 'editable' => 1, 'display' => 1, 'showname' => '系列', 'type' => ''),
    array('name' => 'name', 'editable' => 1, 'display' => 1, 'showname' => '名字', 'type' => ''),
    array('name' => 'ename', 'editable' => 1, 'display' => 1, 'showname' => '标准名字', 'type' => ''),
    array('name' => 'parentid', 'editable' => 1, 'display' => 1, 'showname' => '父车型标识', 'type' => ''),
    array('name' => 'engine', 'editable' => 1, 'display' => 1, 'showname' => '引擎', 'type' => ''),
    array('name' => 'pailiang', 'editable' => 1, 'display' => 1, 'showname' => '排量', 'type' => ''),
    array('name' => 'gonglv', 'editable' => 1, 'display' => 1, 'showname' => '功率', 'type' => ''),
    array('name' => 'gang', 'editable' => 1, 'display' => 1, 'showname' => '缸', 'type' => ''),
    array('name' => 'leixing', 'editable' => 1, 'display' => 1, 'showname' => '类型', 'type' => ''),
    array('name' => 'syear', 'editable' => 1, 'display' => 1, 'showname' => '起始日期', 'type' => ''),
    array('name' => 'eyear', 'editable' => 1, 'display' => 1, 'showname' => '终止日期', 'type' => ''),
    array('name' => 'href', 'editable' => 1, 'display' => 1, 'showname' => '链接', 'type' => ''),
);
$columnNum = count($head);
$edit_url = "carpart_houtai.php?model=".$tablename."&action=edit";
$del_url =  "carpart_houtai.php?model=".$tablename."&action=del";


include("table.php");