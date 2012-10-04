<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" >
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Algorithme d'Earley - Julien Villetorte</title>
	<style type="text/css">
		body{
			font-family: 'Trebuchet MS', 'Lucida Grande', Verdana, Arial, serif;
		}
		.code{
			font-family: Courier;
			font-size: 1.1em;
		}
	</style>
</head>
<body>
	<h1 style="text-align:center;">Algorithme d'Earley<br /><span style="font-size:0.5em">Julien Villetorte</span></h1>
	<h4>Saisie des règles de la grammaire :</h4>
	<?php
	if(!isset($_GET['nb_rules']) || intval($_GET['nb_rules'])<1){
		?>
		<fieldset><legend>Nombre de règles</legend>
		<form action="index.php" method="get">
			<p>
			<label for="nb_rules">Nombre de règles que vous souhaitez entrer : <input type="text" name="nb_rules" id="nb_rules" /></label>
			<input type="submit" value="Définir les règles" />
			</p>
		</form>
		</fieldset>
		<?php
	}
	else{
	?>
		<fieldset><legend>Ecriture des règles et saisie du mot</legend>
		Saisissez les règles, dans l'ordre, de cette façon :<br />
		<em style="margin-left:30px;">S -> UbV,  U -> aUb, ...</em><br /><br />
		Caractères autorisés :
		<ul>
			<li>Pour les symboles terminaux : tous les caractères sauf les majuscules et le point</li>
			<li>Pour les symboles non-terminaux : caractères majuscules uniquement</li>
		</ul>
		<form action="earley_parser.php" method="post">
			<ol>	
			<?php
			for($i=1; $i<=intval($_GET['nb_rules']); $i++)
				echo '	<li><input type="text" size="4" maxlength="1" name="start['.$i.']" /> -> <input type="text" size="8" name="rule['.$i.']" /></li>';
			?>
			</ol>
			<p>Mot à tester : <input type="text" name="word" id="word" /></p>
			<p>
				<label for="disp">Affichage sous forme de </label>
				<select name="disp" id="disp">
					<option value="lists">listes</option>
					<option value="table">tableau</option>
				</select>
			</p>
			<input type="submit" value="Dérouler l'algo" />
		</form>
		</fieldset>
		<?php
	}
	?>
</body>
</html>