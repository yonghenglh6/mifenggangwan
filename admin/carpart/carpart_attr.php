<?php
/**
 * Created by PhpStorm.
 * User: liuhao
 * Date: 2016/1/29
 * Time: 10:25
 */


require('BasePage.php');
class CarpartAttr extends BasePage{
    function init(){
        parent::init();
        $this->tablename="carpart_attr";
        $this->id_field="oe";
        $this->head = array(array('name' => 'oe', 'editable' => 1, 'display' => 1, 'showname' => 'OE号', 'type' => ''),
            array('name' => 'attr', 'editable' => 1, 'display' => 1, 'showname' => '属性信息', 'type' => ''),
            array('name' => 'info', 'editable' => 1, 'display' => 1, 'showname' => '附加信息', 'type' => ''),
            array('name' => 'mode', 'editable' => 1, 'display' => 1, 'showname' => '模式', 'type' => ''),
            array('name' => 'class', 'editable' => 1, 'display' => 1, 'showname' => '大类别', 'type' => ''),
            array('name' => 'aggtyp', 'editable' => 1, 'display' => 1, 'showname' => 'AGGTYPE', 'type' => ''),
            array('name' => 'catalog', 'editable' => 1, 'display' => 1, 'showname' => '类别', 'type' => ''),
            array('name' => 'model', 'editable' => 1, 'display' => 1, 'showname' => '车架号', 'type' => ''),
            array('name' => 'spmno', 'editable' => 1, 'display' => 1, 'showname' => 'SPMNO', 'type' => ''),
            array('name' => 'group', 'editable' => 1, 'display' => 1, 'showname' => '机组', 'type' => ''),
            array('name' => 'subgrp', 'editable' => 1, 'display' => 1, 'showname' => '组别', 'type' => ''),
        );
        $this->edit_url = "carpart_attr.php?action=edit";
        $this->del_url =  "carpart_attr.php?action=del";


    }
    function showPage(){
        return parent::showPage();
    }
    function execute()
    {
        return parent::execute();
    }
}

$mclass=new CarpartAttr();
$mclass->init();
$mclass->execute();

