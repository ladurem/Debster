 <html>
 <head>
  <title>Instalation: Etape <?php echo intval($_GET['step']);?></title>
  <link rel="stylesheet" href="./css/style.css">
  <style type="text/css">

  .green{
    color: green;
  }
  .error{
    color: red;
  }
  a.button {
    color: #555;
    font: bold 12px Helvetica, Arial, sans-serif;
    text-decoration: none;
    padding: 7px 12px;
    position: relative;
    display: inline-block;
    -webkit-transition: border-color .218s;
    -moz-transition: border .218s;
    -o-transition: border-color .218s;
    transition: border-color .218s;
    background: #ddd;
    background: -webkit-gradient(linear,0% 40%,0% 70%,from(#F5F5F5),to(#F1F1F1));
    background: -moz-linear-gradient(linear,0% 40%,0% 70%,from(#F5F5F5),to(#F1F1F1));
    border: solid 1px #aaa;
    border-radius: 2px;
    -webkit-border-radius: 2px;
    -moz-border-radius: 2px;
  }
  a.button_disabled {
    color: #777;
    font: bold 12px Helvetica, Arial, sans-serif;
    text-decoration: none;
    padding: 7px 12px;
    position: relative;
    display: inline-block;
    -webkit-transition: border-color .218s;
    -moz-transition: border .218s;
    -o-transition: border-color .218s;
    transition: border-color .218s;
    background: #ddd;
    background: -webkit-gradient(linear,0% 40%,0% 70%,from(#F5F5F5),to(#F1F1F1));
    background: -moz-linear-gradient(linear,0% 40%,0% 70%,from(#F5F5F5),to(#F1F1F1));
    border: solid 1px #aaa;
    border-radius: 2px;
    -webkit-border-radius: 2px;
    -moz-border-radius: 2px;
  }
 
  </style>
  <meta charset='utf-8'/>
</head>
<body>
  <?php
  if (version_compare(PHP_VERSION, '5.0.0', '<')){
    echo '<div style="margin:20px auto;width:100%;text-align:center;">
    <p><b>Veuillez activer PHP5 sur votre hebergement!</b></p>
    <p><b>Please enable PHP5 on your hosting!</b></p>
    </div>';
    exit();
  }
function redirect($url,$tps=0) {
  echo ' <script language="javascript">document.location.href="'.$url.'"</script>';
}


$step = (empty($_GET['step'])) ? '' : $_GET['step'] ;


  switch ($step) {
    case '1':
    $necessaire = array();
    $necessaire['PHP version ≥ 5.1'] = version_compare(phpversion() > 0, '5.1') ? true : false;
    $necessaire['Extension MySQL'] = extension_loaded('mysql') ? true : false;
    $necessaire['Gestion des sessions'] = extension_loaded('session') ? true : false;
    $necessaire['Gestion de la fonction Hash'] = function_exists('hash') ? true : false;
    echo  '<h3 style="margin-bottom:5px;" >Vérification de la compatibilité avec votre hebergement</h3>  <table style="width:500px;background:#fff;" class="compat" ><tr>
    <td style="width:30%;"><b>Composant</b></td>
    <td style="width:20%;text-align:center;"><b>Compatible ?</b></td>';

    $error=false;
    foreach($necessaire as $data => $value){
      $etat = ($value) ? '<span class="green">ok</span>' : '<span class="error">X</span>' ;
      $error = ($value) ? false : true ;
      echo '<tr><td>'.($data).'</td><td style="text-align:center;">'.$etat.'</tr>';
      echo "\n";
    }
    echo '</table>';
    if(!$error)
      echo '<a href="./install.php?step=2" class="button" >Continuer</a>';
    else echo 'Corrigez les erreurs.';
    break;
    case '2':
    echo "Etape 2 : <b>Connexion a la Base de Donnée : </b> ";
    echo '<form action="install.php?step=3" method="POST">
    <label for="server">Serveur MYSQL : </label><input type="text" name="server" placeholder="localhost" id="server" required/><br />
    <label for="login">Login : </label><input type="text" name="login" id="login" required/><br />
    <label for="mdp">Mot de Passe  : </label><input type="password" name="mdp" id="mdp" /><br />
    <label for="name_bdd">Nom de la base de donnée : </label><input type="text" name="name_bdd" id="name_bdd" required/><br />
    <input type="submit" />';
    break;

    case '3':
    echo 'Etape 3 : <b>Verification de la connexion</b><br /><br />';
    $link = @mysqli_connect($_POST['server'],$_POST['login'], $_POST['mdp'], $_POST['name_bdd']);
    $connect = mysqli_connect_error();
    if(preg_match('#Unknown MySQL server host#', $connect)){
      echo '<span class="error">Serveur MySQL inconnu</span>';
    }else if(preg_match('#Access denied for user#', $connect)){
      echo '<span class="error">Erreur de connexion au serveur MYSQL</span>';
    }else if(preg_match('#Unknown database#', $connect)){
      echo '<span class="error">Base de donnée inconnue</span>';
    }

    if($link) { 
      echo '<span class="green">Connexion reussie.</span>';
$content = '<?php 
  $link = @mysqli_connect("'.$_POST['server'].'","'.$_POST['login'].'","'.$_POST['mdp'].'","'.$_POST['name_bdd'].'");
  if (mysqli_connect_errno()) {
  ?>
  <doctype html>
  <html>
  <head>
  <meta charset="utf_8"/>
  </head>
  <body>
    Échec de la connexion a la base de données
  </body>
  </html>
  <?php
    exit();
}
?>';

$fp = fopen('./include/connectdb.php', 'w');
if(!@fwrite($fp, $content)) die("<span class='error'>Erreur lors de l'ecriture du fichier de configuration.</span>");
if(!@fclose($fp)) die("<span class='error'>Erreur lors de l'ecriture du fichier de configuration.</span>");
if(!@chmod ('./include/connectdb.php', 0666)) die("<span class='error'>Erreur lors du changement des droits du fichier.</span>");
       echo '<br /><span class="green">Fichier de configuration crée.</span>';
      echo '<br /><br /><a href="./install.php?step=4" class="button" >Continuer</a>';
    } else {
     echo '<br /><br /><a href="./install.php?step=2" class="button" >Retour</a>';
   }
   break;
case '4':
 echo "Etape 4 :<b>Création des tables SQL</b>";
include('./include/connectdb.php');
$table = '
CREATE TABLE IF NOT EXISTS `dettes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_source` int(11) NOT NULL,
  `user_cible` int(11) NOT NULL,
  `montant` int(11) NOT NULL,
  `statut` enum(\'0\',\'1\',\'2\') NOT NULL DEFAULT \'0\',
  `date` int(12) NOT NULL,
  `msg_open` varchar(255) NOT NULL,
  `msg_close` varchar(255) NOT NULL,
  `date_close` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';
mysqli_query($link,$table) or die(mysqli_error($link));
$table = '

CREATE TABLE IF NOT EXISTS `friends` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_friend_1` int(11) NOT NULL,
  `id_friend_2` int(11) NOT NULL,
  `accepted` enum(\'0\',\'1\') NOT NULL DEFAULT \'0\',
  `date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';
mysqli_query($link,$table) or die(mysqli_error($link));
$table = '
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail` varchar(80) NOT NULL,
  `nickname` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `lastname` varchar(40) NOT NULL,
  `firstname` varchar(40) NOT NULL,
  `birthyear` year(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
';
mysqli_query($link,$table) or die(mysqli_error($link));
echo '<br /><br /><span class="green">Table crées.</span>';
  echo '<br /><br /><a href="./install.php?step=5" class="button" >Continuer</a>';


  break;
  case '5':
 echo "Etape 5 :<b>Remplissage des tables</b><br /><br />";
include('./include/connectdb.php');

$table = "INSERT INTO `dettes` (`id`, `user_source`, `user_cible`, `montant`, `statut`, `date`, `msg_open`, `msg_close`, `date_close`) VALUES
(1, 1, 2, 13, '2', 1365974830, 'Baguette de pain', 'Cadeaux ! ', 1365974912),
(2, 2, 1, 27, '1', 1365974856, 'Cinema', '', 1365974892),
(3, 1, 2, 75, '0', 1365974873, 'Cotisation BDE', '', 0);";
mysqli_query($link,$table) or die(mysqli_error($link));
$table = "INSERT INTO `friends` (`id`, `id_friend_1`, `id_friend_2`, `accepted`, `date`) VALUES (1, 1, 2, '1', 65974068);";
mysqli_query($link,$table) or die(mysqli_error($link));
$table = "INSERT INTO `user` (`id`, `mail`, `nickname`, `password`, `lastname`, `firstname`, `birthyear`) VALUES
(1, 'member_1@debster.fr', 'membre1', 'f4a05b724c33e7eac11114c85323fe41e86dcbf2b46167f0e0b8583e6aa8c32a', 'Nom1', 'Prenom1', 1992),
(2, 'member_2@debster.fr', 'membre2', 'c6ebf65ea6e12597933ea70478c6b0f179650c0bec83f36e1542c3f3bd39c595', 'Nom2', 'Prenom2', 1990);
";
mysqli_query($link,$table) or die(mysqli_error($link));
echo '<br /><br /><span class="green">Tables remplies.</span>';
echo '<br /><br /><a href="./install.php?step=6" class="button" >Continuer</a>';

    break;
    case '6':
 echo "Etape 6 :<br /><br />";
echo '<br /><br /><span class="green">Instalation terminée ! </span>';
echo '<br /><br /><a href="./install.php?step=7" class="button" >Continuer</a><em>(En cliquant sur continuer, le fichier d\'instalation sera supprimé.)</em>';
     break;
     case '7':
echo '<span class="green">Install.php supprimé !</span>';
redirect('./index.php'); 
unlink('./install.php');
       break;
   default:
   echo '<div style="text-align: center;margin:30px auto;">
   <h2>Bienvenue sur DebSter V1.0.1</h2>
   <p>L\'assistant va vous guider à travers les étapes de l\'installation de votre portail...<br /><br /><b>Merci de completer tous les champs.</b></p><a href="./install.php?step=1" class="button" >Démarrer l\'installation</a>
   </div><hr style="margin-top:30px;margin-bottom:15px;width:90%;" />
   ';
   break;
 }


 ?>
</body>
</html> 