<?php
include('./include/header.php');
include('./include/connectdb.php');
if ( !isset( $_SESSION['id']) AND !isset($_SESSION['pseudo'])){
	echo "Cette partie est uniquement reservée aux inscrits et connectés.<br />";
	die();
}
$act = (empty($_GET['act'])) ? '' : $_GET['act'] ;


switch ($act) {
	case 'add':
	$submit = (empty( $_POST['submit'])) ? '' :  $_POST['submit'] ;

	if($submit) {

		$result = mysqli_query($link,'SELECT id FROM user WHERE mail="'.mysqli_real_escape_string($link,$_POST['cible_id']).'"');
		$mail_ami = mysqli_fetch_array($result);
		
if(empty($mail_ami['id'])){ # verification que le membre existe
	die(error("Votre ami n'existe pas, j'en suis désolé ..."));
	}
$que='SELECT * FROM friends WHERE  ((id_friend_1='.$_SESSION['id'].' AND id_friend_2='.$mail_ami['id'].') OR (id_friend_2='.$_SESSION['id'].' AND id_friend_1='.$mail_ami['id'].'))   ';
$result_ami = mysqli_query($link,$que) or die(mysqli_error());
$ami_ensemble = mysqli_fetch_array($result_ami);
if(!empty($ami_ensemble['id'])){  # verification que le membre existe
	die(error('Vous êtes deja ami avec '.htmlentities($_POST['cible_id'])));
	}

$user_source = intval($_SESSION['id']);
$user_cible = intval($mail_ami['id']);
$sql = 'INSERT INTO friends (`id_friend_1`, `id_friend_2`,`date`) VALUES ('.$user_source.', '.$user_cible.','.time().')';
mysqli_query($link,$sql) or die('ERROR<br />'.$query.mysqli_error());
echo valid("Votre demande d'amitiée a été ajoutée !");
echo 'Redirection vers l\'accueil des amis.<br />';
redirect("./friends.php");
die();


}
echo '<h2>Ajouter un ami</h2>';
?>
<script type="text/javascript">
$(function() {
	var availableTags = [
	<?php
	$ami_id = mysqli_query($link,'SELECT mail FROM user WHERE id!='.intval($_SESSION['id'])) or die(mysqli_error());
	while($ami_b = mysqli_fetch_array($ami_id)) {
		echo '"'.htmlentities(stripslashes($ami_b['mail'])).'",';
		echo "\n";
	}

	?>
	];
	$( "#cible_id" ).autocomplete({
		source: availableTags
	});
});
</script>

<form id="add_friend" method="POST" action="./friends.php?act=add" oninput="total.value = (montant.valueAsNumber/2)">
	<br />
	<label>Email de votre ami:</label>
	<input type="text" id="cible_id" name="cible_id" placeholder="Jean_Mich_du_03@debster.fr" autocomplete="off"   required>
	<input type="hidden" name="submit" value="true" /><br />
	<input type="submit" value="Ajouter" /> 
</form>

<?php
break;
case 'delete':
$id=intval($_GET['id']);
echo "Vous allez supprimer votre ami.<br />";
$result=mysqli_query($link,'SELECT id FROM friends WHERE id='.$id.' AND (id_friend_2="'.$_SESSION['id'].'" OR id_friend_1="'.$_SESSION['id'].'") ')or die (mysqli_error($link));
$data = mysqli_fetch_array($result);
if(empty($data['id'])){
	die(error("<br />Cette amitée ne vous concerne pas !"));
}
	$submit = (empty( $_POST['submit'])) ? '' :  $_POST['submit'] ;

	if($submit) {
	mysqli_query($link,'DELETE FROM friends WHERE id ='.$id) or die(mysqli_error($link));
	valid("Ami supprimée !");
	redirect('./friends.php');

} else {
	echo 'Êtes-vous sur de vouloir supprimer votre ami ? :(  : ';
	?>
	<form action="./friends.php?act=delete&id=<?php echo $id;?>" method="POST">
		<input type="hidden" name="submit" id="submit" value="true" />
		<input type="submit" />
	</form>
	<?php
}



break;
case 'accept':
$result=mysqli_query($link,'UPDATE  friends SET  accepted="1",date='.time().' WHERE  id="'.intval($_GET['id']).'" AND id_friend_2="'.intval($_SESSION['id']).'"')or die (mysqli_error($link));
echo valid("Demande acceptée !");
redirect("./friends.php");
break;
default:
echo '<h2>Gestion de vos amis :</h2><p><a href="./friends.php?act=add">Ajouter un ami</a></p>';
$i=0;
$qu = 'SELECT * FROM friends WHERE id_friend_1="'.intval($_SESSION['id']).'" OR id_friend_2="'.intval($_SESSION['id']).'"';
$result=mysqli_query($link,$qu)or die (mysqli_error($link));
echo '<table frame="border" class="dettes_tab"><tr><td>Etat:</td><td>Pseudo Source :</td><td>Pseudo cible :</td><td>Date :</td><td>Etat : </td><td>Actions :</td></tr>';

while($data = mysqli_fetch_array($result)) {
	$ask_member_c = mysqli_query($link,'SELECT nickname FROM user WHERE id='.$data['id_friend_2']);
	$ask_member_c= mysqli_fetch_array($ask_member_c);
	$ask_member_s = mysqli_query($link,'SELECT nickname FROM user WHERE id='.$data['id_friend_1']);
	$ask_member_s= mysqli_fetch_array($ask_member_s);
	$etat = ($data['accepted']) ? 'Accepte' : 'En cours';
	echo '<tr align="center"><td><img src="./img/user_'.$data['accepted'].'.png"  alt="etat" title="etat:'.$etat.'" /></td>';
	echo '<td>'.$ask_member_s['nickname'].'</td><td>'.$ask_member_c['nickname'].'</td><td>'.temps($data['date']).'</td>';
	echo '<td>'.$etat.'</td>';
	if(!$data['accepted'] AND $data['id_friend_2'] == $_SESSION['id'])
		echo '<td><a href="./friends.php?act=accept&amp;id='.$data['id'].'"><img src="./img/user_accept.png"  alt="Accepter" title="Accepter" /></a> - <a href="./friends.php?act=delete&amp;id='.$data['id'].'"><img src="./img/folder_delete.png"  alt="Supprimer" title="Supprimer" /></a></td>';
	else 
		echo '<td><a href="./friends.php?act=delete&amp;id='.$data['id'].'"><img src="./img/folder_delete.png"  alt="Supprimer" title="Supprimer" /></a></td>';
	
	echo "</td></tr>\n";
	$i++;
}
if($i==0) echo '<tr><td colspan="7"><div align="center">Aucune donnée</div></td></tr>';


echo '</table>';
	//On charge le js en fin de page pour pas rallonger le temps de chargement
echo " <script type='text/javascript'>
$(document).ready(function(){
	$('table.dettes_tab tr:nth-child(even)').addClass('style1');
	$('table.dettes_tab tr:nth-child(odd)').addClass('style2');

});</script>";// this small script altern color inside the tab
break;
}

include('./include/footer.php');
?>