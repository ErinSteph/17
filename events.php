<?php

  include_once __DIR__.'/config.php';
  global $sql, $channel, $guild, $botID;

  function getOddsList(){
    global $sql, $channel, $guild, $botID;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://myanmarunicorn-football-v1.p.mashape.com/odds?bookmaker_id=8&type=1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Mashape-Key: ' . $db['mashapekey']));
    $response = curl_exec($ch);
    curl_close($ch);
    $responseObject = json_decode($response, true);
    $odd = array();
    foreach($responseObject['data'] as $ec){
      if(isset($ec['live']['home']) && isset($ec['live']['away'])){
        $odd[$ec['fixture_id']] = array(
          'home' => $ec['live']['home'],
          'away' => $ec['live']['away']
        );
      }
    }
    return $odd;
  }
  
  function getEventList(){
    global $sql, $channel, $guild, $botID;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://myanmarunicorn-football-v1.p.mashape.com/fixtures');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Mashape-Key: ' . $db['mashapekey']));
    $response = curl_exec($ch);
    curl_close($ch);
    $responseObject = json_decode($response, true);
    $odds = array();
    $eventOdds = getOddsList();
    foreach($responseObject['data'] as $ev){
      if($ev['status'] == 'FT'){
        $que = "SELECT * FROM bets WHERE fixture='" . $ev['id'] . "' AND guild='" . $guild . "' AND channel='" . $channel . "'";
        $checkSql = mysqli_query($sql, $que);
        if(mysqli_num_rows($checkSql) > 0) {
          while($row = mysqli_fetch_assoc($checkSql)) {
            $won = false;
            $score = 'Home: ' . $ev['homeTeam']['goals'] . ' Away: ' . $ev['awayTeam']['goals'] . '.'; 
            if($ev['homeTeam']['goals'] > $ev['awayTeam']['goals']){
              if($row['team'] == 'home'){
                transfer($botID, $row['user'], intval($row['amount']*$row['odds']));
                say('<@' . $row['user'] . '> You won <:coin:284693468511469569>' . intval($row['amount']*$row['odds']) . ' on ' . $ev['homeTeam']['name'] . ' to beat ' . $ev['awayTeam']['name'] . '! ' . $score);
              $won = true;
              }
              
            }else if($ev['homeTeam']['goals'] < $ev['awayTeam']['goals']){
              if($row['team'] == 'away'){
                transfer($botID, $row['user'], intval($row['amount']*$row['odds']));
                say('<@' . $row['user'] . '> You won <:coin:284693468511469569>' . intval($row['amount']*$row['odds']) . ' on ' . $ev['awayTeam']['name'] . ' to beat ' . $ev['homeTeam']['name'] . '! ' . $score);
                $won = true;
              }
            }else{
              transfer($botID, $row['user'], intval($row['amount']));
              say('<@' . $row['user'] . '> Match draw: you got<:coin:284693468511469569>' . intval($row['amount']) . ' back on ' . $ev['awayTeam']['name'] . ' to beat ' . $ev['homeTeam']['name'] . '! ' . $score);
              $won = true;
            }
            
            if($won == false){
              say('<@' . $row['user'] . '> You lost <:coin:284693468511469569>' . intval($row['amount']) . ' on ' . $ev['awayTeam']['name'] . ' to beat ' . $ev['homeTeam']['name'] . '. ' .  $score);
            }

          }
          $que2 = "DELETE FROM bets WHERE fixture='" . $ev['id'] . "' AND guild='" . $guild . "' AND channel='" . $channel . "'";                      
          $checkSql2 = mysqli_query($sql, $que2);
        }
      }else if($ev['status'] == 'Sched'){
        if(isset($eventOdds[$ev['id']]['home'])){
          $odds[$ev['id']] = array(
            'id' => $ev['id'],
            'hometeam' => $ev['homeTeam']['name'],
            'homeodds' => $eventOdds[$ev['id']]['home'],
            'awayteam' => $ev['awayTeam']['name'],
            'awayodds' => $eventOdds[$ev['id']]['away'],
            'time' => explode('Z',
              explode('T',
                $ev['time']
              )[1]
            )[0]
          );
        }
      }
    }
    return $odds;
  }
 
  $data = getEventList();
  
  $writeOdds = fopen('odds.txt', "w");
  fwrite($writeOdds, json_encode($data, true));
  fclose($writeOdds);
 
  foreach($data as $i){
    echo $i['time'] . ' - Fixture ' . $i["id"] . ' - Home: ' . $i["hometeam"] . ' @ ' . $i['homeodds'] . ' / Away: ' . $i["awayteam"] . ' @ ' . $i['awayodds'] . '<br>';
  }


?>
