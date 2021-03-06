<?php
  $json_str = file_get_contents('php://input'); //接收request的body
  $json_obj = json_decode($json_str); //轉成json格式
  
  $myfile = fopen("log.txt", "w+") or die("Unable to open file!"); //設定一個log.txt來印訊息
  fwrite($myfile, "\xEF\xBB\xBF".$json_str); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
  
  $sender_userid = $json_obj->events[0]->source->userId; //取得訊息發送者的id
  $sender_txt = $json_obj->events[0]->message->text; //取得訊息內容
  $sender_replyToken = $json_obj->events[0]->replyToken; //取得訊息的replyToken
  
  $sender_txt=rawurlencode($sender_txt); //因為使用get的方式呼叫luis api，所以需要轉碼
  $ch = curl_init('https://westus.api.cognitive.microsoft.com/luis/v2.0/apps/d7099908-c035-419a-a0b7-979e34e3a58b?subscription-key=d5f8632d50084d24834a4513ef14b9a3&timezoneOffset=-360&q='.$sender_txt);                                                                      
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                                                                          
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result_str = curl_exec($ch);
  fwrite($myfile, "\xEF\xBB\xBF".$result_str); //在字串前加上\xEF\xBB\xBF轉成utf8格式
  $result = json_decode($result_str);
  $ans_txt = $result -> topScoringIntent -> intent;
  $response = array (
    "to" => $sender_userid,
    "messages" => array (
      array (
        "type" => "text",
        "text" => $ans_txt
      )
    )
  );
  
  
 fwrite($myfile, "\xEF\xBB\xBF".json_encode($response)); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
  $header[] = "Content-Type: application/json";
  $header[] = "Authorization: Bearer mMIzVL/3HSFqDXfQBuX93sR3YrKMe8htxxVVj/s2tkYR18JB4pBhwfuPw5pphdtmUYtlQk+0CRlEime5N5uL4/2RhPUscMR4rncVCMf2U+Yo7ydH0Eirf/gBre0TEl056vkVwCAZYL6gwl9gpPMhUAdB04t89/1O/w1cDnyilFU=";
  $ch = curl_init("https://api.line.me/v2/bot/message/push");
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
  $result = curl_exec($ch);
  curl_close($ch);
?>
