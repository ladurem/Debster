</div>
<?php
$url = explode('/',"$_SERVER[REQUEST_URI]");
if ( !($url[count($url) -1] == 'index.php' || $url[count($url) -1] == '') ) { 
	?>
<div id='back'><a href='./index.php'>Retour à l'accueil</a></div>
<?php
}



?>
<div id='bandeau_bas'>
		<div class='width_limit'>
		<table id='bottom'>
			<tr>
				<td>Copyright &copy; Debster, All rights reserved.</td>
				<td>Mentions Légales</td>
				<td>Conditions générales d'utilisation</td>
			</tr>
		</table>
	</div>
</div>
</body>
</html>