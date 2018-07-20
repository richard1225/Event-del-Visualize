<?php
if (isset($_GET['p'])) {
    $page = $_GET['p'];
    if ($page < 1) {
        $page = 1;
    }
} else {
    $page = 1;
}

$pageSize = 20;
$showPages = 5;
$rows = array();
$mysqli = new mysqli('127.0.0.1', 'root', 'richardtt', 'new_clue');
if ($mysqli->connect_error) {
    exit('connect error:' . $mysqli->connect_error);
}
$mysqli->set_charset('utf-8');
$start_position = ($page - 1) * $pageSize;
$sql = "SELECT * FROM compare_rel  ORDER BY update_time desc LIMIT {$start_position},{$pageSize};";
if ($result = $mysqli->query($sql)) {
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
} else {
    exit($mysqli->error);
}

    // 获取数据总条数
$total_sql = "SELECT count(*) FROM compare_rel;";
if ($count_result = $mysqli->query($total_sql)) {
    $row = $count_result->fetch_array(MYSQLI_NUM);
    $total = $row[0];
    $count_result->free();
} else {
    exit($mysqli->error);
}
    // 计算页数
$total_pages = ceil($total / $pageSize);
$mysqli->close();

    // 计算偏移量
$pageOffset = ($showPages - 1) / 2;
    // 初始化数据
