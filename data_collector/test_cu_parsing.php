<html>
  <head>
    <meta charset="utf-8">
  </head>
  <body>
<?php
  include "Snoopy.class.php";

  $today = date("Ymd");
  $isHaveItem = false;
//leeth99.dothome.co.kr
  $connect = mysql_connect('localhost','leeth99','djskej11');
  $db = mysql_select_db('leeth99', $connect);
  $sql = "set names utf8";
  $result = mysql_query($sql, $connect);

  $sql = "select * from cvs_update_check where cvs_name='cu' and upload_date='".$today."'";
  $result = mysql_query($sql, $connect);

  while($row = mysql_fetch_row($result)){
    $isHaveItem = true;
/*
    echo "<b>편의점명</b> : ".$row[1]."<br>";
    echo "<b>상품명</b> : ".$row[2]."<br>";
    echo "<b>가격</b> : ".$row[4]."<br><hr>";
*/

  }


$PageRequestCount = 1;
while($isHaveItem==false){
    $vars = array(
    "pageIndex"=>$PageRequestCount,
    "listType"=>"1"
    //"searchCondition"=>"23"
    );

    $snoopy = new Snoopy();
    $snoopy->httpmethod = "POST";
    $snoopy->submit("http://cu.bgfretail.com/event/plusAjax.do",$vars);

    //echo $snoopy->results;


    //1. 긁어온 문서에서 노드를 찾아내는데 명확성을 부여할 수 있는 HTML부분만 가져옵니다.
  //$parse_html = splitBetweenStr($snoopy->results,"<div class=\"list_area daily_img\">","</ul>");
  //2. 그 내용을 기반으로 하나의 노드를 구분짓습니다.
  $parse_node = splitBetweenStr($snoopy->results,"<div class=\"photo\">","<span class=\"tag\">");
  //3. 노드들을 순회하며 각 노드의 세부내용을 뽑아냅니다.
  $cnt = 0;
  //$sql = "INSERT INTO webtoon(corp, week, updated, toon_name, toon_url, toon_image) VALUES ";

  if(strpos($snoopy->results, "조회된 상품이 없습니다") !== false || $cnt > 5000) {
      echo "*************************데이터 없음 이제 조회 끝!!";
      break;
  } else {
      echo "데이터 아직 존재함";
  }
  $PageRequestCount++;

  echo "<br>-------------------------------------------------------<br>";

  foreach ($parse_node as $value) {
    $parse_n_plus = 1;
    if(strpos($value, "1+1") !== false) {
        $parse_n_plus = 1;
    }
    else if(strpos($value, "2+1") !== false) {
        $parse_n_plus = 2;
    }
    else if(strpos($value, "3+1") !== false) {
        $parse_n_plus = 3;
    }

    $parse_img_tmp = splitBetweenStr($value,"<img src=\"","\" alt");
    $parse_name_tmp = splitBetweenStr($value,"<p class=\"prodName\">","</a></p>");
    $parse_name_tmp2 = explode(">",$parse_name_tmp[0]);
    //echo "*".$parse_name_tmp2;
    $parse_price_tmp = splitBetweenStr($value,"<p class=\"prodPrice\"><span>","</span>");

    //$parse_author_merge = "";
    //foreach ($parse_author_tmp as $value2) {
    //	$parse_author_merge .= $value2.",";
    //}
    //$parse_author_merge=substr($parse_author_merge, 0, -1);

    $parse_img[$cnt] = $parse_img_tmp[0];
    $parse_name[$cnt] = $parse_name_tmp2[1];
    $parse_price[$cnt] = $parse_price_tmp[0];

    echo "item".$cnt." : ".$parse_img[$cnt].", ".$parse_name[$cnt].", ".$parse_price[$cnt]."<br>";


    $sql = "INSERT INTO cvs_n_plus_one_goods(cvs_name, item_name, category, price, image, detail, upload_date) VALUES('cu', '".$parse_name[$cnt]."', '".$parse_n_plus."',".$parse_price[$cnt].",'".$parse_img[$cnt]."','','".$today."')";
    $result = mysql_query($sql, $connect);

    //$sql .= "('daum','".$daily[$date]."','".$today."','[".$parse_author_tmp[0]."] ".$parse_title_tmp[0]."','http://webtoon.daum.net/m/webtoon/view/".$parse_url_tmp[0]."','".$parse_image_tmp[0]."'),";
    $cnt++;

    unset($parse_img_tmp);
    unset($parse_name_tmp);
    unset($parse_name_tmp2);
    unset($parse_price_tmp);


  }
}//while문 끝

