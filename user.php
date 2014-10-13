<?php
include('./include/header.php');


switch ($_GET['action']) {
	case 'add':
	if ( isset( $_POST['new_user']) ){
		//On se connecte a la BDD
		include('./include/connectdb.php');

		$email = mysqli_real_escape_string($link,$_POST['email']);
		//Verifier la validité de l'email
		$nickname = mysqli_real_escape_string($link,$_POST['nickname']);
		$lastname = mysqli_real_escape_string($link,$_POST['lastname']);
		$firstname = mysqli_real_escape_string($link,$_POST['firstname']);
		$birthyear = mysqli_real_escape_string($link,$_POST['birthyear']);
		if ( !is_numeric($birthyear) ) {
			exit('Année de naissance invalide');
		}
		$numb_nickname=strlen($nickname);
		$numb_firstname=strlen($firstname);
		$numb_lastname=strlen($lastname);

		//Vérification que les champs names sont bien remplis. (Année vérifié un peu avant, mots de passes et emails sont vérifiés plus bas)
		if ( $numb_nickname == 0 || $numb_firstname == 0 || $numb_lastname == 0 ) {
					die(error('Tous les champs sont requis. Merci de recommencer.'));
				}

		//Verifier mots de passes identiques
		if ( $_POST['password'] != $_POST['password2'])
			echo "<p>Erreur, les 2 mots de passes sont différents</p>";
		else {
			if(strlen($_POST['password']) <8) {
				die(error('Votre mot de passe n\'est pas assez long, 8 caracteres minimum.'));
			} 

		//On verifie que le e-mail n'est pas déjà utilisé
			$result=mysqli_query($link,'SELECT mail FROM user WHERE mail="'.$email.'" ') or die(mysqli_error($link));
			if (mysqli_num_rows($result) != 0)
				echo error("<p>Attention ! Cette Adresse Email a déjà été utilisé. Merci de renseigner une autre adresse e-mail</p>");
			else {
				if(filter_var($email, FILTER_VALIDATE_EMAIL)) {

			//On hache le passwd et on ajoute l'user dans la bdd
					$pass=hash("sha256","$_POST[password]");
					$query="INSERT INTO user (mail, nickname, password, lastname, firstname,birthyear) VALUES (\"$email\",\"$nickname\", \"$pass\", \"$lastname\", \"$firstname\", \"$birthyear\")";
					$result = mysqli_query( $link,$query) or die(mysqli_error($link));
					echo valid("<p>Felicitations, Votre compte a bien été crée ! Pour continuer, Merci de vous connecter</p>");
					} else {
					echo error("L'adresse mail n'est pas au format correct.");
				}
			}

			include('./include/disconnectdb.php');

			
			
			
		}
	}
	else { 
			redirect("./index.php");
		}

	break;
	
	case 'profil':
	if ( isset($_SESSION['id'])) {
		echo '<h2>Mes informations</h2>';
		//Si on veut passer en mode édition
		if ( isset($_GET['edit'])) {
			//Si on a rempli le formulaire
			if ( isset($_POST['edited'])) {
				


		//On se connecte a la BDD
				include('./include/connectdb.php');

				$email = mysqli_real_escape_string($link,$_POST['mail']);
				$nickname = mysqli_real_escape_string($link,$_POST['nickname']);
				$lastname = mysqli_real_escape_string($link,$_POST['lastname']);
				$firstname = mysqli_real_escape_string($link,$_POST['firstname']);
				$birthyear = mysqli_real_escape_string($link,$_POST['birthyear']);
				if ( !is_numeric($birthyear) || $birthyear<'1900' || $birthyear>'2010' ) {
					die(error('Année de naissance invalide'));
				}
				if ( strlen($nickname) == 0 || strlen($lastname) == 0 || strlen($firstname) == 0 ) {
					die(error('Tous les champs sont requis. Merci de recommencer.'));
				}


		
			//On verifie que le e-mail n'est pas déjà utilisé
					$result=mysqli_query($link,'SELECT id,mail FROM user WHERE mail="'.$email.'" ') or die(mysqli_error($link));
					$row=mysqli_fetch_assoc($result);
					if ( (mysqli_num_rows($result) == 1) && ($row['id'] != $_SESSION['id']) )
						die(error("<p>Attention ! Cette Adresse Email a déjà été utilisé. Merci de renseigner une autre adresse e-mail</p>"));
					else {
						if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
							$query="UPDATE user SET mail='$email',nickname='$nickname',lastname='$lastname',firstname='$firstname',birthyear='$birthyear' WHERE id='$_SESSION[id]'" or die('Erreur lors du changement des informations dans la bdd');
							$result = mysqli_query( $link,$query) or die(mysqli_error($link));
							if ( $result == TRUE)
								echo valid("Felicitations, Votre compte a bien été modifié !");
							if ($_SESSION['pseudo'] != $nickname ) 
								$_SESSION['pseudo'] = $nickname;				
						} 
						else {
							echo error("L'adresse mail n'est pas au format correct.");
						}
					}
				

				include('./include/disconnectdb.php');
				//redirect("./index.php");





			} 
			else {
				//On met le formulaire
				include('./include/connectdb.php');

				$result=mysqli_query($link,"SELECT * FROM user WHERE id=$_SESSION[id]");
				$row=mysqli_fetch_assoc($result);
				mysqli_free_result($result);

				?>
				<table>
					<form method="POST" action="user.php?action=profil&edit">
						<input type='hidden' name='edited'/>
						<tr><td>Nom :</td><td><input type='text' name='lastname' value="<?php echo htmlentities(stripslashes($row['lastname']))?>" required/></td></tr>
						<tr><td>Prénom :</td><td><input type='text' name='firstname' value="<?php echo htmlentities(stripslashes($row['firstname']))?>"  required/></td></tr>
						<tr><td>Email :</td><td><input type='text' name='mail' value="<?php echo htmlentities(stripslashes($row['mail']))?>"  required /></td></tr>
						<tr><td>Pseudo :</td><td><input type='text' name='nickname' value="<?php echo htmlentities(stripslashes($row['nickname']))?>" required /></td></tr>
						<tr><td>Année de naissance :</td><td><input type="number" name="birthyear" min="1900" max="2010" value="<?php echo htmlentities(stripslashes($row['birthyear']))?>"  required/ title="Rentrez uniquement une année de naissance valide"></td></tr>
					</table>
					<tr><td><input type="submit" name="submit"/></td></tr>
				</form>
				<?php	
				include('./include/disconnectdb.php');
			}
			//On edite et on met a jour la BDD

		}
		else {

			include('./include/connectdb.php');

			$result=mysqli_query($link,"SELECT * FROM user WHERE id=$_SESSION[id]");
			$row=mysqli_fetch_assoc($result);
			mysqli_free_result($result);

			?>
			<table id='details'>
				<tr><td>Nom :</td><td><?php echo htmlentities(stripslashes($row['lastname']))?></td></tr>
				<tr><td>Prénom :</td><td><?php echo htmlentities(stripslashes($row['firstname']))?></td></tr>
				<tr><td>Email :</td><td><?php echo htmlentities(stripslashes($row['mail']))?></td></tr>
				<tr><td>Pseudo :</td><td><?php echo htmlentities(stripslashes($row['nickname']))?></td></tr>
				<tr><td>Année de naissance :</td><td><?php echo htmlentities(stripslashes($row['birthyear']))?></td></tr>
			</table>
			<p>Si vous souhaitez changer ces informations, <a href='./user.php?action=profil&amp;edit'>cliquez ici</a></p>
			<p>Si vous souhaitez changer votre mot de passe, <a href='./user.php?action=password'>cliquez ici</a></p>
			<?php	
			include('./include/disconnectdb.php');
		}
	}
	else {
		exit('Vous n\'êtes pas connecté');
	}
	break;


	case 'password':
	if ( isset($_SESSION['id'])) {
		if ( isset($_POST['edit_password'])) {

			//On verifie les 2 nouveaux mots de passe
			if ( $_POST['password'] != $_POST['password2']){ 
				die(error('Les mots de passe ne correspondent pas.'));		
				}
			else {
				if(strlen($_POST['password']) <8) {
					die(error('Votre mot de passe n\'est pas assez long, 8 caracteres minimum.'));
				} 
				include('./include/connectdb.php');
				$result = mysqli_query($link, "SELECT * FROM user WHERE id=$_SESSION[id]") or die('Erreur avec votre compte');
				$row=mysqli_fetch_assoc($result);
				mysqli_free_result($result);
				//Check the old password is correct
				$test_pass=hash("sha256","$_POST[old_password]");
				$true_pass=$row['password'];
				if ( $test_pass != $true_pass ){
					die(error('Mot de passe incorrect.'));
					}
				else {
					$new_password_hash=hash("sha256","$_POST[password]");
					$result = mysqli_query($link, "UPDATE user SET password='$new_password_hash' WHERE id='$_SESSION[id]' ") or die('Erreur lors du changement de mot de passe');
					if ($result == TRUE)
						echo valid('Le mot de passe a bien été modifié.');

					include('./include/disconnectdb.php');
					}
			}
		}
		else {

			?>
			<h2>Changer votre mot de passe</h2>
			
			<form method='POST' action='user.php?action=password' onSubmit="return validate_passwd(this)">
				<input type='hidden' name='edit_password'/>
				<p>
				<label for ="old_password">Votre ancien mot de passe :</label>
					<input  type='password' name='old_password' id="old_password" required /></p>
					<p><label for="password">Votre nouveau mot de passe :</label>
						<input  type='password' name='new_password' id="password" title="8 caractères minimum" required/><br />
						<label for="password2">Retaper votre nouveau mot de passe :</label>
							<input  type='password' name='new_password2' id="password2" title="8 caractères minimum" required/></p>
							<input type='submit'/>
						</form>
						<?php	
					}
				}
				else {
					exit('Vous n\'êtes pas connecté');
				}
				break;



				case 'disconnect':
				if ( isset($_SESSION['id'])) {
					session_destroy();
				}
				redirect('./index.php');
				break;

				case 'connect' :
				default:

				if (isset($_SESSION['id'])) {
					redirect('./index.php');
				}
				else {
					if ( isset($_POST['connection'] )) {
			//On se connecte a la BDD
						include('./include/connectdb.php');

			//Traiter la vérification des identifiants et la connexion
						$email = mysqli_real_escape_string($link,$_POST['email']);

			//Verifier la validité de l'email

						$result=mysqli_query($link,"SELECT id,nickname,password FROM user where mail=\"$email\"");
						$row = mysqli_fetch_assoc($result);
						mysqli_free_result($result);
						$test_password = hash("sha256","$_POST[password]");
						if ($row['password'] == $test_password) {
				//Ouverture de la session
							$_SESSION['id'] = $row['id'];
							$_SESSION['pseudo'] = $row['nickname'];
							$_SESSION['time']=time();
							redirect('./index.php');
						}
						else {
							die(error('Erreur de connexion, Vérifier vos identifiants et réessayer'));
							//redirect('./index.php');
						}

						include('./include/disconnectdb.php');

					}
				}
			}
	include('./include/footer.php');
			?>