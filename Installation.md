# Ubuntu #

_Installation de php-gtk_

Fonctionnant sous php-gtk, et celui-ci ne se trouvant pas dans les dépots officiels, téléchargez puis installez [ceci](http://phpserenamer.googlecode.com/files/php5-gtk2_2.0.1-1_i386.deb) tout d'abord.

_Deux modes d'insallation_

Vous pouvez soit installer directement depuis le paquet deb, ou configurer vous sources pour utiliser le PPA (Personal Package Archives), ce qui permettra d'avoir automatiquement les mises à jour.

## Depuis debian package ##

Téléchargez ensuite la dernière version pour ubuntu [ici](http://code.google.com/p/phpserenamer/downloads/list) pour installer phpserenamer.

## Depuis PPA ##

Ajout des deux lignes dans le sources.list
(ou système -> administration -> sources de mise à jour -> logiciels de tierce partie -> ajouter) :
```
deb http://ppa.launchpad.net/adriengallou/ppa-phpserenamer/ubuntu jaunty main 
deb-src http://ppa.launchpad.net/adriengallou/ppa-phpserenamer/ubuntu jaunty main 
```

Ajouter la clef à la liste des clef acceptables :
```
sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 46B20A27
```

Mettre à jour la liste des paquets disponibles :
```
sudo apt-get update
```

phpserenamer sera alors disponible via :
```
sudo apt-get install phpserenamer
```
et les mises à jour seront alors proposées automatiquement.

# Windows #

Pas besoin d'installer php-gtk, celui-ci ce sera automatiquement installé en même temps que phpserenamer.

Téléchargez la dernière version pour windows ici [ici](http://code.google.com/p/phpserenamer/downloads/list) puis suivez les instructions d'installation.

# Linux #

Téléchargez le tarball, puis effectuez cette série de commandes :
```
mkdir phpserenamer
tar -zxf phpserenamer-0.3.2.tar.gz --directory=phpserenamer
cd phpserenamer
make install
```