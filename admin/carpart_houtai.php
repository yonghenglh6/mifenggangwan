<?php
/**
 * Created by PhpStorm.
 * User: liuhao
 * Date: 2016/2/1
 * Time: 2:56
 */


define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php');
require_once(ROOT_PATH . '/' . ADMIN_PATH. "/nusoap/nusoap.php");   //代码增加  By  www.68ecshop.com
include_once(ROOT_PATH . '/includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);

function showInAlert($msg){
	//echo('qq');
	echo('<script type=\'text/javascript\'>alert("'.$msg.'");</script>');
}

if ($_REQUEST['act'] == 'main')
{
  //echo('<script type=\'text/javascript\'>alert(2);</script>');
    $smarty->display('carpart_houtai.htm');
}else if(isset($_REQUEST['model'])){
	echo("qqqq");
	echo('/admin/carpart/'.$_REQUEST['model'].'.php');
	//echo('<script type=\'text/javascript\'>alert(2);</script>');
    include('carpart/'.$_REQUEST['model'].'.php');
	echo("2");
}
