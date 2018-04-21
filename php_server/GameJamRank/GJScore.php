<?php
header('Content-Type: text/html; charset=UTF-8');
include('GJRankDB.php');

$gameid = $_GET["gameid"];

if(!$gameid)
{
    echo "게임ID가 없습니다.";
    exit;
}

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


$sql = "SELECT id, game_name, score_order FROM ".$table_gameid." WHERE game_id='".$gameid."'";
$query_result = mysql_query($sql);
$result = mysql_fetch_array($query_result);

if(!$result)
{
    echo "게임ID '$gameid' 가 존재하지 않습니다.";
    exit;
}

$myGameID = $result[id];
$scoreOrder = $result[score_order] == 1?"DESC":"ASC" ;
$gameName = $result[game_name];
?>

<html>
<head>
<meta charset="UTF-8">
<title><?=$gameName?> 순위표</title>
<style type="text/css">
table.type09 {
    border-collapse: collapse;
    text-align: left;
    line-height: 1.5;

}
table.type09 thead th {
    padding: 10px;
    font-weight: bold;
    vertical-align: top;
    color: #369;
    border-bottom: 3px solid #036;
}
table.type09 tbody th {
    width: 150px;
    padding: 10px;
    font-weight: bold;
    vertical-align: top;
    border-bottom: 1px solid #ccc;
    background: #f3f6f7;
}
table.type09 td {
    width: 350px;
    padding: 10px;
    vertical-align: top;
    border-bottom: 1px solid #ccc;
}
</style>
<script language="javascript">
setTimeout(function(){
   window.location.reload(1);
}, 5000);
</script>
</head>
<body>
<h1><?=$gameName?> 게임 순위표!</h1>
<table class="type09">
    <thead>
    <tr>
        <th scope="cols">순위</th>
        <th scope="cols">닉네임</th>
        <th scope="cols">점수</th>
    </tr>
    </thead>
    <tbody>
<?php

$sql = "SELECT nickname, score, ";
$sql .= "	@rn := CASE WHEN @rn = 0 THEN 1 ELSE @rn + 1 END rownum, ";
$sql .= "   @rk := CASE WHEN @rn = 1 THEN 1 ELSE ";
$sql .= "     	CASE WHEN @score = score THEN @rk ELSE @rn END ";
$sql .= "   	END rank, ";
$sql .= "       @score := score ";
$sql .= "   FROM ";
$sql .= "   (SELECT nickname, score FROM ".$table_socre." WHERE game_id=".$myGameID." AND score != '' ORDER BY score ".$scoreOrder.", nickname) a, ";
$sql .= "   (SELECT @score := 0, @rk := 0, @rn := 0) b";

$query_result = mysql_query($sql);
while($result = mysql_fetch_array($query_result))
{
?>
      <tr>
        <th scope="row"><?=$result[rank]?></th>
        <td><?=$result[nickname]?></td>
        <td><?=$result[score]?></td>
    </tr>  

<?php
}
mysql_free_result($query_result);
mysql_close($db);
?>
</tbody>
</table>
</body>
</html>
