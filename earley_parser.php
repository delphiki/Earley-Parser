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
		}
		.curletter{
			text-decoration: underline;
		}
		ul{
			list-style-type: none;
		}
		.table_titles{
			text-align:center; 
			border-right:1px solid black; 
			border-left:1px solid black; 
			border-bottom:1px solid black;
		}
		.table_item{
			text-align:center; 
			border-right:1px solid black; 
			border-left:1px solid black;
			vertical-align:top;
		}
		.rouge{
			color: red;
		}
	</style>
</head>
<body>
<h1 style="text-align:center;">Algorithme d'Earley<br /><span style="font-size:0.5em">Julien Villetorte</span></h1>
<?php
/**
 * @author Julien Villetorte
 * @version 1.0
 */

// classe définissant un élément d'une liste :
class item{
	/**
	*  @var string $start correspond au symbole non-terminal à gauche de la flèche
	* @var string $rule correspond à la règle (ce qu'il y a à droite de la flèche)
	* @var int $index correspond à l'indice de la liste d'origine de l'élément
	*/
	var $start;
	var $rule;
	var $index;
	
	public function __construct($_start, $_rule, $_index){
		$this->start = $_start;
		$this->rule = $_rule;
		$this->index = $_index;
	}
}

 // on stocke les différentes règles et on vérifie qu'il n'y a pas d'erreur dans la saisie
if(isset($_POST['start']) && isset($_POST['rule']) && isset($_POST['word'])){
	$i = count($_POST['start']);
	$k=0;
	$old_start = $_POST['start'][1];
	for($j=1; $j<=$i; $j++){
		if(empty($_POST['start'][$j]) || empty($_POST['rule'][$j])) die('Un champ des règles a été mal rempli !');
		if($j>1 && $old_start == $_POST['start'][$j]){$k++;}
		else{$k=0;}
		$rules[ $_POST['start'][$j] ][$k] = $_POST['rule'][$j];
		$old_start = $_POST['start'][$j];
	}
	$word = htmlspecialchars(trim($_POST['word']));
}
else
	die('Données manquantes !'); 
	
###########################################################
#							JEUX D'EXEMPLE
###########################################################
$rules1 = array(
			'P' => array('S'),
			'S' => array('S+M',
						'M'),
			'M' => array('M*T',
						'T'),
			'T' => array('2',
						'3',
						'4')
			);
$word1 = '2+3*4';

$rules2 = array(
			'S' => array('UbV'),
			'U' => array('aUb',
						'ab'),
			'V' => array('bVa',
						'ba')
			);
$word2 = 'aabbbba';

$rules3 = array(
			'S' => array('(S)', 
						'R'),
			'R' => array('E=E'),
			'E' => array('(E+E)',
						'a',
						'b')
			);
$word3 = '(a+b)=b';	

$rules4 = array(
			'S' => array('AS', 
						'SB',
						'c'),
			'A' => array('AB',
						'a'),
			'B' => array('BA',
						'b')
			);
$word4 = 'acba';

###########################################################
	
// nombre de caractères du mot
$nb_car = strlen($word);

// variable qui contiendra les éléments des différentes listes L0, L1, L2, ...
$lists = array(array());

// variable qui contiendra les variables non-terminales, dans leur ordre de définition (S, U, V, ...)
$starts = array();

echo '<p><strong>Mot à tester :</strong> '.$word.'</p>
';

echo '<p><strong>Longueur du mot :</strong> '.$nb_car.'</p>
';

echo '<h4>Règles :</h4>
';
$k = 0;
echo '<ol>
';
// on génère la liste des règles
foreach($rules as $key => $value){
	// on remplit starts
	$starts[$k] = $key;
	$k++;
	foreach($rules[$key] as $key2)
		echo '	<li class="code">&nbsp;'.$key.' -> '.$key2.'</li>
';
}
echo '</ol>
';

// construction de la chaine pour les regex
for($j=0; $j<count($starts); $j++){
	$regex_starts_str .= $starts[$j];
	if($j<(count($starts)-1))
		$regex_starts_str .= '|';
}

