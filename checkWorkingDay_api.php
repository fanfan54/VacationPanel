<?
/******************************************************************************/
/*                                                                            */
/*                       __        ____                                       */
/*                 ___  / /  ___  / __/__  __ _____________ ___               */
/*                / _ \/ _ \/ _ \_\ \/ _ \/ // / __/ __/ -_|_-<               */
/*               / .__/_//_/ .__/___/\___/\_,_/_/  \__/\__/___/               */
/*              /_/       /_/                                                 */
/*                                                                            */
/*                                                                            */
/******************************************************************************/
/*                                                                            */
/* Titre          : Déterminer rapidement si un jour est férié (fêtes...      */
/*                                                                            */
/* URL            : http://www.phpsources.org/scripts382-PHP.htm              */
/* Auteur         : Olravet                                                   */
/* Date édition   : 05 Mai 2008                                               */
/* Website auteur : http://olravet.fr/                                        */
/*                                       réécrit par François LEFÈVRE         */
/******************************************************************************/
mb_internal_encoding('UTF-8');
// On déclare la (GROSSE) fonction
function checkWorkingDay($timestamp)
{
	// On récupère les données du timestamp
$day = date("j", $timestamp);
$month = date("n", $timestamp);
$year = date("Y", $timestamp);

	// On récupère les propriétés du jour nécessaires
$dayOfWeek = date("N", $timestamp);

	// On récupère les dates des jours fériés mobiles
	// Dimanche de pâques (base)
$easter_date = easter_date($year);
$easter_day = date("j", $easter_date);
$easter_month = date("n", $easter_date);
	// Lundi de Pâques (férié)
$easter_monday = mktime(0, 0, 0, date("n", $easter_date), date("j", $easter_date)+1, date("Y", $easter_date));
$easter_monday_day = date("j", $easter_monday); // FÉRIÉ - jour
$easter_monday_month = date("n", $easter_monday); // FÉRIÉ - mois
	// Ascension (férié)
$ascension = mktime(0, 0, 0, date("n", $easter_date), date("j", $easter_date)+39, date("Y", $easter_date));
$ascension_day = date("j", $ascension); // FÉRIÉ - jour
$ascension_month = date("n", $ascension); // FÉRIÉ - mois
	// Lundi de Pentecôte 
$pentecote_monday = mktime(0, 0, 0, date("n", $easter_date), date("j", $easter_date)+50, date("Y", $easter_date));
$pentecote_monday_day = date("j", $pentecote_monday); // FÉRIÉ - jour
$pentecote_monday_month = date("n", $pentecote_monday); // FÉRIÉ - mois

	// On crée un array contenant jour et mois sélectionnés, pour le switch
$day_selected = array($day,$month);

$isWorkingDay = false;
switch($day_selected)
{
	case array('1','1'):
		$vacationName = "Jour de l'an (1er janvier)";
	break;

	case array($easter_monday_day,$easter_monday_month):
		$vacationName = "Lundi de Pâques";
	break;

	case array('1','5'):
		$vacationName = "Fête du travail (1er mai)";
	break;

	case array('8','5'):
		$vacationName = "8 mai";
	break;

	case array($ascension_day,$ascension_month):
		$vacationName = "Ascension";
	break;

	case array($pentecote_monday_day,$pentecote_monday_month):
		$vacationName = "Lundi de Pentecôte";
	break;

	case array('14','7'):
		$vacationName = "Fête nationale (14 juillet)";
	break;

	case array('15','8'):
		$vacationName = "15 août";
	break;

	case array('1','11'):
		$vacationName = "1er novembre";
	break;

	case array('11','11'):
		$vacationName = "11 novembre";
	break;

	case array('25','12'):
		$vacationName = "Noël (25 décembre)";
	break;

	default:
		if($dayOfWeek>5) {
			$vacationName = "Week-end";
		}
		else {
			$vacationName = "(jour de travail)";
			$isWorkingDay = true;
		}
		
}

// On prépare un array retournant en sortie un booléen indiquant si l'on travaille ou non, et le nom de l'évènement impliquant que l'on ne travaille pas
$return = array(
	'isWorkingDay'=>$isWorkingDay,
	'vacationName'=>$vacationName);
return $return;
}

// On récupère les arguments passés en GET (day, month, year)
if(isset($_GET['day']) && isset($_GET['month']) && isset($_GET['year'])){
	$date_query=mktime(0,0,0,$_GET['month'],$_GET['day'],$_GET['year']);
	echo json_encode(checkWorkingDay($date_query)); // On appelle la fonction
}

?>