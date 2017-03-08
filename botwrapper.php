<?php

  $exTime = (intval(time()) + 63);

  include_once __DIR__.'/config.php';

  heartbeat();

  mysqli_query($sql, "UPDATE logs SET running='ready' WHERE guild_id='$guild' AND channel_id='$channel'");
  sleep(3);

  while(intval(time()) < $exTime){
    if(isRunning() == 'ready'){
      include __DIR__.'/bot.php';
    }
    usleep(200000);
  }

  mysqli_query($sql, "UPDATE logs SET running='ready' WHERE guild_id='$guild' AND channel_id='$channel'");
  die();

?>
