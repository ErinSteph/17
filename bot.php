<?php
include_once __DIR__.'/config.php';
global $sql, $channel, $guild;

mysqli_query($sql, "UPDATE logs SET running='running' WHERE guild_id='$guild' AND channel_id='$channel'");

$que = "SELECT * FROM logs WHERE channel_id='$channel' AND guild_id='$guild'";

echo $que . '<br><br>';

$checkSql = mysqli_query($sql, $que);

if(mysqli_num_rows($checkSql) > 0) {
    while($row = mysqli_fetch_assoc($checkSql)) {
        $prevData = json_decode($row["data"], true);
        $last = intval($row["last"]);
    }
}else{
  mysqli_query($sql,"INSERT INTO logs (channel_id,guild_id,data,heartbeat) VALUES ('" . $channel . "','" . $guild . "','[]', '" . (intval(time()) + 100) . "')");

}

var_dump($checkSql);

echo '<br><br>';

echo mysqli_num_rows($checkSql) . '<br>';

if(mysqli_num_rows($checkSql) < 1){
    echo 'no logs<br><br>';
}

$temp = array();

$stored = $prevData;

foreach(
  $discord->channel->getChannelMessages([
    'channel.id' => $channel, 
    'after' => $last, 
    'around' => ($last+25),
    'limit' => 100, 
    'before' =>  intval(999999999999999999999999)
  ]) 
as $i => $key){
 echo json_encode($key);
 echo '<br><br>'; 
  array_push($temp, $key['id']);

  if(in_array($key['id'], $stored) == false && $key['author']['id'] != $botID){
    
    
    $useQ = "SELECT * FROM balance WHERE id='" . $key['author']['id'] . "'";
    $useSql = mysqli_query($sql, $useQ);
    if(mysqli_num_rows($useSql) < 1) {
      mysqli_query($sql,"INSERT INTO balance (id,bal) VALUES ('" . $key['author']['id'] . "','100')");
    $key['bal'] = 100;
    }else{
      while($row = mysqli_fetch_assoc($useSql)) {
        $key['bal'] = $row['bal'];
      }
    }


    if($key['id'] > $last){
      $last = $key['id'];
    }
 
    echo  $key['author']['username'] . ': ' . $key['content'] .  '<br>';

  /*---------- commands -----------*/


    // DESCRIBE IMAGE
    if($key['attachments'][0]){
      think();
      $imagInfo = imgRead($key['attachments'][0]['url']);
      $name = '<@' .  $key['author']['id'] . '>';
      $remind = $key['content'];
      $key['content'] = $imagInfo;
      chatAI($key);
      $key['content'] = $remind;
      $discord->channel->createMessage([
        'channel.id' => $channel,
        'content' => $name . ' ' . $imagInfo . '!'
      ]); 
    }

    // BOT MENTIONED
    if(strpos($key['content'], '<@' . $botID . '>') !== false || rand(1,90) == 10){
      think();
      $name = '<@' .  $key['author']['id'] . '>';
      $reply = chatAI($key);
      $discord->channel->createMessage([
        'channel.id' => $channel,
        'content' => $name . ' ' . $reply
      ]);
    } 

    // on !17
    if(strpos($key['content'], '!17') !== false){
      think();
      $name = '<@' .  $key['author']['id'] . '>';
      $discord->channel->createMessage([
        'channel.id' => $channel, 
        'content' => "Hi $name!"
      ]);
    }
    
    // on !boop
    if(strpos($key['content'], '!boop') !== false){
      $boops = json_decode(file_get_contents('boops.txt'), true);
      think();
      $name = '<@' .  $key['author']['id'] . '>';
      $cont = '<' . explode("<", $key['content'], 2)[1];
      $boopee =  explode(">", $cont, 2)[0] . '>';
      if($boops[$name]){
        if($boops[$name][$boopee]){
            $boops[$name][$boopee]++;
            $booped = $boops[$name][$boopee];
        }else{
          $boops[$name][$boopee] = 1;
          $booped = 1;
        }
      }else{
        $boops[$name] = array();
        $boops[$name][$boopee] = 1;
        $booped = 1;
      }
      $writeBoops = fopen('boops.txt', "w");
      fwrite($writeBoops, json_encode($boops, true));
      fclose($writeBoops);
      if($booped == 1){
        $msgend = " That's the first time!";
      }else{
        $msgend = " That's " . $booped . " times now!";
      }
      $msg = $name . " booped " . $cont . "!" . $msgend;
      $discord->channel->createMessage([
        'channel.id' => $channel,
        'content' => "$msg"
      ]);
    }

    // on !bal
    if(strpos($key['content'], '!bal') !== false){
      think();
      $name = '<@' .  $key['author']['id'] . '>';
      $discord->channel->createMessage([
        'channel.id' => $channel,
        'content' => $name . ", your balance is <:coin:284693468511469569>" . $key['bal'] . "."
      ]);
    }

    // on !send
    if(strpos($key['content'], '!send') !== false){
      think();
      $name = '<@' .  $key['author']['id'] . '>';
      $amnt = intval(explode(' ', 
        explode('send ', $key['content'])[1]
      )[0]);
      $too = explode('>', 
        explode('<@', $key['content'])[1]
      )[0];
      $out = transfer($key['author']['id'], $too, $amnt);
      $discord->channel->createMessage([
        'channel.id' => $channel,
        'content' => $name . " " . $out
      ]);
    }


  /*---------- /commands -----------*/ 
  }
}

$encdat = json_encode($temp, true);

mysqli_query($sql, "UPDATE logs SET data='$encdat', last='$last', running='ready' WHERE guild_id='$guild' AND channel_id='$channel'");

?>
