install:
	#Copie de l'ensemble des ichiers
	cp -v -R *[^Makefile$$] /

	##Chmod sur le fichier de lancement
	chmod +x /usr/lib/phpserenamer/data/phpserenamer

	##Chmod sur le dossier cache
	chmod -R 777 /usr/lib/phpserenamer/cache/

	##Ajout du lien symbolique dans le dossier bin
	ln -s -f /usr/lib/phpserenamer/data/phpserenamer /usr/bin/phpserenamer

uninstall:
	rm -rf -v /usr/lib/phpserenamer
	rm -f -v /usr/bin/phpserenamer
