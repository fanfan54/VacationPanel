<?php
// Page simplifiée d'installation de l'application
session_start();
$_SESSION['is_server_admin'] = false;
function get_key($bit_length = 1024){
    $fp = @fopen('/dev/urandom','rb');
    if ($fp !== FALSE) {
        $key = substr(base64_encode(@fread($fp,($bit_length + 7) / 8)), 0, (($bit_length + 5) / 6)  - 2);
        @fclose($fp);
        return $key;
    }
    return null;
}

if(!empty($_POST['setup_input_hashcheck'])) {
	try
    {
      $fileName = dirname(__FILE__)."/vacationpanelsetupcheck.txt";

      if ( !file_exists($fileName) ) {
        throw new Exception('Le fichier temporaire a été supprimé par un processus étranger. Réessayez.');
      }

      $fp = fopen($fileName, "xb");
      if ( !$fp ) {
        throw new Exception('Impossible de lire le fichier temporaire.<br>Dans ces conditions la configuration du serveur est impossible.<br>Veuillez donner SEULEMENT À PHP les droits de lecture et écriture sur le dossier racine du Gestionnaire de Congés.');
      }  
      $str = stream_get_contents($fp);
      echo $str;
      if($_POST['setup_input_hashcheck'] == $str) {
      	$_SESSION['is_server_admin'] = true;
      }
      fclose($fp);

      // send success JSON

    } catch ( Exception $e ) {
      // send error message if you can
    	?><div class="alert_banner alert alert-danger col-xs-12 col-sm-offset-1 col-sm-10 col-lg-offset-2 col-lg-8 alert-dismissible fade in" role="alert"><?=$e->getMessage() ?><button type="button" class="close" data-dismiss="alert" aria-label="Fermer"><span aria-hidden="true">&times;</span></button></div>
    <?php

    }
} else {
	try
    {
      $fileName = dirname(__FILE__)."/vacationpanelsetupcheck.txt";

      $fp = fopen($fileName, "rb");
      if ( !$fp ) {
        throw new Exception('Impossible d\'écrire le fichier temporaire.<br>Dans ces conditions la configuration du serveur est impossible.<br>Veuillez donner SEULEMENT À PHP les droits de lecture et écriture sur le dossier racine du Gestionnaire de Congés.');
      }  
      fputs($fp,get_key(1024));
      fclose($fp);

      // send success JSON

    } catch ( Exception $e ) {
      // send error message if you can
    	?><div class="alert_banner alert alert-danger col-xs-12 col-sm-offset-1 col-sm-10 col-lg-offset-2 col-lg-8 alert-dismissible fade in" role="alert"><?=$e->getMessage() ?><button type="button" class="close" data-dismiss="alert" aria-label="Fermer"><span aria-hidden="true">&times;</span></button></div>
    <?php

    }
}

?>

