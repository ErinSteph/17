<?php
include_once __DIR__.'/config.php';
include __DIR__.'/vendor/autoload.php';
use WebSocket\Client;

$sock_client = new Client("wss://gateway.discord.gg/?encoding=json&v=6");

$sock_push = '{"op": 2,"d":{"token": "' . $token . '", "properties": {"$browser": "Restcord Gateway Connect"}}}';

$sock_re = json_decode($sock_client->receive(), true);

var_dump($sock_re);

if($sock_re['op'] === 0){
  return;
}

if($sock_re['op'] === 10){
  $sock_client->send($sock_push);
}

?>