echo '<h2>Déroulement de l\'algorithme</h2>
';

/**
* fonction permettant de remplir L0
* @param array $rule liste des règles
* @param array $lists liste des éléments contenus dans les Listes
* @param array $starts liste des symbole non-terminaux
* @param string $regex_starts_str chaine pré-définie pour les regex
*/
function init(&$rules, &$lists, &$starts, &$regex_starts_str){
	$cur = 0;
	// étape 1
	while($rules[$starts[0]][$cur]){
		$lists[0][$cur] = new item($starts[0], '.'.$rules[$starts[0]][$cur], 0);
		$cur++;
	}
	
	$pattern = '#[^A-Z]*\.(['.$regex_starts_str.'])[^A-Z]*#';
	$continue = true; // variable d'indication de la saturation
	while($continue){
		foreach($lists[0] as $key){
			$continue=false;
			// étape 2
			if(substr($key->rule, -1, 1)=='.'){
				foreach($lists[0] as $key2){
					if(preg_match($pattern, $key2->rule, $matches)){
						$pos = strpos(($key2->rule),'.');
						$new_rule = substr($key2->rule, 0, $pos).$matches[1].'.'.substr($key2->rule, $pos+2);
						if(!in_array(new item($key2->start, $new_rule, 0), $lists[0])){
							$lists[0][$cur] = new item($key->start, $new_rule, 0);
							$cur++;
							$continue=true;
						}
					}
				}
			}
			// étape 3
			if(preg_match($pattern, $key->rule, $matches)){
				if($matches[1]!=$starts[0]){
					$i=0;
					while($rules[$matches[1]][$i] 
					&& !in_array(new item($matches[1], '.'.$rules[$matches[1]][$i], 0),$lists[0])){	
						$lists[0][$cur] = new item($matches[1], '.'.$rules[$matches[1]][$i], 0);
						$cur++;
						$i++;
					}
					$continue=true;
				}
			}
		}
	}
}

/**
* fonction permettant de remplir toutes les autres Listes
* @param array $rule liste des règles
* @param array $lists liste des éléments contenus dans les Listes
* @param array $starts liste des symbole non-terminaux
* @param string $regex_starts_str chaine pré-définie pour les regex
* @param int $nb_car nombre de caractères dans le mot
* @param string $word mot que l'on veut tester
*/
function parse(&$rules, &$lists, &$starts, &$regex_starts_str, &$nb_car, &$word){
	for($l_idx=1; $l_idx <= $nb_car; $l_idx++){
		$cur=0;
		// étape 4
		foreach($lists[$l_idx-1] as $key){
			if(strpos(($key->rule),$word[$l_idx-1])==(strpos(($key->rule),'.')+1)){
				$i=0;
				$pos = strpos(($key->rule),'.');
				$new_rule = substr($key->rule, 0, $pos).$word[$l_idx-1].'.'.substr($key->rule, $pos+2);
				$lists[$l_idx][$cur] = new item($key->start, $new_rule, $key->index);
				$cur++;
				$i++;
			}
		}
		$pattern = '#[^A-Z]*\.(['.$regex_starts_str.'])[^A-Z]*#';
		$continue = true; // variable d'indication de la saturation
		while($continue){
			$continue=false;
			foreach($lists[$l_idx] as $key){
				// étape 5
				if(substr($key->rule, -1, 1)=='.'){
					foreach($lists[$key->index] as $key2){
						if(preg_match($pattern, $key2->rule, $matches)){
							$pos = strpos(($key2->rule),'.');
							$new_rule = substr($key2->rule, 0, $pos).$matches[1].'.'.substr($key2->rule, $pos+2);
							if(!in_array(new item($key2->start, $new_rule, $key2->index), $lists[$l_idx])){
								$lists[$l_idx][$cur] = new item($key2->start, $new_rule, $key2->index);
								$cur++;
								$continue=true;
							}
						}
					}
				}
				// étape 6
				if(preg_match($pattern, $key->rule, $matches)){
					$i=0;
					while($rules[$matches[1]][$i] 
					&& !in_array(new item($matches[1], '.'.$rules[$matches[1]][$i], $l_idx), $lists[$l_idx])){
						$lists[$l_idx][$cur] = new item($matches[1], '.'.$rules[$matches[1]][$i], $l_idx);
						$cur++;
						$i++;
						$continue=true;
					}
				}				
			}
		}
	}	
}

