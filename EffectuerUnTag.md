  * Créer un ticket (dans la milestone actuelle) indiquant de faire le tag

  * Modifier le numéro de version dans le fichier `data/debianPackage/DEBIAN/control`

  * Modifier le numéro de version la classe srutils `lib/util/srUtils.class.php`

  * Effectuer une commande semblable à celle-ci
```
$ svn copy https://phpserenamer.googlecode.com/svn/trunk https://phpserenamer.googlecode.com/svn/tags/0/0.1.0 -m "Tag de la 0.1.0 (Fixes issue 000)"
```