<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="assets/stylesheets/bootstrap.css" rel="stylesheet">
		<style>
			body {
				margin-top: 0 !important;
			}
			.text-info {
				color: #5bc0de;
			}
			h1 {
				font-weight: bold;
				font-family: sans-serif;
			}
			.well-danger {
				color: #ffffff !important;
				background-color: #d64e18;
				box-shadow: inset 0 -4px 0 0 #e96a38;
				border-color: #d64e18;
			}
			.white {
				color: white !important;
			}
			footer {
			  height: 60px;
			  background-color: #f5f5f5;
			  margin-top:50px;
			  padding-top:20px;
			  padding-bottom:20px;
			}

			footer {
			  background-color:#414141;
			}

			footer a {
			  color:#efefef;
			}
		</style>
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		<title>Configuration du Gestionnaire de Congés</title>
	</head>
	<body>      
		<div class="jumbotron" id="jheader">
  			<div class="container">
	  			<h1>Configurons votre nouveau <span class="text-info">Gestionnaire de Congés</span>...</h1>
	  			<p>Vous avez choisi une solution simple, élégante et efficace pour gérer les congés de vos employés.<br>
	  				Merci !<br>
	  				Vous allez voir, l'installation est facile et rapide.<br>
	  				Dans cinq minutes l'application sera parfaitement adaptée à votre entreprise.</p>
  			</div>
		</div>
      <section class="container">

        <form accept-charset="utf-8" method="post" action="<?=$_SERVER['SCRIPT_NAME'] ?>" name="setupform" class="col-md-10 col-md-offset-1">
          <?php if($_SESSION['is_server_admin']): ?>
          	<div class="well">
          <fieldset>
          	<legend class="text-center">Informations essentielles</legend>
            <div class="form-group">
              <label for="setup_input_enterprisename">Nom de votre entreprise</label>
              <div class="input-group">
                <input autofocus required id="setup_input_enterprisename" type="text" pattern=".{1,50}" class="form-control input-lg" name="setup_input_enterprisename" />
                <span class="input-group-btn">
                  <button onclick="$('#setup_input_enterprisename').val('')" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-remove-circle"></span></button>
                </span>
              </div>
              <span class="help-block">Le nom de votre entreprise sera affiché en tant que titre et marque dans les pages de l'application et dans les e-mails envoyés aux utilisateurs par celle-ci.<br>De 1 à 50 caractères au total.</span>
            </div>
			<div class="form-group">
	       		<label for="register_input_isEnabled" class="important-field">
	        	<input type="checkbox" id="register_input_isEnabled" name="register_input_isEnabled" checked />
	        	IMPORTANT - Activer le compte (activé par défaut)</label>
	        	<span class="help-block important-field">Ce paramètre est important car si vous décochez la case, le compte sera créé mais l'utilisateur ne pourra pas se connecter à son compte tant que vous n'aurez pas activé manuellement le compte dans le <a href="index.php?action=manageusers&quickAction=enableaccount">panneau de gestion des utilisateurs</a>.<br>Vous pouvez également activer d'office le compte mais le désactiver après (ex: vacances, sanction envers l'utilisateur, etc...) dans le même panneau de gestion.<br>Activé par défaut.</span>
	        </div>
          </fieldset>
          <fieldset class="well">
          	<legend class="text-center">Connexion à votre base de données</legend>
            <div class="form-group">
              <label for="setup_input_enterprisename">Nom de votre entreprise</label>
              <div class="input-group">
                <input autofocus required id="setup_input_enterprisename" type="text" pattern=".{1,50}" class="form-control input-lg" name="setup_input_enterprisename" />
                <span class="input-group-btn">
                  <button onclick="$('#setup_input_enterprisename').val('')" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-remove-circle"></span></button>
                </span>
              </div>
              <span class="help-block">Le nom de votre entreprise sera affiché en tant que titre et marque dans les pages de l'application et dans les e-mails envoyés aux utilisateurs par celle-ci.<br>De 1 à 50 caractères au total.</span>
            </div>
          </fieldset>
            <div class="form-group">
              <label for="register_input_password_new">Mot de passe (obligatoire)</label>
              <div class="input-group">
                <span onmouseenter="$('#register_input_password_new').attr('type','text');$('#register_input_password_repeat').attr('type','text');" onmouseleave="$('#register_input_password_repeat').attr('type','password');$('#register_input_password_new').attr('type','password');" class="input-group-addon input-lg">
                  <span class="glyphicon glyphicon-eye-open"></span>
                </span>
                <input required id="register_input_password_new" type="password" pattern=".{6,}" class="form-control input-lg" name="register_input_password_new" autocomplete="off" />
                <span class="input-group-btn">
                  <button onclick="$('#register_input_password_new').val('');$('#register_input_password_repeat').val('');" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-remove-circle"></span></button>
                </span>
              </div>
              <span class="help-block">Mot de passe confidentiel à retenir par l'utilisateur, il ne pourra pas être récupéré une fois perdu.<br>Au moins 6 caractères.</span>
            </div>
            <div class="form-group">
              <label for="register_input_password_repeat">Mot de passe (répétez) (obligatoire)</label>
              <div class="input-group">
                <span onmouseenter="$('#register_input_password_new').attr('type','text');$('#register_input_password_repeat').attr('type','text');" onmouseleave="$('#register_input_password_repeat').attr('type','password');$('#register_input_password_new').attr('type','password');" class="input-group-addon input-lg">
                  <span class="glyphicon glyphicon-eye-open"></span>
                </span>
                <input required id="register_input_password_repeat" type="password" pattern=".{6,}" class="form-control input-lg" name="register_input_password_repeat" autocomplete="off" placeholder="Entrez le même mot de passe qu'indiqué ci-dessus" />
                <span class="input-group-btn">
                  <button onclick="$('#register_input_password_new').val('');$('#register_input_password_repeat').val('');" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-remove-circle"></span></button>
                </span>
              </div>
              <span class="help-block">Afin de nous assurer que vous vous souviendrez du mot de passe, veuillez ré-entrer le même mot de passe une deuxième fois.</span>
            </div>
          </fieldset>
          <div class="form-group">
            
            <label for="register_input_role">Type de compte (obligatoire) (si l'utilisateur est un Gestionnaire il pourra devenir Employé)</label>
            <select required id="register_input_role" class="form-control input-lg" name="register_input_role">
              <option selected value="worker">Employé</option>
              <option value="manager">Gestionnaire</option>
              <option value="manager-worker">Gestionnaire et employé</option>
            </select>
            <span class="help-block">Le type de compte est très important, il détermine les autorisations de l'utilisateur sur le serveur.<br>
              <ul>
                <li>Un employé peut seulement demander des jours de congés, voir les jours de congés des autres employés si la fonction est activée dans cette entreprise, et gérer ses paramètres personnels.</li>
                <li>Un gestionnaire peut gérer les demandes de congés des employés, les accepter ou les refuser, et contacter ceux-ci via l'application. Il peut également gérer ses paramètres personnels et créer à son tour des comptes pour de nouveaux employés. Par contre, s'il n'est pas également employé, il ne peut pas demander ses jours de congés.</li>
              </ul>
            </span>
            
          </div>
          <div class="form-inline">
            <label>(obligatoire)</label>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-btn">
                  <button onclick="$('#register_input_firstname').val($('#register_input_username').val().split('.')[0].capitalize());$('#register_input_lastname').val($('#register_input_username').val().split('.')[1].capitalize());" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-flash"></span></button>
                </span>
                <label class="sr-only" for="register_input_firstname">Prénom</label>
                <input required name="register_input_firstname" type="text" class="form-control input-lg" pattern=".{2,255}" id="register_input_firstname" placeholder="Prénom" />
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <label class="sr-only" for="register_input_lastname">Nom</label>
                <input required name="register_input_lastname" type="text" class="form-control input-lg" pattern=".{2,255}" id="register_input_lastname" placeholder="Nom" />
                <span class="input-group-btn">
                  <button onclick="$('#register_input_lastname').val('');$('#register_input_firstname').val('');" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-remove-circle"></span></button>
                </span>
              </div>
            </div>
            <span class="help-block">Les noms et prénoms de l'utilisateur sont utilisés dans l'interface graphique (notamment dans les menus, les calendriers...) et dans les e-mailings à des fins de personnalisation.<br>CONSEIL : Cliquez sur l'éclair pour remplir automatiquement les champs si l'identifiant est au format prenom.nom.<br>Entre 2 et 255 caractères.</span>
          </div>
          <div class="form-group">
            <label for="register_input_email">Adresse e-mail (obligatoire)</label>
            <div class="input-group">
              <span class="input-group-btn">
                <button onclick="$('#register_input_email').val($('#register_input_username').val()+'@')" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-flash"></span></button>
              </span>
              <input required id="register_input_email" type="email" class="form-control input-lg" name="register_input_email" placeholder="Cliquez sur l'éclair pour insérer l'identifiant" />
              <span class="input-group-btn">
                <button onclick="$('#register_input_email').val('')" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-remove-circle"></span></button>
              </span>
            </div>
            <span class="help-block">Entrez une adresse e-mail au format correct.<br>CONSEIL : cliquez sur l'éclair pour insérer dans le champ l'identifiant de l'utilisateur, au cas où ce serait le même pour son adresse e-mail.<br>L'utilisateur doit pouvoir consulter les e-mails importants (à propos de ses congés notamment) qui seront envoyés à cette adresse.</span>
          </div>
          <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
            <div class="panel panel-default">
              <div class="panel-heading" role="tab" id="headingOne">
                <h4 class="panel-title">
                  <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne" class="collapsed">Autres paramètres qui seront réglés sur les valeurs par défaut sans modification de votre part (cliquez pour déplier)</a>
                </h4>
              </div>
              <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">
                  
                  <div class="form-group" id="register_input_days_formgroup">
                    <label for="register_input_days">Solde de congés disponible au départ (seulement pour les employés, par défaut 25)</label>
                    <div class="input-group">
                      <input id="register_input_days" type="number" class="form-control" name="register_input_days" min="0" value="25" />
                      <span class="input-group-btn">
                        <button onclick="$('#register_input_days').val('25')" class="btn btn-default" type="button"><span class="glyphicon glyphicon-remove-circle"></span></button>
                      </span>
                    </div>
                    <span class="help-block">Détermine le solde de congés qu'aura à disposition l'employé dès la création de son compte.<br>L'employé pourra demander un nombre de jours de congés égal à son solde restant.</span>
                  </div>
            
                  <div class="form-group">
                    <label for="register_input_isFirstLogin">
                    <input type="checkbox" id="register_input_isFirstLogin" name="register_input_isFirstLogin" checked />
                      Activer le didacticiel au premier démarrage (activé par défaut)</label>
                    <span class="help-block">Lancer ou non le didacticiel de découverte de l'application dès la première connexion de l'utilisateur.<br>L'utilisateur pourra choisir d'ignorer ce didacticiel mais il lui sera proposé d'office.<br>Activé par défaut.</span>
                  </div>
                  <div id="register_input_defaulttab_formgroup" class="form-group">
            
                    <label for="register_input_defaulttab">Onglet par défaut (par défaut "Gérer les congés" pour les gestionnaires et "Demander mes congés" pour les employés)</label>
                    <select disabled required id="register_input_defaulttab" class="form-control input-lg" name="register_input_defaulttab">
                      <optgroup id="worker-tabs" label="Employés">
                        <option id="preferredtab-worker" selected value="vacationask">Demander mes congés</option>
                      </optgroup>
                      <optgroup id="manager-tabs" label="Gestionnaires" disabled>
                        <option id="preferredtab-manager" value="vacationmanage">Gérer les congés</option>
                        <option value="manageusers">Gérer les employés</option>
                        <option value="registerusers">Ajouter des employés</option>
                      </optgroup>
                    </select>
                    <span class="help-block">Choisissez l'onglet par défaut de l'utilisateur, c'est-à-dire l'onglet qui sera affiché après connexion complète de l'utilisateur (sauf si un onglet particulier est demandé dans la requête).<br><a style="pattern-error" onclick="$('#register_input_role').focus()">Si l'utilisateur est un gestionnaire et employé (cliquez pour changer son rôle)</a>, il peut sélectionner l'onglet "Demander mes congés".</span>
            
          </div>
                </div>
              </div>
            </div>
          </div>
    <?php else: ?>
          <div class="well well-danger">
			<fieldset>
          	<legend class="text-center white">Confirmez votre identité</legend>
            <div class="form-group">
              <span class="help-block white">L'application a créé un fichier nommé "vacationpanelsetupcheck.txt" dans son répertoire.<br>Veuillez recopier le contenu de ce fichier.</span>
              <div class="input-group">
                <input autofocus required id="setup_input_hashname" type="text" class="form-control input-lg" name="setup_input_hashname" />
                <span class="input-group-btn">
                  <button onclick="$('#setup_input_hashname').val('')" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-remove-circle"></span></button>
                </span>
              </div>
            </div>
          </fieldset>
      </div>
    <?php endif; ?>
          <div class="row form-group">
            <input type="submit" name="register_input_submit" value="Valider" id="register_input_submit" class="btn-lg col-xs-4 col-xs-offset-1 btn btn-primary" />
            <input type="reset" name="register_input_reset" value="Effacer" id="register_input_reset" class="btn-lg col-xs-4 col-xs-offset-2 btn btn-danger" />
          </div>
        </form>
      </section>
          <span id="top-link-block" class="hidden">
        <a href="#top" class="well well-sm"  onclick="$('html,body').animate({scrollTop:0},'slow');return false;">
          <i class="glyphicon glyphicon-chevron-up"></i> Haut de page
        </a>
      </span>
      <footer>
        <div class="container">
          <p class="text-muted">Copyright (c) NETLOR 2015</p>
        </div>
      </footer>

				<script type="text/javascript" src="assets/javascript/jquery-2.1.4.min.js"></script>
				<script type="text/javascript" src="assets/javascript/bootstrap.js"></script>		
	</body>
</html>