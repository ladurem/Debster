<?php
if(!include './include/connectdb.php')
	header('Location: install.php');
include('./include/header.php');
if ( isset( $_SESSION['id'])){
	include('./include/connectdb.php');
	$query="SELECT mail,firstname,lastname FROM user WHERE id=$_SESSION[id]";
	$result = mysqli_query( $link,$query) or die(error(mysqli_error($link)));
	$row = mysqli_fetch_assoc($result);
	$result=mysqli_query($link,'SELECT * FROM friends WHERE (id_friend_2="'.$_SESSION['id'].'" OR id_friend_1="'.$_SESSION['id'].'") ')or die (mysqli_error($link));
	$nb_friends = mysqli_num_rows($result);
	$result=mysqli_query($link,'SELECT * FROM dettes WHERE (user_cible ="'.$_SESSION['id'].'" OR user_source="'.$_SESSION['id'].'") AND statut=0')or die (mysqli_error($link));
	$nb_dettes = mysqli_num_rows($result);
	echo "<p>Bienvenue ".htmlentities($row['firstname'])." ".htmlentities($row['lastname'])."</p>";
	?>
	<table id='home'>
		<tr frame="border" id='head-line'><td><h3>Mon profil</h3></td><td><h3>Mes amis</h3></td><td><h3>Mes dettes</h3></td></tr>
		<tr>
			<td><a href="./user.php?action=profil">Modifier mes informations</a></td>
			<td><?php echo htmlentities($nb_friends); ?> ami<?php if ($nb_friends>1) {echo 's';} ?> sur Debster.</td>
			<td><?php echo htmlentities($nb_dettes);?> dette<?php if ($nb_dettes>1) {echo 's';} ?> en cours.
		</tr>
		<tr>
			<td><a href="./user.php?action=password">Modifier mon mot de passe</a></td>
			<td><a href="./friends.php">Voir mes amis</a></td>
			<td><a href="./dettes.php">Voir les dettes</a></td>
		</tr>		
		</table>
		<?php
	}else {
		?>
		<div>
			<form method="POST" action="user.php?action=connect">
				<input type='hidden' name='connection'/>
				<p><div><em>Identifiez-vous !</em></div><div class='connection'>E-mail : <input type="email" name="email"/> </div><div class='connection'>Mot de passe : <input type="password" name="password"/><input type="submit" name="submit"/></div></p>
			</form>
		</div>
		<div id='inscription'>
			<h2>Inscrivez-vous !</h2>
			<form method="POST" action="user.php?action=add" id="register" onSubmit="return validate_passwd(this)">
				<input type='hidden' name='new_user'/>
				<table>
					<tr><td>Nom :</td><td><input type="text" name="lastname" id="lastname" required/></td></tr>
					<tr><td>Prénom : </td><td><input type="text" name="firstname" id="firstname" required/></td></tr>
					<tr><td>Pseudo : </td><td><input type="text" name="nickname" id="nickname" required/></td></tr>
					<tr><td>E-mail : </td><td><input type="email" name="email" id="email" required/></td></tr>
					<tr><td>Année de naissance :</td><td><input type="number" name="birthyear" id="birthyear" min="1900" max="2010" title="Rentrez uniquement une année de naissance valide" /></tr>
					<tr><td>Mot de passe :</td><td><input type="password" name="password" id="password" title="8 caractères minimum" required/></td></tr>
					<tr><td>Retaper votre mot de passe :</td><td><input type="password" name="password2" id="password2" title="8 caractères minimum" required/></td></tr>
					<tr><td><input type="submit" name="submit"/></td></tr>
				</table>
			</form>
		</div>
		<img id='index_picture' src='./img/home.jpg' alt='Enjoy your life !'/>
		<?php
	}
	include('./include/footer.php');
	?>

