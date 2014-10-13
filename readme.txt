-------------------------------------------------
Fichier README.TXT
Projet DEBSTER
Charles-Damien PAPOT & Martin LADURE
Avril 2013
-------------------------------------------------

Instalation : 
Lors de la première utilisation, le script install.php, est automatiquement appelé, celui-ci creera les tables automatiquement, et les remplira également.
Dès l'instalation terminée, deux membres sont crées :
Pseudo du premier membre : member_1@debster.fr , mot de passe-> membre1 
Pseudo du deuxieme membre :member_2@debster.fr , mot de passe -> membre2 
Par défaut, ces deux membres sont amis et  quelques dettes ont été ajoutés.
Dés la fin de l'instation le fichier install.php est automatiquement supprimé.
Pour que l'instalation se déroule correctement, le dossier include doit être accessible en écriture (si ce n'est pas le cas, le script vous avertira).



Fonctionalités : 
Les utilisateurs ont la possibilité de s'incrire, pour cela ils doivent fournir un email (qui sera unique dans la base de donnée), un mot de passe (qui sera crypté dans la base de donnée), son nom, prénom et sa date de naissance (pour connaitre son age).
Une fois inscrit, il doit se connecter a l'aide de son adresse email et son mot de passe.
Une fois connecté :
L'utilisateur a le choix d'acceder au module de :
Amitié : Celui-ci gère l'amitié entre deux membres.
Dette : Celui- gère les dettes entre deux amis
Profil : Permet de modifier son profil

Détail :
Le module amitié :
Par defaut, il liste les amitiés en cours/acceptés. Nous pouvons ajouter un ami, en cliquant sur le lien ajouter : On nous propose un champ texte qui propose une auto-complesion du champ permet de trouver rapidement un pseudo d'un ami.
Une fois la demande envoyée, le destinataire de la demande doit l'aprouver pour accepter ce lien entre les deux membres.
Il est evidement possible de supprimer le lien entre les deux membres en cliquant sur supprimer.

Le module dette : 
Par défaut, il liste les dettes en cours/ remboursées/annulées. Une icone sur la premiere colonne permet de rapidement voir l'etat de la dette  : Noir = dette annulée, vert = dette remboursée et jaune = dette en cours. 
Si une dette est en cours, l'utilisateur a la possibilité de  : rembourser la dette, editer la dette, annuler la dette ou la consulter en détail.
Si une dette est remboursée ou annulée : On ne peux que la consulter.
Pour ajouter une dette, il suffit de cliquer sur ajouter.
L'utilisateur a le choix de choisir qui a payé la dette(si il est payeur ou profiteur).
Ensuite il doit choisir le pseudo de son ami, une auto-complession est faite avec sa liste d'amis. Il choisit le montant, et le libéllé de la dette.

Le module de profil :
Par defaut, le module liste les informations du membre. Il peut changer ces informations ou changer son mot de passe. Il a la possibilité de changer toutes ses informations contenues dans la base de donnée.

---
Architecture 
./dettes.php : Module de dette
./friends.php : Module d'amitée
./user.php    : Module de profil
./install.php : Module d'installation
./css/style.css : Feuille CSS du site
./css/jquery-ui.css : Feuille de style pour l'autocompletion
./include/connectdb.php : Contient les données de connexion a la base de donnée
./include/disconnectdb.php : Deconnexion a la base de donnée
./include/footer.php : Contient les bas de page
./include/header.php : Contient les hauts de page
./include/jquery-ui.js : Librairie Jquery