/**
* fonction qui vérifie si l'élément de la liste détermine qu'un mot est accepté
* @param item $item élément testé
* @param array $lists liste des éléments contenus dans les Listes
*/
function check($item, &$lists){
	$s = $lists[0][0]->start;
	$res = false;
	foreach($lists as $key2){
		foreach($key2 as $key){
			if($key->start == $s){
				if(('.'.($item->rule) == ($key->rule).'.')){
					$res = true;
				}
			}
		}
	}
	return $res;
}

/**
* fonction d'affichage sous forme de listes des listes préalablement générées
* @param array $lists liste des éléments contenus dans les Listes
* @param int $nb_car nombre de caractères dans le mot
* @param string $word mot que l'on veut tester
*/
function display(&$lists, &$nb_car, &$word){
	echo '<ul>
';
	for($i=0; $i<=$nb_car; $i++){
		echo '	<li>
		Liste L<sub>'.$i.'</sub>';
		if($i>0) echo ', lettre courante : <strong>'.substr($word, 0, $i-1).'<span class="curletter">'.$word[$i-1].'</span>'.substr($word, $i).'</strong>';
		echo ' :<br />
		<ol>
';
		$j=0;
		while($lists[$i][$j]){
			if($lists[$i][$j]->index == 0 && check($lists[$i][$j], $lists))
					echo '			<li class="code rouge">&nbsp;[ '.$lists[$i][$j]->start.' -> '.str_replace('.', '&#149;', $lists[$i][$j]->rule).' , '.$lists[$i][$j]->index.' ]</li>
';
			else
				echo '			<li class="code">&nbsp;[ '.$lists[$i][$j]->start.' -> '.str_replace('.', '&#149;', $lists[$i][$j]->rule).' , '.$lists[$i][$j]->index.' ]</li>
';
			$j++;
		}
		echo '		</ol><br />
	</li>
';
	}
	echo '</ul>
';
}

/**
* fonction d'affichage sous forme d'un tableau des listes préalablement générées
* @param array $lists liste des éléments contenus dans les Listes
* @param int $nb_car nombre de caractères dans le mot
* @param string $word mot que l'on veut tester
*/
function table_display(&$lists, &$nb_car, &$word){
	echo '<table style="width:100%;">
	<tr>
';
	for($i=0; $i<=$nb_car; $i++){
		echo '		<td class="table_titles">
			Liste L<sub>'.$i.'</sub><br />
';
		if($i>0)
			echo '			<strong>'.substr($word, 0, $i-1).'<span class="curletter">'.$word[$i-1].'</span>'.substr($word, $i).'</strong>
';
	echo '		</td>
';
	}
	echo '	</tr>
	<tr>
';
	for($j=0; $j<=$nb_car; $j++){
		echo '		<td class="table_item">
';
		foreach($lists[$j] as $key)
			if($key->index == 0 && check($key, $lists))
				echo '			<span class="rouge">[ '.$key->start.' -> '.str_replace('.', '&#149;', $key->rule).' , '.$key->index.' ]</span><br />
';			else 
				echo '			[ '.$key->start.' -> '.str_replace('.', '&#149;', $key->rule).' , '.$key->index.' ]<br />
';
		echo '		</td>
';
	}
			
echo '	</tr>
</table>
';
}

// on initialise L0
init($rules,$lists, $starts, $regex_starts_str);
// on rempli les autres listes
parse($rules, $lists, $starts, $regex_starts_str, $nb_car, $word);
// on affiche le résultat
if($_POST['disp']=='lists')
	display($lists, $nb_car, $word);
elseif($_POST['disp']=='table')
	table_display($lists, $nb_car, $word);
?>
</body>
</html>