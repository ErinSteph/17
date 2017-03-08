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
  mysqli_query($sql,"INSERT INTO logs (channel_id,guild_id,data,heartbeat,last) VALUES ('" . $channel . "','" . $guild . "','[]', '" . (intval(time()) + 100) . "', '" . getChannelLast() . "')");

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

    $name = '<@' .  $key['author']['id'] . '>';
    $isAdmin = false;
    if(in_array($key['author']['id'], $adminIDs) === true) $isAdmin = true;
  /*---------- commands -----------*/


    // DESCRIBE IMAGE
    if($key['attachments'][0]){
      think();
      $imagInfo = imgRead($key['attachments'][0]['url']);
      $remind = $key['content'];
      $key['content'] = $imagInfo;
      $reply = chatAI($key);
      $key['content'] = $remind;
      say($name . ' ' . $imagInfo . '!');
    }

    // BOT MENTIONED
    if(strpos($key['content'], '<@' . $botID . '>') !== false || rand(1,90) == 10){
      think(); 
      $reply = chatAI($key);
      say($name . ' ' . $reply);
    } 

    // on !17
    if(strpos($key['content'], '!17') !== false){
      think();
      say("Hi " . $name . "!");
    }
    
    // on !boop
    if(strpos($key['content'], '!boop') !== false){
      $boops = json_decode(file_get_contents('boops.txt'), true);
      think();
      if(cockblock($key, 'boop it bro') === true){
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
      say($msg);
      }
    }
  
    // on !bal
    if(strpos($key['content'], '!bal') !== false){
      think();
      if(cockblock($key, 'need balance') === true){          
        say($name .  ", your balance is <:coin:284693468511469569>" . $key['bal'] . ".");
      }
    }

    // on !send
    if(strpos($key['content'], '!send') !== false){
      think();
      if(cockblock($key, 'send coins') === true){
        $amnt = intval(explode(' ', 
          explode('send ', $key['content'])[1]
        )[0]);
        $too = explode('>', 
          explode('<@', $key['content'])[1]
        )[0];
        $out = transfer($key['author']['id'], $too, $amnt);
        say($name . ' ' . $out);
      }
    }

    // on !odds
    if(strpos($key['content'], '!odds') !== false){
      think();
      if(cockblock($key, 'get odds') === true){
        $odds = json_decode(file_get_contents('odds.txt'), true);
        $datas = '';
        $ni = 10;
        foreach($odds as $i){
          if($ni > 0){
            $ni--;
            $datas .= $i['time'] . ' - Fixture **' . $i["id"] . '** - **Home:** ' . $i["hometeam"] . ' @ **' . $i['homeodds'] . '** / **Away:** ' . $i["awayteam"] . ' @ **' . $i['awayodds'] . '**' . PHP_EOL;
          }
        }
        say('**Next 10 Matches:**' . PHP_EOL . '*!bet 10 1349627 home*' . PHP_EOL .  $datas);
      }
    }
    
    // on !bet
    if(strpos($key['content'], '!bet') !== false){
      think();
      if(cockblock($key, 'place bet') === true){
        $amount = explode('!bet ', $key['content'])[1];
        $bits = explode(' ', $amount);
        $amount = intval($bits[0]);
        $fix = intval($bits[1]);
        bet($key, $fix, $amount);
      }
    }

    // on !priv
    if(strpos($key['content'], '!priv') !== false){
      think();
      if($isAdmin === true){
        say($name . ', you have Admin privilege.');       
      }else{
        say($name . ', you have User privilege.');
      }
    }


  /*---------- /commands -----------*/ 
  }
}

$encdat = json_encode($temp, true);

mysqli_query($sql, "UPDATE logs SET data='$encdat', last='$last', running='ready' WHERE guild_id='$guild' AND channel_id='$channel'");

?>
