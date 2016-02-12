<?php

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/*
*  代码增加_start By morestock_morecity
*   获取排列组合后的新数组
*/
function get_pailie_zuhe($auto_attr_temp)
{

if (empty($auto_attr_temp))
{
	return array();
}
foreach ($auto_attr_temp as $attribute_value)
{
        //转换成数组
        $CombinList[$attribute_value['attr_name']][] = array('goods_attr_id'=>$attribute_value['goods_attr_id'], 'attr_value'=>$attribute_value['attr_value']);
}

//echo '<pre>';
//print_r ($CombinList);
//echo '</pre>';

$CombineCount = 1;
foreach($CombinList as $Key => $Value)
{
    $CombineCount *= count($Value);
}

$RepeatTime = $CombineCount;
foreach($CombinList as $ClassNo => $StudentList)
{
    // $StudentList中的元素在拆分成组合后纵向出现的最大重复次数
    $RepeatTime = $RepeatTime / count($StudentList);

    $StartPosition = 1;

    // 开始对每个数组的值进行循环
    foreach($StudentList as $Student)
    {
        $TempStartPosition = $StartPosition;

        $SpaceCount = $CombineCount / count($StudentList) / $RepeatTime;

        for($J = 1; $J <= $SpaceCount; $J ++)
        {
            for($I = 0; $I < $RepeatTime; $I ++)
            {
               $Result[$TempStartPosition + $I][$ClassNo] = $Student;
            }
            $TempStartPosition += $RepeatTime * count($StudentList);
        }
        $StartPosition += $RepeatTime;
    }
}

/* 打印结果 */
//echo "<pre>";
//print_r($Result);
//echo "</pre>";
return $Result;

}


/* 代码增加_end By morestock_morecity */
?>