//증정품 조사
if($isHaveItem==false){//
  $vars = array();
  $cnt = 0;

  $default_name = array();
  $default_img = array();
  $default_price = array();
  $bonus_name = array();
  $bonus_img = array();
  $bonus_price = array();


  $snoopy = new Snoopy();
  $snoopy->httpmethod = "POST";
  $snoopy->submit("http://cu.bgfretail.com/event/presentAjax.do",$vars);

    //echo $snoopy->results;


    //1. 긁어온 문서에서 노드를 찾아내는데 명확성을 부여할 수 있는 HTML부분만 가져옵니다.
  //$parse_html = splitBetweenStr($snoopy->results,"<div class=\"list_area daily_img\">","</ul>");
  //2. 그 내용을 기반으로 하나의 노드를 구분짓습니다.
  $parse_node = splitBetweenStr($snoopy->results,"<div class=\"presentList-w\">","<li>증정행사</li>");
  $trans_results = str_replace("원</p>", "원</p><!--증정품끝단-->", $snoopy->results);
  echo "***transe".$trans_results;
  $parse_node2 = splitBetweenStr($trans_results,"<div class=\"presentList-e\">","<!--증정품끝단-->");
  //3. 노드들을 순회하며 각 노드의 세부내용을 뽑아냅니다.


  foreach ($parse_node as $value) {

    $parse_img_tmp = splitBetweenStr($value,"<img src=\"","\" alt");
    $parse_name_tmp = splitBetweenStr($value,"<p class=\"prodName\"><a href=","</a></p>");
    $parse_name_tmp2 = explode(">",$parse_name_tmp[0]);
    $parse_price_tmp = splitBetweenStr($value,"<p class=\"prodPrice\"><span>","</span>원");


    $default_name[$cnt] = $parse_name_tmp2[1];
    $default_img[$cnt] = $parse_img_tmp[0];
    $default_price[$cnt] = str_replace(",", "", $parse_price_tmp[0]);
    echo "***".$default_name[$cnt].",".$default_img[$cnt].",".$default_price[$cnt]."<br>";
    $cnt++;
  }

  $cnt = 0;
  foreach ($parse_node2 as $value) {

    $parse_img_tmp = splitBetweenStr($value,"<img src=\"","\" alt");
    $parse_name_tmp = splitBetweenStr($value,"<p class=\"prodName\"><a href=","</a></p>");
    $parse_name_tmp2 = explode(">",$parse_name_tmp[0]);
    $parse_price_tmp = splitBetweenStr($value,"<p class=\"prodPrice\">","원</p>");


    $bonus_name[$cnt] = $parse_name_tmp2[1];
    $bonus_img[$cnt] = $parse_img_tmp[0];
    $bonus_price[$cnt] = str_replace(",", "", $parse_price_tmp[0]);
    echo "***".$bonus_name[$cnt].",".$bonus_img[$cnt].",".$bonus_price[$cnt]."<br>";
    $cnt++;
  }

  for($i = 0; $i < $cnt; $i++){
    $sql = "INSERT INTO cvs_bonus_goods(cvs_name, default_item_name, bonus_item_name, price, bonus_price, default_item_image, bonus_item_image, detail, upload_date) VALUES('cu', '".$default_name[$i]."', '".$bonus_name[$i]."',".$default_price[$i].",".$bonus_price[$i].",'".$default_img[$i]."','".$bonus_img[$i]."','','".$today."')";
    //echo $sql."<br>";
    $result = mysql_query($sql, $connect);
  }





}

if($isHaveItem == false){
  $sql = "INSERT INTO cvs_update_check(cvs_name, upload_date) VALUES('cu','".$today."')";
  $result = mysql_query($sql, $connect);
}
//업데이트 종료
  /*
  $snoopy = new Snoopy;
  $snoopy->fetch("http://cu.bgfretail.com/event/plusAjax.do");
  print $snoopy->results;
*/
function splitBetweenStr($str, $startWord, $endWord){
    for ($i=0, $len=strlen($str); $i<$len; $i++){
        $target = substr($str,$i);
        $prevStartIdx = strpos($target, $startWord);
        $startIdx = $prevStartIdx + strlen($startWord);
        $endIdx = strpos(substr($target, $startIdx), $endWord);
        if ($prevStartIdx===false || $endIdx===false){
             break;
        } else {
             $betweenStrings[] = substr($target, $startIdx, $endIdx);
             $i += $startIdx + $endIdx + strlen($endWord) - 1;
        }
    }
    return $betweenStrings;
}

?>
  </body>

</html>
