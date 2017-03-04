# 17
~~I do not expect anybody to be able to install and run this, it's just for people who were asking to see the source.~~

~~If you REALLY REALLY wanna run it, [ask](https://discord.gg/MpUB5Hp), so I can probably not help you.~~

Installing this is getting kind of doable now.

It's written to be run in Apache with Cron Jobs, so quickly how to set that up:

Install apache2, mysql, phpmyadmin, curl, etc.
Import database schema in phpmyadmin
Install php composer
Run ~$ php composer require restcord/restcord
Run ~$ php composer require textalk/websocket
Upload or clone the php files to the web root
Fill out user settings in config.php
Run ~$ crontab -e
Add a line: ~$ * * * * * curl http://your_site_or.ip/botwrapper.php

And the bot should now be running.

