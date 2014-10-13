<?php
session_start();
function coupe($str,$limit=20){
	if (strlen($str) > $limit){
		$str = substr($str, 0, $limit);
		$last_space = strrpos($str, " ");
		$str = substr($str, 0, $last_space)." (...)";
	}
	return $str;
}
function s($nb){#accorde automatiquement les mots
	return ($nb>1) ? 's' : '' ;
}
function temps($date){
	$sec = time() - 60;
	$min = time() - 60*60;
	$heu = time() - 60*60*3;
	$time = time() - $date;
  //Recupère time de ce matin 0h00-hier matin meme heure
  $time_matin = mktime(0, 0, 0, date('m'),  date('d'), date('Y'));//Ce matin 0h0
  $time_hier_matin = mktime(0, 0, 0, date('m'),  date('d')-1, date('Y'));//hier matin 0h0
  if($date > $sec) //Si on $date moins de 1min
  return 'il y a '. $time.'  seconde'.s($time);
elseif($date > $min)//si moins d'une heure
return 'il y a '.floor($time/60).' minute'.s(floor($time/60));
elseif($date > $heu){ //Moins de 3heures

	$nb_heure = floor($time/3600);
	$nb_min = floor(($time%3600)/60);
	$nb_sec = $time%(3600/60);
	$retour = 'il y a ' .$nb_heure. ' heure'.s($nb_heure);
	if($nb_min != 0)
		$retour .= ' & '.$nb_min.' minute'.s($nb_min);
	return $retour;
}

elseif($date > $time_hier_matin) {//Si plus de 3heures, et hier
//intval pour enlever le 0 si < 10 (date('H') peut revoyer 09, là c'est 9  )

  $nb_heure = intval(date('H', $date));//nombre d'heures
  $nb_min =   date('i', $date);//nombre de minutes
  $nb_sec =   date('s', $date);//nombre de secondes
if($date > $time_matin) //Si aujourd'hui
$retour = 'aujourd\'hui';
else
	$retour = 'hier';
if($nb_heure == 0)
	$retour .= ' à minuit';
else 
	$retour .=' à ' .$nb_heure .' heure'.s($nb_heure);
return $retour;
}

else {//Avant-hier ou avant

	$nb_an =  intval(date('Y', $date));
	$nb_mois =  date('m', $date);
	$nb_jour =  date('d', $date);
	$nb_heure = date('H', $date);
	$nb_min =   date('i', $date);
	$nb_sec =   date('s', $date);
	$retour = 'le ' .$nb_jour. '/' .$nb_mois. '/' .$nb_an. ' à ' .$nb_heure.':'.$nb_min;
}

return $retour;

}

function error($str) {
	echo '<center><span class="rouge"><img src="./img/error.png" alt="error" title="error" /> '.$str.'</span></center>';
	include("./footer.php");


}
function valid($str) {
	return '<center><span class="vert"><img src="./img/valid.png" alt="valid" title="valid" /> '.$str.'</span></center>';
}
function redirect($url,$tps=2000) {
echo '<script language="javascript">
<!--
function redirect() 
{
window.location="'.$url.'" 
}
setTimeout("redirect()",'.$tps.'); 
-->
</SCRIPT>';

}

?>

<!DOCTYPE html>
<html>

<head>
	<meta charset='utf-8'/>
	<title>Debster : Split bills with your friends</title>
	<link rel="stylesheet" href="./css/jquery-ui.css" />
	<link rel="stylesheet" href="./css/style.css">
	<script src="./js/jquery-ui.js"></script>
	<script type="text/javascript">
function validate_passwd(data) {
	if (data.password.value != data.password2.value) {
		alert('The two passords don\'t match!');
		data.password.focus();
		return false;
	}
	else if (data.password.value == data.password2.value) {
		if ( data.password.value.length < 8) {
			alert('8 caractères minimums!');
			data.password.focus();
			return false;
		}
		else
			return true;
	}
	else {
		data.password.focus();
		return false;
	}
}
</script>

</head>
<body>
	<HEADER>
		<div id='bandeau_haut'>
			<div class='width_limit'>
				<table id='top'>
					<tr>
						<td><a href='./index.php'><img src='./img/logo.png' alt="Logo" title="logo"/></a> </td>
						<td>
							<?php 
							if (!isset( $_SESSION['id'])) {
								echo "<p>Arretez de faire les comptes, Debster le fait pour vous ! </p>";
							}
							else {
								if ( (time() - $_SESSION['time']) > 3600 )
									session_destroy();
								else {
									include('connectdb.php');
									$qe = 'SELECT * FROM friends WHERE accepted ="0" AND (id_friend_2="'.intval($_SESSION['id']).'")';
									$new_friend_request = mysqli_query($link,$qe);
									$nb_request_friend = mysqli_num_rows($new_friend_request)>0 ? '<span class="rouge"><a href="./friends.php">'.mysqli_num_rows($new_friend_request).'</a></span>' : '0';
									echo 'Votre Compte : '.htmlentities(stripslashes(($_SESSION['pseudo']))).' ('.$nb_request_friend.')</td><td><a href="./user.php?action=disconnect"><img id="deconnexion" src="./img/deconnexion.png" alt="Deconnexion"/></a>';
									include('disconnectdb.php');
								}
							}
							?>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</HEADER>
	<div id='content'>

