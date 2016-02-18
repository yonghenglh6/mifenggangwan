
<script type="text/javascript" src="jquery-1.12.0.min.js" ></script>
<script type="text/javascript" src="editable.js" ></script>
<link href="general.css" rel="stylesheet" type="text/css"/>


<?php

?>


<script type="text/javascript">


    function changeRow($tr,url){
        var data={};
        $tds = $tr.find("td");
        $.each($tds.filter(":lt(" + ($tds.size() - 1) + ")"), function (i, td) {
            var c = $(td).children().get(0);
            if (c != null) {
                if (c.tagName.toLowerCase() == "select") {
                    data[ $(td).attr('name')]=c.options[c.selectedIndex].text;
//                    data = data + "&" + $(td).attr('name') + "=" + c.options[c.selectedIndex].text;
                }
                else if (c.tagName.toLowerCase() == "input") {
                    data[$(td).attr('name')]=c.value;
//                    data = data + "&" + $(td).attr('name') + "=" + c.value;
                }
            }else{
                data[$(td).attr('name')]=$(td).text();
//                data = data + "&" + $(td).attr('name') + "=" + $(td).text();
            }
            /* 可以在此处扩展input、select以外的元素确认行为 */
        });

//        alert("<?php //echo $edit_url?>//"+data);
        $.ajax({
            type: "post",
            url: url,
            data:data,
            success: function (result) {
                alert(result);
            },
            error:function (result) {

                alert("修改失败。");
                location.reload();
            }
        });
    }
    $(function () {
        $("#tt1").editable({
            head: true,
            noeditcol: [ <?php
            $begin=true;
            for($i=0;$i<$columnNum;$i=$i+1){
                if($head[$i]['editable']==0)
                    if($begin){
                        echo $i;
                        $begin=false;
                    }else{
                    echo ','.$i;
                }
                }
            ?>  ]
            ,
            editcol: [

                <?php
                $begin=true;
                for($i=0;$i<$columnNum;$i=$i+1){
                    if($head[$i]['editable']==1)
                        if($begin){
                            echo ' { colindex: '.$i.' }';
                    $begin=false;
                        } else{

                            echo ','.' { colindex: '.$i.' }';
                        }
                }
                ?>
            ],
            onok: function ($tr) {
                changeRow($tr,"<?php echo $edit_url?>");
                return true; //返回false表示失败，dom元素不会有变化
            },
            ondel: function ($tr) {
                changeRow($tr,"<?php echo $del_url?>");
                return true; //返回false表示成功，dom元素相应有变化
            }
        });
    });
</script>









<form method="post" action="" name="listForm" onsubmit="return confirmSubmit(this)">
    <!-- start goods list -->
    <div class="list-div" id="listDiv" >


        <!-- 分页 -->
        <table id="page-table" cellspacing="0">
            <tbody><tr>
                <td align="right" nowrap="true">
                    <!-- $Id: page.htm 14216 2008-03-10 02:27:21Z testyang $ -->
                    <div id="turn-page">
                        当前第 <span id="pageCurrent"><?php echo $page;?></span>
                        页，每页 50个
        <span id="page-link">


          <a href="javascript:gotoPageFirst()">第一页</a>
          <a href="javascript:gotoPagePrev()">上一页</a>
          <a href="javascript:gotoPageNext()">下一页</a>
                    </div>
                </td>
            </tr>
            </tbody></table>

        <table cellpadding="3" cellspacing="1" id="tt1"  border="1">
            <tbody><tr>


                <?php
                foreach($head as $onehead){
                    ?>
                    <th><a href="javascript:listTable.sort('goods_sn');"><?php echo $onehead['showname'] ;?></a></th>

                    <?php
                }
                ?>
            </tr>


                <?php
                foreach($content as $onerow){
                    ?>

            <tr>
                <?php


                for($i=0;$i<$columnNum;$i=$i+1){

                    ?>
                    <td  name="<?php echo $head[$i]['name'] ?>" style="background-color: rgb(255, 255, 255);"><?php echo $onerow[$i] ?></td>

                    <?php
                }
                ?>


            </tr>
                    <?php
                }
                ?>
</tbody></table>


        <script type="text/javascript">
            var cpage=<?php echo $page?>;
            function goToPage(apage){
                var reg = new RegExp("[?&](page=).*?(&|$)");
                var ourl=location.href.replace(reg,'');
                if(ourl.indexOf("?")!=-1){
                    window.location.href=ourl+"&page="+apage;
                }else{
                    window.location.href=ourl+"?page="+apage;
                }

            }
            function gotoPageFirst(){
                goToPage(1);

            }
            function gotoPagePrev(){
                if(cpage>1)
                    cpage=cpage-1;
                goToPage(cpage);
            }
            function gotoPageNext(){
                cpage=cpage+1;
                goToPage(cpage);
            }
        </script>


            <!-- 分页 -->
            <table id="page-table" cellspacing="0">
                <tbody><tr>
                    <td align="right" nowrap="true">
                        <!-- $Id: page.htm 14216 2008-03-10 02:27:21Z testyang $ -->
                        <div id="turn-page">
                            当前第 <span id="pageCurrent"><?php echo $page;?></span>
                            页，每页 50个
        <span id="page-link">


          <a href="javascript:gotoPageFirst()">第一页</a>
          <a href="javascript:gotoPagePrev()">上一页</a>
          <a href="javascript:gotoPageNext()">下一页</a>
                        </div>
                    </td>
                </tr>
                </tbody></table>

</form>