<?php
    define("PASSWORD", "admin");
    session_start();
    require('inc/db.php');
    $db = new SaeMysql();

    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: login.html");
        exit();
    }

    if (isset($_POST['password']) && (addslashes($_POST['password']) == PASSWORD) && (empty($_SESSION['is_admin']))) {
        $_SESSION['is_admin'] = TRUE;
    }

    if ($_SESSION['is_admin']) {

        if (isset($_POST['url'])) {
            $douban_id = intval($_POST['url']);
            $book_from = empty($_POST['from']) ? NULL : $_POST['from'];
            $result = json_decode(file_get_contents("https://api.douban.com/v2/book/" . $douban_id), true);
            if ($result['code']) {
                exit($result['msg'] . '  <a href="index.php">返回</a>');
            }
            $title = $result['title'];
            $image = $result['images']['large'];
            $summary = $result['summary'];
            $sql = "INSERT INTO book_list (douban_id,title,image,summary,book_from) VALUES ($douban_id, '$title','$image','$summary','$book_from')";
            $db->runSql($sql);
            if ($db->errno() != 0) {
                exit("Error:" . $db->errmsg());
            } else {
                header("Location: index.php");
            }
        } elseif (isset($_GET['state'])) {
            $id = intval($_GET['state']);
            $sql = "UPDATE book_list SET state = NULL WHERE id=$id ";
            $db->runSql($sql);
            if ($db->errno() != 0) {
                exit("Error:" . $db->errmsg());
            } else {
                exit("1");
            }
        }
    }
    $sql = "SELECT * FROM book_list ORDER BY id DESC";
    $data = $db->getData($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="assets/ico/favicon.png">

    <title>八戒图书馆</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="assets/js/html5shiv.js"></script>
    <script src="assets/js/respond.min.js"></script>
    <![endif]-->
    <style type="text/css">
        .admin {
            line-height: 34px;
        }

        .form-inline .item-douban {
            font-size: 18px;
            line-height: 34px;
            height: 34px;
            display: inline-block;
            vertical-align: middle;
        }

        .item-from {
            margin-left: 10px;
        }

        .list-group .col-sm-5 {
            text-align: right;
        }
    </style>
</head>

<body>

<div class="container">


    <div class="header">
        <div class="pull-right admin">
            <?php
            if ($_SESSION['is_admin']) {
                echo '管理员, <a href="?logout">登出</a>';
            } else {
                echo '<a href="login.html">管理员登录</a>';
            }
            ?>
        </div>
        <h1>八戒图书管理</h1>
    </div>
    <hr>
    <div class="panel panel-default">
        <!-- Default panel contents -->
        <div class="panel-heading">图书列表</div>
        <?php
            if($_SESSION['is_admin']){
        ?>
        <div class="panel-body">
            <form class="form-inline" role="form" action="" method="POST">
                <span class="item-douban">http://book.douban.com/subject/</span>

                <div class="form-group">
                    <input type="text" class="form-control" name="url" id="url" placeholder="Enter douban id">
                </div>
                <span class="item-douban">/</span>

                <div class="form-group item-from">
                    <input type="text" class="form-control" name="from" id="from" placeholder="来源（eg:xxx捐赠）">
                </div>
                <button type="submit" class="btn btn-primary">确定添加</button>
            </form>
        </div>
        <?php }//end if admin ?>
        <!-- List group -->
        <ul class="list-group">
            <?php
            $list = "";
            foreach ($data as $item) {
                $list .= '<li class="list-group-item" data-id="' . $item["id"] . '"><div class="row">';
                $list .= '<span class="col-sm-7">';
                $list .= '<a href="http://book.douban.com/subject/' . $item["douban_id"] . '/" target="_blank">' . $item["title"] . '</a>';
                if (!empty($item["book_from"])) {
                    $list .= '[' . $item["book_from"] . ']';
                }
                $list .= '</span><span class="col-sm-5">';
                if (!empty($item["state"])) {
                    $get_sql = "select name from book_user where openid='" . $item['state'] . "'";
                    $borrow_name = $db->get_var($get_sql);
                    $list .= $borrow_name . '(' . $item["update_time"] . '借出) ';
                    if($_SESSION['is_admin']){
                        $list .= '<a class="btn btn-default btn-sm book-return" href="?state=' . $item["id"] . '">还书</a>';
                    }
                } else {
                    $list .= '空闲';
                }
                $list .= '</span>';
                $list .= '</div></li>';
            }
            echo $list;
            $db->closeDb();
            ?>
        </ul>
    </div>

</div>
<!-- /container -->


<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script type="text/javascript" src="http://codeorigin.jquery.com/jquery-1.10.2.min.js"></script>
<script type="text/javascript">
    $(".book-return").click(function (e) {
        e.preventDefault();
        $.get($(this).attr("href"), function (result) {
            if (result == '1') {
                alert("还书成功");
                location.reload();
            } else {
                alert(result + "\n臣妾做不到啊~");
            }
        });
    })
</script>
</body>
</html>