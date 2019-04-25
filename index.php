<?php
require_once("config.php");
if($GLOBALS['_setting_isFirstStart']){
	require_once("_install.php");
	die();
}

session_start();
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// On vérifie que l'on n'est pas en mode XHR
if(!isset($_GET['action']) || substr($_GET['action'],0,4)!=="xhr_"): ?>

<!DOCTYPE html>
<html lang="fr">
	<head>

		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="assets/stylesheets/bootstrap.css" rel="stylesheet">
		<link href="assets/stylesheets/icomoon.css" rel="stylesheet">
		<link href="assets/stylesheets/main.css" rel="stylesheet">
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
<?php endif;
/**
 * Gestionnaire de congés par François LEFÈVRE
 * v1
 */

// Variables globales pour index.php
$GLOBALS["notification"] = array();
$GLOBALS["html_header_title"] = "Congés ".$GLOBALS["_setting_enterprise_name"];
$GLOBALS["html_stylesheets"] = array();
$GLOBALS["html_scripts_imported"] = array();

$GLOBALS["register_input_haserror"] = false;
$GLOBALS["register_input_username_haserror"] = false;
$GLOBALS["register_input_username_pattern_haserror"] = false;
$GLOBALS["register_input_password_mismatch"] = false;
$GLOBALS["register_input_passwordnew_haserror"] = false;
$GLOBALS["register_input_passwordrepeat_haserror"] = false;
$GLOBALS["register_input_password_pattern_haserror"] = false;
$GLOBALS["register_input_role_haserror"] = false;
$GLOBALS["register_input_defaulttab_haserror"] = false;
$GLOBALS["register_input_firstname_haserror"] = false;
$GLOBALS["register_input_firstname_pattern_haserror"] = false;
$GLOBALS["register_input_lastname_haserror"] = false;
$GLOBALS["register_input_lastname_pattern_haserror"] = false;
$GLOBALS["register_input_email_haserror"] = false;
$GLOBALS["register_input_email_pattern_haserror"] = false;
$GLOBALS["register_input_days_pattern_haserror"] = false;

$GLOBALS['content_generator'] = NULL; // Définira quel module devra écrire

// Fonctions globales
function pushNotification($type,$content) {
	switch($type) {
		case "banner_success":
			$classes = "alert_banner alert alert-success col-xs-12 col-sm-offset-1 col-sm-10 col-lg-offset-2 col-lg-8 alert-dismissible fade in";
		break;

		case "banner_info":
			$classes = "alert_banner alert alert-info col-xs-12 col-sm-offset-1 col-sm-10 col-lg-offset-2 col-lg-8 alert-dismissible fade in";
		break;

		case "banner_warning":
			$classes = "alert_banner alert alert-warning col-xs-12 col-sm-offset-1 col-sm-10 col-lg-offset-2 col-lg-8 alert-dismissible fade in";
		break;

		case "banner_error":
			$classes = "alert_banner alert alert-danger col-xs-12 col-sm-offset-1 col-sm-10 col-lg-offset-2 col-lg-8 alert-dismissible fade in";
		break;

		default:
			$classes = "alert_banner alert alert-info col-xs-12 col-sm-offset-1 col-sm-10 col-lg-offset-2 col-lg-8 alert-dismissible fade in";
	}

	$GLOBALS["notification"][] = array($content,$classes);
}

require_once("login.class.php"); // On importe la classe du module de login

$GLOBALS['loginApp'] = new OneFileLoginApplication(); // On instancie le système de login

// On vérifie que l'on a le droit de lancer l'application (elle est uniquement accessible si logué)
if($GLOBALS['loginApp']->runLoginSystem()) {
	// On lance les modules de l'application
	require_once("vacationpanel.class.php"); // On importe la classe du module principal VacationPanel
	$vacationPanelApp = new VacationPanel(); // On instancie l'application pour la lancer via le constructeur

} else {
	$GLOBALS['content_generator'] = "loginApp"; // Le module de login affiche ses pages
}

// On génère le header de la page en HTML

// On vérifie que l'on n'est pas en mode XHR
if(!isset($_GET['action']) || substr($_GET['action'],0,4)!=="xhr_"): ?>
		<title><?=$GLOBALS["html_header_title"] ?></title>
<?php foreach($GLOBALS["html_stylesheets"] as $temp_stylesheet_href): ?>
		<link rel="stylesheet" href="<?=$temp_stylesheet_href ?>">
<?php endforeach; ?>

	</head>
	<body>
<?php foreach($GLOBALS["notification"] as $temp_notification_content): ?>
		<div class="<?=$temp_notification_content[1] ?>" role="alert"><?=$temp_notification_content[0] ?><button type="button" class="close" data-dismiss="alert" aria-label="Fermer"><span aria-hidden="true">&times;</span></button></div>
<?php endforeach;
endif;

// On génère le contenu
if(isset($GLOBALS['content_generator'])) {
	switch($GLOBALS['content_generator']) {
		case "loginApp":
			$GLOBALS['loginApp']->writeContent();
		break;

		case "vacationPanelApp":
			$vacationPanelApp->writeContent();
		break;
	}
}

// On vérifie que l'on n'est pas en mode XHR
if(!isset($_GET['action']) || substr($_GET['action'],0,4)!=="xhr_"): ?>
		<script type="text/javascript" src="assets/javascript/jquery-2.1.4.min.js"></script>
		<script type="text/javascript" src="assets/javascript/bootstrap.js"></script>
<?php foreach($GLOBALS["html_scripts_imported"] as $temp_script_src): ?>
		<script type="text/javascript" src="<?=$temp_script_src ?>"></script>
<?php endforeach; ?>
	</body>
</html>
<?php endif; ?>
