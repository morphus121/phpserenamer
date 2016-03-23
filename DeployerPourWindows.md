# Introduction #

Utilisation de Inno Setup Compiler.

Ajout de php-gtk dans le dossier lib/vendor/php-gtk2.

# Pour afficher les images des boutons #

Modifier le fichier gtkrc.(dans lib/vendor/php-gtk2/etc/gtk-2.0),

Modifier la ligne 5, gtk-button-images = 0.

Changer le 0 to 1et enregistrer.

# Le code #

```
[Setup]
AppId={{856EE7F8-8BF2-4C41-8F91-DD667BB8C922}
AppName=phpserenamer
AppVerName=phpserenamer 0.1.0
AppPublisherURL=http://code.google.com/p/phpserenamer/
AppSupportURL=http://code.google.com/p/phpserenamer/
AppUpdatesURL=http://code.google.com/p/phpserenamer/
DefaultDirName={pf}\phpserenamer
DefaultGroupName=phpserenamer
OutputBaseFilename=phpserenamer-0.1.0
Compression=lzma
SolidCompression=yes

[Languages]
Name: "french"; MessagesFile: "compiler:Languages\French.isl"

[Files]
Source: "C:\Documents and Settings\User\Mes documents\phpserenamer\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs

[Icons]
Name: "{group}\phpserenamer"; Filename: "{app}\lib\vendor\php-gtk2\php-win.exe {app}\symfony main"

[Registry]
Root: HKCR; Subkey: "Directory\shell\phpserenamer"; ValueType: string; ValueName: ""; ValueData: "phpserenamer"; Flags: createvalueifdoesntexist
Root: HKCR; Subkey: "Directory\shell\phpserenamer\command"; ValueType: string; ValueName: ""; ValueData: """{app}\lib\vendor\php-gtk2\php-win.exe"" ""{app}\symfony"" main ""%1"""; Flags: createvalueifdoesntexist


```