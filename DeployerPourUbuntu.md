En attendant la création de la tache personalisée :

  * export de la version voulue
  * création d'un dossier "package" par exemple, dans lequel on crée un dossier debian
  * on y place le contrib et le script postinstall
  * ajouter la version exportée dans package/usr/lib/phpserenamer
  * effectuer dpkg
  * supprimer dossier package