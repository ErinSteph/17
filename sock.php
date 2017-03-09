<?php

include_once __DIR__.'/config.php';
include __DIR__.'/vendor/autoload.php';
use WebSocket\Client;
global $nowplaying;

$sock_client = new Client("wss://gateway.discord.gg/?encoding=json&v=6");

$sock_push = '{"op": 2,"d":{"token": "' . $token . '", "properties": {"$browser": "Restcord Gateway Connect"}}}';

if(isset($nowplaying)){
  $sock_push_game = '{"op": 3,"d":{"idle_since": null, "game":{"name":"' . $nowplaying . '"} }}';
}

$sock_re = json_decode($sock_client->receive(), true);
var_dump($sock_re); echo '<br><br>';

if($sock_re['op'] === 0){
  return;
}

if($sock_re['op'] === 10){
  $sock_client->send($sock_push);
}

$sock_re = json_decode($sock_client->receive(), true);
var_dump($sock_re); echo '<br><br>';

if(isset($nowplaying)){
  $sock_client->receive();
  $sock_client->receive();
  $sock_client->send($sock_push_game);
  $sock_re = json_decode($sock_client->receive(), true);
  var_dump($sock_re); echo '<br><br>';
  $sock_client->send($sock_push_game);
}

?>       