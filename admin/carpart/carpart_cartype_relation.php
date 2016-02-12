<?php
/**
 * Created by PhpStorm.
 * User: liuhao
 * Date: 2016/1/29
 * Time: 10:25
 */
header("Content-Type: text/html;charset=utf-8");
include_once("model/basesql.php");


$tablename="shopnc_carpart_cartype";
$id_field="oe,uid";
$link = getSql();
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if ($_GET['action'] == 'edit') {



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
        $sqlstr.=" where oe='".$_POST['oe']."' and uid='".$_POST['uid']."'";
        $result = mysqli_query($link, $sqlstr);
        if(FALSE == $result)
        {
            echo "Querry failed!";
        }


        return;
    } else if ($_GET['action'] == 'del') {
        $sqlstr = "delete from ".$tablename." where oe='".$_POST['oe']."' and uid='".$_POST['uid']."'";
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

$head = array(array('name' => 'oe', 'editable' => 1, 'display' => 1, 'showname' => 'OE号', 'type' => ''),
    array('name' => 'uid', 'editable' => 1, 'display' => 1, 'showname' => '车型UID（主要为车架号）', 'type' => ''),
);

$columnNum = count($head);
$edit_url = "carpart_houtai.php?model=".$tablename."&action=edit";
$del_url =  "carpart_houtai.php?model=".$tablename."&action=del";


include("table.php");