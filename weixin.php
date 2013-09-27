<?php
/**
 * 八戒图书调用文件
 *
 */
define("ERR_MSG","请求出错，臣妾做不到啊~");
define("TOKEN","your_wechat_token");

require('inc/Wechat.php');
require('inc/db.php');
/**
 * Wechat sdk调用
 */
class MyWechat extends Wechat {

    /**
     * 用户关注时触发，回复「欢迎关注」
     *
     * @return void
     */
    protected function onSubscribe() {
        $this->responseText("感谢你关注老谭justdoIT，会时不时的更新一些老谭的碎碎念\n回复help可以查看八戒图书使用帮助\n此处省略100字……");
    }

    /**
     * 收到文本消息时触发，回复收到的文本消息内容
     *
     * @return void
     */
    protected function onText() {
        $cmd = strtolower(trim($this->getRequest('content')));
        $user_id = $this->getRequest('FromUserName');
        if($cmd == "help"){
            $content = "小主吉祥\n首次使用需要绑定姓名，回复bd+名字，例如'bd老谭'\n\n回复m可以查看书籍目录\n\n回复b+序号即可预订,例如b1";
            $this->responseText( $content );
        }
        elseif(preg_match("/^bd./", $cmd)){
            $db = new SaeMysql();
            $user_name = str_replace("bd","",$cmd);
            $sql = "INSERT INTO book_user (openid,name) VALUES ('$user_id', '".addslashes($user_name)."')";
            $db->runSql( $sql );
            if( $db->errno() != 0 ){
                $content = "请求格式出错，或者你已经绑定过了";
            }else{
                $content = "HI！$user_name,你已经成功绑定了大名";
            }
            $db->closeDb();
            $this->responseText( $content );
        }
        elseif(preg_match("/^m\d*$/", $cmd)){
            $page_cmd = intval(str_replace("m","",$cmd));
            $page = $page_cmd?$page_cmd:1;//当前页
            $page_num = 10;//单页条数
            $db = new SaeMysql(ERR_MSG);
            $sql = "select count(*) from book_list";
            $count = $db->getVar($sql);
            $page_size = ceil($count/$page_num);//总页数
            if($page > $page_size){
                $content = "当前只有".$page_size."页，你的请求臣妾做不到啊~";
            }else{
                $sql = "select id,title,state,update_time from book_list limit ".($page-1)*($page_num).",$page_num";
                $data = $db->getData($sql);
                $content ="";
                foreach($data as $item){
                    $content .= $item['id']." ".$item['title'];
                    if(!empty($item['state'])){
                        $user_sql = "select name from book_user where openid='".$item['state']."'";
                        $borrow_user = $db->getVar($user_sql);
                        $borrow_time = ceil((time() - strtotime($item['update_time']))/86400);
                        $content .= "—".$borrow_user."[借出".$borrow_time."天]\n";
                    }else{
                        $content .= "—[空闲]\n";
                    }
                }
                if($page < $page_size){
                    $content .= "\n回复编号查看简介\n回复 b+编号 预定该书\n回复 m".($page+1)." 查看下一页";
                }else{
                    $content .= "\n回复编号查看简介\n回复 b+编号 预定该书\n牌子都翻完了，回复 m1 查看第一页";
                }

                if( $db->errno() != 0 ){
                    $content = $db->errmsg();
                }
                $db->closeDb();
            }
            $this->responseText( $content );
        }
        elseif(preg_match("/^b\d+$/", $cmd)){
            $borrow_id = intval(str_replace("b","",$cmd));
            $db = new SaeMysql(ERR_MSG);

            $is_sql = "select count(*) from book_user where openid='".$user_id."'";
            $is_bd = $db->getVar($is_sql);
            if ($is_bd == 1){
                $sql = "select id,state from book_list where id=".$borrow_id;
                $data = $db->getLine($sql);
                $state = $data['state'];
                $id = $data['id'];
                if($db->errno() != 0){
                   $content = $db->errmsg();
                }elseif(empty($id)){
                    $content = ERR_MSG;
                }
                elseif(!empty($state)){
                   $get_sql = "select name from book_user where openid='".$state."'";
                   $state_user = $db->getVar($get_sql);
                   $content = "sorry，此书还被".$state_user."强占中，去催催他吧";
                }else{
                   $set_sql = "UPDATE book_list SET state='".addslashes($user_id)."', update_time='".date('Y-m-d H:i:s')."' WHERE id=".addslashes($borrow_id);
                   $db->runSql($set_sql);
                   if( $db->errno() != 0 ){
                       $content = $db->errmsg();
                   }else{
                       $content = "预借成功，快去找 ‘伟哥’ 吧";
                   }
                }
            }else{
                $content = "你还没绑定，回复 bd姓名 进行绑定才能借书";
            }
            $db->closeDb();
            $this->responseText( $content );
        }
        elseif(preg_match("/^\d+$/", $cmd)){
            $book_id = intval($cmd);
            $db = new SaeMysql(ERR_MSG);
            $sql = "select * from book_list where id=".$book_id;
            $data = $db->getLine($sql);
            if( $db->errno() != 0 || empty($data)){
                $content = $db->errmsg();
            }else{
                $title = $book_id." ".$data['title'];
                if(!empty($data['book_from'])){
                   $title .= "[".$data['book_from']."]";
                }
                $content = $data['summary'];
                $image = $data['image'];
                $url = "http://book.douban.com/subject/".$data['douban_id']."/";
                $items = array(
                    new NewsResponseItem($title,$content, $image, $url)
                );
                $this->responseNews($items);
            }
            $db->closeDb();
        }
        else{
            $content = "你的请求，臣妾做不到啊~你可以回复help查看微信借书帮助";
            $this->responseText( $content );
        }
        $this->responseText( $content );
    }
}

$wechat = new MyWechat(TOKEN, FALSE);
$wechat->run();
