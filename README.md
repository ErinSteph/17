# 17
~~I do not expect anybody to be able to install and run this, it's just for people who were asking to see the source.~~

~~If you REALLY REALLY wanna run it, [ask](https://discord.gg/MpUB5Hp), so I can probably not help you.~~

Installing this is getting kind of doable now.

It's written to be run in Apache with Cron Jobs, so quickly how to set that up:

1. Install apache2, mysql, phpmyadmin, curl, etc.
2. Import database schema in phpmyadmin
3. Install php composer
4. Run ~$ php composer require restcord/restcord
5. Run ~$ php composer require textalk/websocket
6. Upload or clone the php files to the web root
7. Fill out user settings in config.php
8. Create a file in the web root called boops.txt with 0777 permissions
9. Run ~$ crontab -e
10. Add a line: ~$ * * * * * curl http://your_site_or.ip/botwrapper.php

And the bot should now be running.

