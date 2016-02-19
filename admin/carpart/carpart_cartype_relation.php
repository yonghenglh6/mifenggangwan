<?php
/**
 * Created by PhpStorm.
 * User: liuhao
 * Date: 2016/1/29
 * Time: 10:25
 */




require('BasePage.php');
class CarpartCartypeRelation extends BasePage{
    function init(){
        parent::init();
        $this->tablename="shopnc_carpart_cartype";
        $this->id_field="oe,uid";
        $this->head = array(array('name' => 'oe', 'editable' => 1, 'display' => 1, 'showname' => 'OE号', 'type' => ''),
            array('name' => 'uid', 'editable' => 1, 'display' => 1, 'showname' => '车型UID（主要为车架号）', 'type' => ''),
        );
        $this->edit_url = "carpart_cartype_relation.php?action=edit";
        $this->del_url =  "carpart_cartype_relation.php?action=del";
        $this->add_url =  "carpart_cartype_relation.php?action=add";
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
        $sqlstr.=" where oe='".$_POST['oe']."' and uid='".$_POST['uid']."'";
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
        $sqlstr = "delete from ".$this->tablename." where oe='".$_POST['oe']."' and uid='".$_POST['uid']."'";
        $result = mysqli_query($this->link, $sqlstr);
        if(FALSE == $result)
        {
            echo "Querry failed!";
        }
        echo "删除成功";
    }

}

$mclass=new CarpartCartypeRelation();
$mclass->init();
$mclass->execute();