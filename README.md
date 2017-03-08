# 17
Installing this is getting kind of doable now.

It's written to be run in Apache with Cron Jobs, so quickly how to set that up:

1. Install server basics like [LAMP stack](http://howtoubuntu.org/how-to-install-lamp-on-ubuntu), [phpmyadmin](https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-phpmyadmin-on-ubuntu-12-04), etc.
2. Import database schema in phpmyadmin
3. Install [php composer](https://getcomposer.org/)
4. Run `php composer require restcord/restcord`
5. Run `php composer require textalk/websocket`
6. Upload or clone the php files to the web root
7. Fill out user settings in config.php, including (free) API keys from:
  * [Personality Forge](http://www.personalityforge.com/)
  * [Microsoft Cognitive Services (Computer Vision API key)](https://www.microsoft.com/cognitive-services/en-us/sign-up)
  * [Mashape](https://market.mashape.com/)
  * [Discord](https://discordapp.com/developers/applications/me)
9. Run `crontab -e` and add these lines: 
  * `* * * * * curl http://your_site_or.ip/botwrapper.php`
  * `* * * * * curl http://your_site_or.ip/events.php`

And the bot should now be running.

If you need help, [ask](https://discord.gg/MpUB5Hp).

