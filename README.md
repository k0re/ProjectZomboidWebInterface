# ProjectZomboidWebInterface

Webserver Based on a LGSM installed Project Zomboid installation. 
Useful for editing Mods and Starting/Restarting/Updating the Game.

1. Run composer install to install required libraries.
2. Create database based on sql file.
3. visudo for giving www-data the following sudo rights:
	www-data    ALL= (pzserver) NOPASSWD: /home/pzserver/pzserver *
