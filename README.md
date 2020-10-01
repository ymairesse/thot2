l# Thot
Thot est, dans la mythologie égyptienne antique, le scribe des dieux.

Application en ligne pour la communication avec les élèves et les parents dans une école L'application ne fonctionne que parallèlement à la plate-forme Zeus/Edu dont elle utilise la base de données.

L'accès est personnalisé et conditionné à un nom d'utilisateur et un mot de passe.

En début de production, elle permet déjà:
- aux élèves de consulter leur bulletin scolaire et les résultats de leurs évaluations en ligne
- aux enseignants / coordinateurs / direction de déposer des notifications dans l'interface web des élèves; ces notifications peuvent être adressées à un élève en particulier, à toute une classe, à tout un niveau d'étude, à tous les élèves
- d'envoyer (ou pas) un avertissement par mail lors du dépôt d'une notification à un élève ou à une classe
- de connaître les dates d'anniversaires du jour (parmi les élèves de l'école)
- l'accès au journal de classe en ligne contenant les activités de chaque cours et les travaux à effectuer
- l'accès à des documents et des répertoires partagés par les professeurs et les éducateurs
- la consultation par les parents de l'élève de toutes les infos disponibles pour les élèves et des fiches de comportement
- la participation à des forums initiés par les enseignants
- le contact par mail avec les enseignants sur base d'un formulaire
- l'inscription aux réunions de parents par les parents de l'élève

Le dépôt https://github.com/ymairesse/thot n'est plus mis à jour; il contient une version plus ancienne de l'application. Elle est conservée pour des raisons historiques...

# Installation de l’application Thot

Télécharger l’archive .zip depuis https://github.com/ymairesse/thot2 et extraire dans un répertoire local.
Configurer le fichier /config.inc.php comme dans l’application parente Zeus. Celle-ci doit avoir également été configurée, y compris les paramètres généraux dans l’interface web d’administration.

Personnaliser le fichier /config.ini

Changer l’image de fond /images/background.jpg

Personnaliser le logo de l’école /images/logoEcole.png (image png environ 100x100)

Déployer l’ensemble sur le serveur web par FTP.

Les utilisateurs (élèves uniquement) sont définis dans l’application parente Zeus. Il n’existe pas d’interface d’administration dans l’application Thot. Il n'existe pas d'utilisateur "prof".
