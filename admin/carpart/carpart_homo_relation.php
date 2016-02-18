<?php
/**
 * Created by PhpStorm.
 * User: liuhao
 * Date: 2016/1/29
 * Time: 10:25
 */



require('BasePage.php');
class CarpartHomoRelation extends BasePage{
    function init(){
        parent::init();
        $this->tablename="carpart_homo_realation";
        $this->id_field="oe1,oe2";
        $this->head = array(array('name' => 'oe1', 'editable' => 1, 'display' => 1, 'showname' => 'OE号', 'type' => ''),
            array('name' => 'oe2', 'editable' => 1, 'display' => 1, 'showname' => '关联的OE号', 'type' => ''),
            array('name' => 'relation', 'editable' => 1, 'display' => 1, 'showname' => '关系', 'type' => ''),

        );
        $this->edit_url = "carpart_homo_relation.php?action=edit";
        $this->del_url =  "carpart_homo_relation.php?action=del";

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
        $sqlstr.=" where oe1='".$_POST['oe1']."' and oe2='".$_POST['oe2']."'";
        $result = mysqli_query($this->link, $sqlstr);
        if(FALSE == $result)
        {
            echo "Querry failed!";
        }

    }
    function operateAdd(){
        echo "添加成功";
    }
    function operateDelete(){
        $sqlstr = "delete from ".$this->tablename." where oe1='".$_POST['oe1']."' and oe2='".$_POST['oe2']."'";
        $result = mysqli_query($this->link, $sqlstr);
        if(FALSE == $result)
        {
            echo "Querry failed!";
        }
        echo "删除成功";
    }

}

$mclass=new CarpartHomoRelation();
$mclass->init();
$mclass->execute();

