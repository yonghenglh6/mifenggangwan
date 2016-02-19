<?php

/**
 * Created by PhpStorm.
 * User: liuhao
 * Date: 2016/2/19
 * Time: 2:49
 */
class BasePage
{

    public $sqllink;
    public $tablename;
    public $id_field;
    public $head;

    public $edit_url;
    public $del_url;
    public $add_url;

    public $aikey;
    function init(){

        $this->edit_url = $this->tablename.".php?action=edit";
        $this->del_url =  $this->tablename.".php?action=del";
        $this->add_url = $this->tablename.".php?action=add";
        $this->id_field="oe";
        $this->aikey="";
    }

    function showPage(){
        $page = 1;
        if (isset($_GET["page"])) {
            $page = intval($_GET["page"]);
        }

        $wheresql=" ";
        if (isset($_GET["sfield"])&&isset($_GET["svalue"])) {
            if($_GET["sfield"]!='请选择列'){
                $wheresql=" where ".$_GET["sfield"]."='".$_GET["svalue"]."' ";
            }
        }


//获取总页数
        $sqlstr = "select * from ".$this->tablename.$wheresql." limit " . (($page - 1) * 50) . ",50";

        $result = mysqli_query($this->sqllink, $sqlstr);
        $data = getRs($result);
        $content = array();
        foreach ($data as $orow) {
            $nrow = $orow;
//    $nrow[7] = $orow[7] == '1' ? '是' : '否';
//    $nrow[8] = $orow[8] == '1' ? '是' : '否';
//    $nrow[9] = $orow[9] == '1' ? '是' : '否';

            $content[] = $nrow;
        }
        $columnNum = count($this->head);

        $tablename=$this->tablename;
        $head=$this->head;
        $edit_url=$this->edit_url;
        $del_url=$this->del_url;
        $add_url=$this->add_url;

        include("table.php");
    }

    function execute(){
        require("model/basesql.php");

        $this->sqllink = getSql();

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if ($_GET['action'] == 'add') {
                //添加

                return $this->operateAdd();
            } else if ($_GET['action'] == 'edit') {
                //修改

                return $this->operateEdit();

            } else if ($_GET['action'] == 'del') {
                //删除

                return $this->operateDelete();
            }

            return;
        }

        $this->showPage();
    }



    function getData(){


    }

    function filterData(){

    }

    function mapData(){

    }

    function operateEdit(){
        $sqlstr = "update ".$this->tablename." set ";
        $begin=true;;
        foreach($_POST as $key => $value){

            if($begin){
                $begin=false;
                $sqlstr=$sqlstr."".$key."='".$value."'";
            }else{
                $sqlstr=$sqlstr.",".$key."='".$value."'";
            }
        }
        $sqlstr.=" where ".$this->id_field."='".$_POST[$this->id_field]."'";
        $result = mysqli_query($this->sqllink, $sqlstr);
        if(FALSE == $result)
        {
            echo "Querry failed!";
        }else{
            echo "修改成功";
        }
    }
    function operateAdd(){

        $sqlstr = "insert into ".$this->tablename." ";
        $keylist="";
        $valuelist="";
        $begin=true;;
        foreach($_POST as $key => $value){
            if($key!=$this->aikey){
            if($begin){
                $begin=false;
                $keylist=$keylist."`".$key."`";
                $valuelist=$valuelist."'".$value."'";
                //$sqlstr=$sqlstr."".$key."='".$value."'";
            }else{
                $keylist=$keylist.",`".$key."`";
                $valuelist=$valuelist.",'".$value."'";
                //$sqlstr=$sqlstr.",".$key."='".$value."'";
            }}
        }
        $sqlstr.=" (".$keylist.") values (".$valuelist.")";
        $result = mysqli_query($this->sqllink, $sqlstr);
        if(FALSE == $result)
        {
            echo "添加失败";
        }else{
            echo "修改成功";
        }
    }
    function operateDelete(){
        $sqlstr = "delete from ".$this->tablename." where ".$this->id_field." = '".$_POST[$this->id_field]."'";
        $result = mysqli_query($this->sqllink, $sqlstr);
        if(FALSE == $result)
        {
            echo "Querry failed!";
        }
        echo "删除成功";
    }
}