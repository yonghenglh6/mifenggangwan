<?php
/**
 * Created by PhpStorm.
 * User: liuhao
 * Date: 2016/1/29
 * Time: 6:14
 */


require '../data/config.php';


function getSql()
{
    global $db_host2;
    global $db_name;
    global $db_user;
    global $db_pass;
    $mysql_server_name = $db_host2; //数据库服务器名称
    $mysql_username = $db_user; // 连接数据库用户名
    $mysql_password = $db_pass; // 连接数据库密码
    $mysql_database = $db_name; // 数据库的名字

    $link = mysqli_connect(
        $mysql_server_name, /* The host to connect to 连接MySQL地址 */
        $mysql_username,   /* The user to connect as 连接MySQL用户名 */
        $mysql_password, /* The password to use 连接MySQL密码 */
        $mysql_database);  /* The default database to query 连接数据库名称*/

    if (!$link) {
        printf("Can't connect to MySQL Server. Errorcode: %s ", mysqli_connect_error());
        exit;
    }

    mysqli_query($link,"SET NAMES 'UTF8'");
    mysqli_query($link,"SET CHARACTER SET UTF8");
    mysqli_query($link,"SET CHARACTER_SET_RESULTS=UTF8'");
    return $link;
}


function getRs($result){
    $head =array();
    $content=array();
	if(!$result){
		echo "result not found.";
		return $content;
	}
    $fields_num = mysqli_num_fields($result);

// printing table rows
    while($row = mysqli_fetch_row($result))
    {
        $content[]=$row;
    }
    mysqli_free_result($result);

    return $content;
}
?>