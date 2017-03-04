<?php
  ini_set('max_execution_time', 70); 
  ini_set("display_startup_errors", 1);
  ini_set("display_errors", 1);  
  ini_set('memory_limit', '-1');
  
  include __DIR__.'/vendor/autoload.php';
  use RestCord\DiscordClient;

  $db = array();
  
  /*------------------------------------------------------//
                       USER SETTINGS
                These are your user settings. 
       You need to fill these out for the bot to work.
  //------------------------------------------------------*/

  $db['seed'] = ''; // 30+ random characters
  $db['host'] = 'localhost'; // database host
  $db['user'] = ''; // database username
  $db['pass'] = ''; // database password 
  $db['name'] = ''; //database name
  $db['botID'] = ''; // ID number of the chosen bot from PersonalityForge.com
  $db['chatkey'] = ''; // PersonalityForge.com API key
  $db['chatsecret'] = ''; // PersonalityForge.com API secret
  $db['imagekey'] = ''; // Microsoft Cognitive Services Computer Vision API key

  $botID = ; // the numeric user ID of your discord bot
  $guild = ; // the numeric id of your discord server
  $channel = ; // the numeric id of the channel you want the bot in
  $token = ''; // your discord API token
  
  /*------------------------------------------------------//
                       IMPORTANT STUFF
      Unless you're 1337 and you know what you're doing,
          you shouldn't mess with stuff under here.
  //------------------------------------------------------*/
  
  $linked = '<@' . $botID . '>';
  $sql = mysqli_connect($db['host'],$db['user'],$db['pass'],$db['name']);
  if(!$sql){
    die("Connection failed: " . mysqli_connect_error());
  }
  $discord = new DiscordClient(['token' => $token]);
   

  global $sql, $channel, $guild, $botID, $linked, $discord, $token;

  // Core funcs

  function isRunning(){
    global $sql, $channel, $guild, $botID, $linked, $discord;
    $useD = "SELECT * FROM logs WHERE channel_id='" . intval($channel) . "' AND guild_id='" . intval($guild) . "'";
    $useDx = mysqli_query($sql, $useD);
    if(mysqli_num_rows($useDx) > 0){
      while($row = mysqli_fetch_assoc($useDx)){
        return $row["running"];
      }
    }else{
      return 'empty';
    }
  }

  function heartbeat(){
    global $sql, $channel, $guild, $botID, $linked, $discord;
    $useD = "SELECT * FROM logs WHERE channel_id='" . intval($channel) . "' AND guild_id='" . intval($guild) . "'";
    $useDx = mysqli_query($sql, $useD);
    if(mysqli_num_rows($useDx) > 0){
      while($row = mysqli_fetch_assoc($useDx)){
        if(intval(time()) > intval($row["heartbeat"])){
          mysqli_query($sql, "UPDATE logs SET heartbeat='" . intval(intval(time()) + 100) . "' WHERE channel_id='" . intval($channel) . "' AND guild_id='" . intval($guild) . "'");
          include __DIR__.'/sock.php';
        }
      }
    }else{
      return 'empty';
    }
  }

  function safeHash($input){ global $db; return hash('sha256', $db['seed'] . $input . $db['seed']); }

  function imgRead($url){
    global $db;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://westus.api.cognitive.microsoft.com/vision/v1.0/analyze?visualFeatures=Description");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Ocp-Apim-Subscription-Key: ' . $db['imagekey']));
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
      'url' => $url
    ), true));
    $result = curl_exec($curl);
    curl_close($curl);
    $desc = json_decode($result, true)['description']['captions'][0]['text'];
    return $desc;
  }
  
  function chatAI($key){
    global $db, $sql, $channel, $guild, $botID, $linked, $discord;
      if(strpos($key['content'], '<@' . $botID . '>') !== false){
        $sndCnt = preg_replace('/<@[0-9+]>/', '', $key['content']);
      }else{
        $sndCnt = $key['content'];
      }
      $mge = array(
        'message' => array(
                'message' => $sndCnt,
                'chatBotID' => $db['botID'],
                'timestamp' => time()),
        'user' => array(
                'firstName' => $key['author']['username'],
                'externalID' => $key['author']['id'])
      );
      $phost = "http://www.personalityforge.com/api/chat/";
      $messageJSON = json_encode($mge);
      $hash = hash_hmac('sha256', $messageJSON, $db['chatsecret']);
      $purl = $phost."?apiKey=".$db['chatkey']."&hash=".$hash."&message=".urlencode($messageJSON);
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $purl);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($ch);
      curl_close($ch);
      $responseObject = json_decode($response, true);
      $reply = str_replace('Laurel', '17' ,$responseObject['message']['message']);
      return $reply;
  }

  function transfer($from, $too, $amount){
    global $sql;

    $useQx = "SELECT * FROM balance WHERE id='" . intval($from) . "'";
    $useSqlx = mysqli_query($sql, $useQx);
    if(mysqli_num_rows($useSqlx) < 1) {
      mysqli_query($sql,"INSERT INTO balance (id,bal) VALUES ('" . intval($from)  . "','100')");
    $senderBal = 100;
    }else{
      while($row = mysqli_fetch_assoc($useSqlx)) {
        $senderBal = intval($row['bal']);
      }
    }

    $useQi = "SELECT * FROM balance WHERE id='" . intval($too) . "'";
    $useSqli = mysqli_query($sql, $useQi);
    if(mysqli_num_rows($useSqli) < 1) {
      mysqli_query($sql,"INSERT INTO balance (id,bal) VALUES ('" . intval($too)  . "','100')");
      $recipientBal = 100;
    }else{
      while($row = mysqli_fetch_assoc($useSqli)) {
        $recipientBal = intval($row['bal']);
      }
    }

    if($senderBal >= intval($amount) && intval($amount) > 0 && intval($from) != intval($too)){
      $newSender = intval($senderBal-intval($amount));
      $newRecipient = intval($recipientBal+intval($amount));
      mysqli_query($sql, "UPDATE balance SET bal='" . intval($newSender) . "' WHERE id='" . intval($from) . "'");
      mysqli_query($sql, "UPDATE balance SET bal='" . intval($newRecipient) . "' WHERE id='" . intval($too) . "'");
      return 'sent <:coin:284693468511469569>' . intval($amount) . ' to <@' . intval($too) . '>.';
     }else{
      $exit = 'Internal error.';
      if($senderBal < intval($amount)) $exit = 'Balance too low.';
      if(intval($amount) <= 0) $exit = 'Transfer amount less than one coin.';
      if(intval($from) == intval($too)) $exit = 'You cannot transfer to yourself.';
      return '*An error occurred: ' . $exit . ' Your funds have not changed. Your balance is <:coin:284693468511469569>' . $senderBal . '*';
    }

  }

  function cleanId($tag){
    $match = array();
    preg_match('/<@([0-9]+)\?/', $tag, $match);
    return $match[1];
  }

  function think(){
    global $discord, $channel;
    $discord->channel->triggerTypingIndicator([
      'channel.id' => $channel
    ]);
  }
  
  function say($msg){
    global $discord, $channel;
    $discord->channel->createMessage([
      'channel.id' => $channel,
      'content' => $msg
    ]);
  }

  function cockblock($msg, $cock){
    $name = '<@' .  $msg['author']['id'] . '>';
    $msg['content'] = 'say ' . $cock;
    $ai = chatAI($msg);
    if(strpos($ai, $cock) === false){
      say($name . ' ' .$ai);
      return false;
    }else{
      return true;
    }
  }


?>
