<?php
/**
 * Created by PhpStorm.
 * User: liuhao
 * Date: 2016/1/29
 * Time: 10:13
 */



require('BasePage.php');
class Cartype extends BasePage{
    function init(){
        parent::init();
        $this->tablename="shopnc_cartype_detail";
        $this->id_field="did";
/*        $this->head = array(array('name' => 'did', 'editable' => 0, 'display' => 1, 'showname' => 'ID', 'type' => ''),
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
		*/
		
		$this->head = array(array('name' => 'did', 'editable' => 0, 'display' => 1, 'showname' => 'ID', 'type' => ''),
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
        );
        $this->edit_url = "cartype.php?action=edit";
        $this->del_url =  "cartype.php?action=del";
        $this->add_url =  "cartype.php?action=add";
    }

}

$mclass=new Cartype();
$mclass->init();
$mclass->execute();