$start = 1;
$end = $total_pages;
if ($total_pages > $showPages) {
    if ($page > $showPages) {
        $start = $page - $pageOffset;
        $end = $total_pages > $page + $pageOffset ? $page + $pageOffset : $total_pages;
    } else {
        $start = 1;
        $end = $total_pages > $showPages ? $showPages : $total_pages;
    }
    if ($page + $pageOffset > $total_pages) {
        $start = $start - ($page + $pageOffset - $end);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style type="text/css">
        body{
            font-size: 13px;
            font-family:Helvetica Neue,Helvetica,PingFang SC,Hiragino Sans GB,Microsoft YaHei,SimSun,sans-serif;
            width: 100%;
            padding_top:50px;
            -webkit-font-smoothing: antialiased;
        }
        div.content{
            height:260px;
            font-size: 15px;
            padding-top: 35px;
        }
        table{
            font-size: 14px;
            color: #606266;
        }
        tr{
            border-bottom: 1px solid #ebeef5;
        }
        th{
            text-align: left;
            border-bottom: 1px solid #ebeef5;
            padding: 12px 0;
        }
        td{
            padding: 12px 0;
        }
        tbody>tr:hover{
            background-color: #f5f7fa;
        }
        .expand-icon{
            cursor: pointer;
            /* color: #666; */
            font-size: 12px;
            transition: transform .2s ease-in-out;
            height: 20px;
        }
        thead{
            color: #909399;
            font-size: 20px;
        }
        .icon-td{
            cursor: pointer;
            padding-left: 10px;
            padding-right: 20px;
        }
        label{
            width: 90px;
            color: #99a9bf;
            margin-top: 25px;
        }
        .datadiv{
            margin-left: 18px;
        }
        .datadiv>span{
            
        }
        .expanded-cell{
            padding-left: 5px;
        }
        .wrapper{
            float: left;    
            display: inline-block;
            width: 49%;
            margin-left: 8px;
        }
        .btn-default{
            font-family: Microsoft YaHei,SimSun,sans-serif;
            position: fixed;
        }
    </style>
    <title>pageTest</title>
</head>
<body>
    <button type="button" class="btn btn-default" aria-label="Left Align" onclick="foldAll(this)">
        <span aria-hidden="true">点击展开全部标签</span>
    </button>
    <div class="content">
        <table border="0" cellspacing="0" cellpadding="0" width="820px" align="center">
            <thead>
                <tr>
                    <th></th>
                    <th>id</th>
                    <th>标题</th>
                    <th>日期</th>
                </tr>
            </thead>
            <?php foreach ($rows as $row) : ?>
                <tr>
                    <td class="icon-td" isopen="false" onclick="copyText(this)"><i class="expand-icon">></i></td>
                    <td><?php echo mb_substr($row['del_id'],0,10,'utf-8')."..." ?></td>
                    <td><?php echo mb_substr($row['del_title'],0,30,'utf-8')."..."; ?></td>
                    <td><?php echo $row['update_time']; ?></td>
                </tr>
                <tr class="hide">
                    <td colspan="4" class="expanded-cell">
                        <div>
                            <div class="wrapper">
                                <div class="item">
                                    <label>被删除-id</label>
                                    <div class="datadiv"><span><?php echo $row['del_id'];?></span></div>
                                </div>
                                <div class="item">
                                    <label>被删除-type</label>
                                    <div class="datadiv"><span><?php echo $row['del_type'];?></span></div>
                                </div>
                                <div class="item">
                                    <label>被删除-title</label>
                                    <div class="datadiv"><a href="<?php echo $row['del_url'];?>" target='_blank'><?php echo $row['del_title'];?></a href=""></div>
                                </div>
                                <div class="item">
                                    <label>被删除-content</label>
                                    <div class="datadiv"><span><?php echo $row['del_content'];?></span></div>
                                </div>
                            </div>
                            <div class="wrapper">
                                <div class="item">
                                    <label>相关-id</label>
                                    <div class="datadiv"><span><?php echo $row['rel_id'];?></span></div>
                                </div>
                                <div class="item">
                                    <label>相关-type</label>
                                    <div class="datadiv"><span><?php echo $row['rel_type'];?></span></div>
                                </div>
                                <div class="item">
                                    <label>相关-title</label>
                                    <div class="datadiv"><a href="<?php echo $row['rel_url'];?>" target='_blank'><?php echo $row['rel_title'];?></a></div>
                                </div>
                                <div class="item">
                                    <label>相关-content</label>
                                    <div class="datadiv"><span><?php echo $row['rel_content'];?></span></div>
                                </div>
                            </div>

                            
                        </div>
                    </td>
                </tr>
            <?php endforeach ?>
        </table>

        <!-- 以下是翻页 -->
        <center>
            <nav style="textt-align:center;">
                <ul class="pagination">
                <?php if ($page > 1) { ?>
                    <li><a href="index.php?p=1">首页</a></li>
                    <li><a href="index.php?p=<?php echo $page - 1; ?>">&laquo;上一页</a></li>
                <?php 
            } else { ?>
                    <li class="disabled"><span>首页</span></li>
                    <li class="disabled"><span>&laquo;上一页</span></li>
                <?php 
            } ?>
                <?php if ($total_pages > $showPages && $page > $pageOffset + 1) : ?>
                    <li class="disabled"><span>...</span></li>
                <?php endif ?>
                <?php for ($i = $start; $i <= $end; $i++) : ?>
                    <?php if ($page == $i) { ?>
                        <li class="active"><span><?php echo $i; ?><span class="sr-only">(current)
                        </span></span></li>
                    <?php 
                } else { ?>
                        <li><a href="index.php?p=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                    <?php 
                } ?>
                <?php endfor ?>
                <?php if ($total_pages > $showPages && $total_pages > $page + $pageOffset) : ?>
                        <li class="disabled"><span>...</span></li>
                <?php endif ?>
                <?php if ($page < $total_pages) { ?>
                    <li><a href="index.php?p=<?php echo $page + 1; ?>">下一页&raquo;</a></li>
                    <li><a href="index.php?p=<?php echo $total_pages; ?>">尾页</a></li>
                <?php 
            } ?>

                </ul>
                <url class="pagination">
                    <li class="disabled"><span>共<?php echo $total_pages ?>页</span></li>
                </url>
                
            </nav>
        </center>
    </div>
    
    
</body>
<script>
    function copyText(arg_this){
        now_this = arg_this
        if(arg_this.innerText == ">"){
            arg_this.innerText = "v";
            arg_this.setAttribute("isopen","true");
            arg_this.parentElement.nextElementSibling.className="display";
        }else if(arg_this.innerText == "v"){
            arg_this.innerText = ">";
            arg_this.setAttribute("isopen","false");
            arg_this.parentElement.nextElementSibling.className="hide";
        }
    } 
    function foldAll(arg_this){
        if(arg_this.firstElementChild.innerText == "点击展开全部标签"){
            arg_this.firstElementChild.innerText = "点击关闭全部标签";
            document.querySelectorAll(".icon-td").forEach(function(item, index, array){
                if (item.getAttribute("isopen") == "false"){
                    item.click();
                }
            });
        }else{
            arg_this.firstElementChild.innerText = "点击展开全部标签"
            document.querySelectorAll(".icon-td").forEach(function(item, index, array){
                if (item.getAttribute("isopen") == "true"){
                    item.click();
                }
            });
        }
    }
</script>
</html>

