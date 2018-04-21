<?php
$rawData = $_POST["mainJson"];

if(!$rawData)
    $rawData = file_get_contents("php://input");

include('GJRankDB.php');

//설정
$table_socre = 'gjr_score';
$table_gameid = 'gjr_gameid';

if(!$db = mysql_connect($db_host, $db_username, $db_password)){
    echo 'Could not connect to mysql';
    exit;
}

if(!mysql_select_db($db_database, $db)){
    echo 'Could not select database';
    exit;
}

mysql_query("set names utf8", $db);


/*
request
{
    "command" : "postScore",
    "content" : {
        "nickname" : "빵원",
        "score" : 123
    }
}

response
{
    "result" : "OK", or Error
    "content" : {
        "requestCommand" : "postScore",    
        "responseValue" : "bla bla~"
    }
}
*/

$json = json_decode($rawData);

$command = $json->command;
$content = $json->content;

function MakeResult($result, $value)
{
    global $command;
    $resultStr = "OK";
    if(!$result)
        $resultStr = "Error";

    echo json_encode(array("result"=>$resultStr, "content"=>array("requestCommand"=>$command, "responseValue"=>$value)));    
}

function SelectQuery($sql)
{
    $query_result = mysql_query($sql);

    $result = mysql_fetch_array($query_result);
    mysql_free_result($query_result);
    return $result;
}


switch($command)
{
    case "getOrCreateGameID":
    $gameid = $content->gameid;
    $gamename = $content->gamename;
    $order = $content->order;
    
    $sql = "SELECT id FROM ".$table_gameid." WHERE game_id='".$gameid."'";
    $result = SelectQuery($sql);

    $_id = 0;
    if(!$result) //등록된거 없음. 게임 등록
    {
        $sql = "INSERT INTO ".$table_gameid." SET ";
        $sql .= "game_id='".$gameid."',";
        $sql .= "game_name='".$gamename."'";
        if($order)
            $sql .= ",score_order=".$order;

        mysql_query($sql);
        $_id = mysql_insert_id();
        $retJson = array("id"=>$_id, "message"=>$gameid." 게임을 생성하였습니다.");        
        MakeResult(true, $retJson);
    }
    else
    {        
        $_id = $result[id];        
        $retJson = array("id"=>$_id, "message"=>$gameid." 게임이 존재합니다.");
        MakeResult(false, $retJson);
    }
    break;

    case "getOrCreateNickname":
    $gameid = $content->gameid;
    $nickname = $content->nickname;

    $sql = "SELECT id FROM ".$table_socre." WHERE game_id=".$gameid." AND nickname='".$nickname."'";
    $result = SelectQuery($sql);

    if(!$result) //등록된거 없음. 닉네임 등록
    {
        $sql = "INSERT INTO ".$table_socre." SET ";
        $sql .= "game_id='".$gameid."',";
        $sql .= "nickname='".$nickname."',";
        $sql .= "reg_time=now()";

        mysql_query($sql);
        $_id = mysql_insert_id();

        $retJson = array("id"=>$_id, "message"=>$nickname." 을 생성했습니다.");
        MakeResult(true, $retJson);
    }
    else
    {        
        $_id = $result[id];

        $retJson = array("id"=>$_id, "message"=>$nickname." 이 존재합니다.");
        MakeResult(true, $retJson);
    }
    break;


    case "postScore":  //점수보내기
    $nickname = $content->nickname;
    $gameid = $content->gameid;
    $score = $content->score;

    $sql = "SELECT score_order FROM ".$table_gameid." WHERE id=".$gameid;
    $result = SelectQuery($sql);

    if(!$result)
    {
        $retJson = array("message"=>"게임이 등록되지 않았습니다.");
        MakeResult(false, $retJson);

        mysql_close($db);
        exit;
    }

    $order = $result[score_order];    //order정보 1: 높은것이 1등, 2 : 낮은것이 1등

    $sql = "SELECT score FROM ".$table_socre." WHERE game_id='".$gameid."' AND nickname='".$nickname."'";    //기존 score를 가져온다.
    $result = SelectQuery($sql);

    if(!$result)
    {
        $retJson = array("message"=>$nickname." 닉네임이 등록되지 않았습니다.");
        MakeResult(false, $retJson);

        mysql_close($db);
        exit;
    }

    $score_update = false;
    if(!$result[score])
    {
        $score_update = true;
        //점수가 없으므로 바로 업데이트
    }
    else
    {
        if($order == 1)
        {
            if($score > $result[score])
                $score_update = true;            
        }
        else if($order == 2)
        {
            if($score < $result[score])
                $score_update = true;
        }
    }

    if($score_update)
    {
        $sql = "UPDATE ".$table_socre." SET ";
        $sql .= "score=".$score." ";
        $sql .= "WHERE game_id='".$gameid."' AND nickname='".$nickname."'";

        mysql_query($sql);        

        $retJson = array("message"=>"점수가 업데이트되었습니다.");
        MakeResult(true, $retJson);
    }
    else
    {
        $retJson = array("message"=>"기존점수를 계속 사용합니다.");
        MakeResult(true, $retJson);
    }
}

mysql_close($db);

?>