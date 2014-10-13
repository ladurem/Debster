<?php
include('./include/header.php');
include('./include/connectdb.php');
if ( !isset( $_SESSION['id']) AND !isset($_SESSION['id'])){
	echo "Cette partie est uniquement reservée aux inscrits et connectés.<br />";
	die();
}

function return_statut($tp) {
	switch ($tp) {
		case 0:
		return 'ouvert';
		break;
		case 1:
		return 'remboursée';
		break;
		case 2:
		return 'annulée';
		break;
	}
}
$act = (empty($_GET['act'])) ? '' : $_GET['act'] ;

switch ($act) {
	case 'add':
		$sub = (empty($_POST['submit'])) ? '' : $_POST['submit'] ;

		if($sub) {
			$result = mysqli_query($link,'SELECT id FROM user WHERE nickname="'.mysqli_real_escape_string($link,$_POST['cible_id']).'"');
			$pseudo_ami = mysqli_fetch_array($result);
			if(empty($pseudo_ami['id'])){ # verification que le membre existe
				die(error("Votre ami n'existe pas, j'en suis désolé ..."));
			}
			$que='SELECT id,accepted FROM friends WHERE  ((id_friend_1="'.$_SESSION['id'].'" AND id_friend_2="'.$pseudo_ami['id'].'") OR (id_friend_2="'.$_SESSION['id'].'" AND id_friend_1="'.$pseudo_ami['id'].'"))   ';
			$result_ami = mysqli_query($link,$que) or die(mysqli_error());
			$ami_ensemble = mysqli_fetch_array($result_ami);
			if(!$ami_ensemble['accepted']){  # verification que le membre existe
				die(error("Votre amitié n'existe pas, j'en suis désolé ..."));
			}
			if(!is_numeric($_POST['montant'])){ 
				die(error("Montant non reel, appartient-il aux complexes ? "));
			}
			#Si pas d'erreur => Traitements
			if($_POST['payed'] == "me") {
				$user_source = intval($_SESSION['id']);
				$user_cible = intval($pseudo_ami['id']);
			} elseif ($_POST['payed'] == "friend") {
				$user_cible = intval($_SESSION['id']);
				$user_source = intval($pseudo_ami['id']);	
			}
			$query = 'INSERT INTO dettes (user_source, user_cible, montant, date, msg_open) VALUES ("'.$user_source.'", "'.$user_cible.'", "'.$_POST['montant'].'", "'.time().'", "'.mysqli_real_escape_string($link,$_POST['msg_open']).'")';
			mysqli_query($link,$query) or die('ERROR<br />'.$query.mysqli_error());
			echo valid("Votre dette a été ajoutée !");
			echo 'Redirection vers l\'accueil des dettes.<br />';
			redirect("./dettes.php");
			die();
		}
		?>
		<script type="text/javascript">
		$(function() {
			var availableTags = [
			<?php
			$ami_id = mysqli_query($link,'SELECT id_friend_1,id_friend_2 FROM friends WHERE (id_friend_1='.intval($_SESSION['id']).' OR id_friend_2='.intval($_SESSION['id']).') AND accepted  ') or die(mysqli_error());
			while($ami_b = mysqli_fetch_array($ami_id)) {
				$query = 'SELECT nickname FROM user WHERE id="'.$ami_b['id_friend_1'].'" OR id="'.$ami_b['id_friend_2'].'" ';
				$ami_pseudo = mysqli_query($link,$query) or die(mysql_error());
				while($ami_pseudo2 = mysqli_fetch_array($ami_pseudo)) {
					if($ami_pseudo2['nickname'] == $_SESSION['pseudo'])
						continue;
					echo '"'.$ami_pseudo2['nickname'].'",';
				}

			}

		?>
		];
		$( "#cible_id" ).autocomplete({
			source: availableTags
		});
		});
		</script>
		<h2>Ajouter une dette</h2>
		<form id="add_dette" method="POST" action="./dettes.php?act=add" oninput="total.value = (montant.valueAsNumber/2)">
			<table id='details'>
				<tr>
					<td><label for="paye">Qui a payé la dette ? :</label></td>
					<td><select id="payed" name="payed" > Moi
						<option value="me" selected="selected">Moi</option>
						<option value="friend">Mon ami</option>
					</select></td>
				</tr>

			<tr>
			<td><label for="cible_id">Pseudo de votre ami:</label></td>
			<td><input type="text" id="cible_id" name="cible_id" placeholder="Jean_Mich_du_03" autocomplete="off"   required>
			</td>
		</tr>
			<tr><td><label for="montant">Montant :</label></td>
			<td><input type="number" id="montant" value="0" name="montant" min="0" step="0.01" required>
			</td></tr>
			<tr><td><label for="msg_open">Informations :</label></td>
			<td><input type="text" id="msg_open" name="msg_open" required oninput="check(this)"></td>
		<!-- <label>Date (si vous ne souhaitez pas dater la dette du jour):</label>
			<input type="date" id="date" name="date"> -->
			</tr><tr>
			<td><label for="total">Total (par personne):</label></td>
			<td><output id="total" name="total">0</output> &euro;</td></tr>
			<tr><td>
			<input type="hidden" name="submit" value="true" />
			<input type="submit" value="Ajouter" /> </td></tr>
			</table>
		</form>

		<?php
	break;

	case 'explore':
		$id = intval($_GET['id']);
		$result=mysqli_query($link,'SELECT * FROM dettes WHERE id='.$id.' AND (user_cible ="'.$_SESSION['id'].'" OR user_source="'.$_SESSION['id'].'") ')or die (mysqli_error($link));
		$data = mysqli_fetch_array($result);
		$ask_member_c = mysqli_query($link,'SELECT nickname FROM user WHERE id='.$data['user_cible']);
		$ask_member_c= mysqli_fetch_array($ask_member_c);
		$ask_member_s = mysqli_query($link,'SELECT nickname FROM user WHERE id='.$data['user_source']);
		$ask_member_s= mysqli_fetch_array($ask_member_s);
		if(empty($data['id'])){
			die("<br />Cette dette ne vous concerne pas !");
		}
		echo 'Consultez une dette : <br />';
		?>
		<table id='details'>
			<tr><td>Qui a payé ? </td><td><?php echo $ask_member_s['nickname'];?></td></tr>
			<tr><td>Qui doit rembourser ? </td><td><?php echo $ask_member_c['nickname']?></td></tr>
			<tr><td>Montant total à partager :</td><td><?php echo $data['montant'];?> euros</td></tr>
			<tr><td>Montant à rembourser :</td><td><?php echo $data['montant']/2;?> euros</td></tr>
			<tr><td>Statut :</td><td><?php echo return_statut($data['statut']);?></td></tr>
			<tr><td>Message d'ouverture :</td><td><?php echo $data['msg_open'];?></td></tr>
			<?php if($data['statut']>=1) {
				?>
				<tr><td>Message de fermeture :</td><td><?php echo htmlentities(stripslashes($data['msg_close']));?></td></tr>
				<tr><td>Date de fermeture:</td><td><?php echo temps($data['date_close']);?></td></tr>
			<?php
			}
		echo '</table>';
	break;

	case 'edit':
		echo "<h2>Editer une dette</h2>";
		$id = intval($_GET['id']);
		$result=mysqli_query($link,'SELECT * FROM dettes WHERE id='.$id.' AND (user_cible ="'.$_SESSION['id'].'" OR user_source="'.$_SESSION['id'].'") ')or die (mysqli_error($link));
		$data = mysqli_fetch_array($result);
		$ask_member_c = mysqli_query($link,'SELECT nickname FROM user WHERE id='.$data['user_cible']);
		$ask_member_c= mysqli_fetch_array($ask_member_c);
		$ask_member_s = mysqli_query($link,'SELECT nickname FROM user WHERE id='.$data['user_source']);
		$ask_member_s= mysqli_fetch_array($ask_member_s);
		if(empty($data['id']))
			die(error("<br />Cette dette ne vous concerne pas !"));
		if($data['statut'])
			die(error("<br />Cette dette a été fermée ou rembousée. Edition impossible."));
		$sub = (empty($_POST['submit'])) ? '' : $_POST['submit'] ;

		if($sub) {
			if(!is_numeric($_POST['montant'])){ 
				die(error("Montant non reel, appartient-il aux complexes ? "));
			}

		#Si pas d'erreur => Traitements
		$result=mysqli_query($link,'UPDATE  dettes SET  montant="'.$_POST['montant'].'",msg_open="'.mysqli_real_escape_string($link,$_POST['msg_open']).'"  WHERE  id="'.$id.'" AND (user_source="'.intval($_SESSION['id']).'" OR user_cible="'.intval($_SESSION['id']).'")')or die (mysqli_error($link));
		echo valid("Votre dette a été editée !");
		echo 'Redirection vers l\'accueil des dettes.<br />';
		redirect("./dettes.php");
		die();


		}
		?><form id="add_dette" method="POST" action="./dettes.php?act=edit&id=<?php echo $id; ?>" oninput="total.value = (montant.valueAsNumber/2)">

		<table id='details'>
		<tr><td><label>Source de la dette:</label></td>
		<td><input type="text" id="source_id" name="source_id" value="<?php echo $ask_member_s['nickname']; ?>" disabled   required></td></tr>
		<tr><td><label>Cible de la dette:</label></td>
		<td><input type="text" id="cible_id" name="cible_id" value="<?php echo $ask_member_c['nickname']; ?>"  disabled required></td></tr>
		<tr><td><label>Montant :</label></td>
		<td><input type="number" id="montant" value="<?php echo intval($data['montant']) ?>" name="montant" min="0"  
		title="Rentrez uniquement des chiffres !" required></td></tr>
		<tr><td>
		<label>Informations :</label></td>
		<td><input type="text" id="msg_open" name="msg_open" value="<?php echo stripslashes($data['msg_open']) ?>" required oninput="check(this)"></td></tr>
		<tr><td>
		<label>Total (par personne):</label></td>
		<td><output id="total" name="total"><?php echo intval($data['montant'])/2; ?> </output> .00 &euro;</td>
		<tr><td>
		<input type="hidden" name="submit" value="true" />
		<input type="submit" value="Editer" /> 
		</td></tr></table>
		</form>
		<?php

		break;

	case 'close':
		$result=mysqli_query($link,'UPDATE  dettes SET  statut="1",date_close="'.time().'"  WHERE  id="'.intval($_GET['id']).'" AND (user_source="'.intval($_SESSION['id']).'" OR user_cible="'.intval($_SESSION['id']).'")')or die (mysqli_error($link));
		echo valid("Vous venez de rembourser votre dette");
		redirect("./dettes.php");
		break;
		case 'annule':
		$id=intval($_GET['id']);
		echo "Vous voulez annuler une dette.";
		echo "<br />Vous pouvez uniquement annuler les detes dont vous êtes la source.";
		$result=mysqli_query($link,'SELECT * FROM dettes WHERE id='.$id.' AND user_source="'.$_SESSION['id'].'" ')or die (mysqli_error($link));
		$data = mysqli_fetch_array($result);
		if(empty($data['id'])){
			die(error("<br />Cette dette ne vous concerne pas !"));
		}

		$sub = (empty($_POST['submit'])) ? '' : $_POST['submit'] ;

		if($sub) {

			mysqli_query($link,'UPDATE  dettes SET  statut ="2" ,msg_close ="'.mysqli_real_escape_string($link,$_POST['msg_close']).'"  ,date_close ="'.time().'" WHERE  id ='.$id);
			echo (valid("Bien annulé !"));
			redirect("./dettes.php");
			die();

		} else {
			echo 'Veuillez saisir la raison de la fermeture : ';
			?>
			<form action="./dettes.php?act=annule&id=<?php echo $id;?>" method="POST">
				<input type="text" name="msg_close" id="msg_close" /> <br />
				<input type="hidden" name="submit" id="submit" value="true" />
				<input type="submit" />
			</form>
			<?php
		}
	break;

	default:
		
		$i=0;
		$order = (empty($_GET['order'])) ? '' : $_GET['order'] ;
		if (isset($_GET['all'])) {
			$status='';
		}
		else {
			$status='AND statut="0"';
		}
		switch ($order) {
			case 'mt':
			$order = 'ORDER BY montant DESC';
			break;
			case 'st':
			$order = 'ORDER BY statut ASC';
			break;
			case 'date':
			$order = 'ORDER BY date DESC';	
			break;

			default:
			$order = '';	
			break;
		}
		$result=mysqli_query($link,'SELECT * FROM dettes WHERE (user_source="'.intval($_SESSION['id']).'" OR user_cible="'.intval($_SESSION['id']).'") '.$status.' '.$order)or die (mysqli_error($link));
		if (isset($_GET['all'])) {
			echo ' <h2>Gestion de toutes les dettes</h2><p>';	
		}
		else {
		echo ' <h2>Gestion des dettes en cours</h2><p>';
		}
		echo '<a href="./dettes.php?act=add">Ajouter une dette</a></p>';
		echo '<table frame="border" class="dettes_tab"><tr><td>Etat:</td><td>Pseudo Source :</td><td>Pseudo cible :</td><td>Montant <a href="./dettes.php?order=mt"><img src="./img/arrow_down.png"  alt ="arrow_down" /></a>: </td><td>Libéllé:</td><td>Statut <a href="./dettes.php?order=st"><img src="./img/arrow_down.png"  alt ="arrow_down" /></a>:</td><td>Date <a href="./dettes.php?order=date"><img src="./img/arrow_down.png"  alt ="arrow_down" /></a>:</td><td>Actions :</td></tr>';

		while($data = mysqli_fetch_array($result)) {
			$ask_member_c = mysqli_query($link,'SELECT nickname FROM user WHERE id='.$data['user_cible']);
			$ask_member_c= mysqli_fetch_array($ask_member_c);
			$ask_member_s = mysqli_query($link,'SELECT nickname FROM user WHERE id='.$data['user_source']);
			$ask_member_s= mysqli_fetch_array($ask_member_s);
			echo '<tr><td><img src="./img/folder_'.$data['statut'].'.png"  alt="etat" titler="etat" /></td>';
			echo '<td>'.$ask_member_s['nickname'].'</td><td>'.$ask_member_c['nickname'].'</td><td><div align="center">'.($data['montant']/2).'&euro; - ('.$data['montant'].'&euro;)</div></td><td>'.coupe(htmlentities(stripslashes($data['msg_open']))).'</td><td>'.return_statut($data['statut']).'</td><td>'.temps($data['date']).'</td><td>';
			if(!$data['statut']) {
				echo ' <a href="./dettes.php?act=close&amp;id='.$data['id'].'"><img src="./img/money.png"  alt="Rembourser" title="Rembourser" /></a>';
				echo ' <a href="./dettes.php?act=edit&amp;id='.$data['id'].'"><img src="./img/folder_edit.png"  alt="Editer" title="Editer" /></a>';
				if($data['user_source']==$_SESSION['id'])
					echo ' <a href="./dettes.php?act=annule&amp;id='.$data['id'].'"><img src="./img/folder_delete.png"  alt="Supprimer" title="Supprimer" /></a>';
			}
			echo ' <a href="./dettes.php?act=explore&amp;id='.$data['id'].'"><img src="./img/folder_explore.png"  alt="Consulter" title="Consulter" /></a>';	
			echo "</td></tr>";
			$i++;
		}
		if($i==0) echo '<tr><td colspan="7"><div align="center">Aucune donnée</div></td></tr>';


		echo '</table>';
		if (isset($_GET['all'])) {
			echo "<p><a href='./dettes.php'>Afficher les dettes en cours</a></p>";
		}
		else {
			echo "<p><a href='./dettes.php?all'>Afficher toutes les dettes</a></p>";
		}
			//On charge le js en fin de page pour pas rallonger le temps de chargement
		echo " <script type='text/javascript'>
		$(document).ready(function(){
			$('table.dettes_tab tr:nth-child(even)').addClass('style1');
			$('table.dettes_tab tr:nth-child(odd)').addClass('style2');

		});</script>";
	break;
}
include('./include/footer.php');
?>