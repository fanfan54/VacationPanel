<?php
mb_internal_encoding('UTF-8');
/**
 * Classe VacationPanel
 * Regroupe toutes les actions globales de l'interface loguée de VacationPanel
 *
 * @author François Lefèvre
 */
class VacationPanel {
	
	private $content_mode;
  private $userrole_tutored = array(); // Définit le grade des comptes utilisateurs pouvant être gérés par l'utilisateur logué
  private $userstutored_string;
  private $userrole_string;

	public function __construct() {
    // Cool, l'application se lance :D

		$this->setDefaultHeaders(); // On écrit les headers par défaut communs à chaque module
    $this->identifyRequestedModule(); // On identifie le module demandé par l'utilisateur
	}

	public function writeContent() {
		// if(isset($this->content_mode)) {
		
    // On affiche le header de l'application
    ?>

      <header id="global-header">
        <nav class="navbar navbar-default navbar-fixed-top">
          <div class="container-fluid">
            <div class="navbar-header">
              <a class="navbar-brand" href="#">
                <!--<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAMAAAC7IEhfAAAA81BMVEX///9VPnxWPXxWPXxWPXxWPXxWPXxWPXz///9hSYT6+vuFc6BXPn37+vz8+/z9/f2LeqWMe6aOfqiTg6uXiK5bQ4BZQX9iS4VdRYFdRYJfSINuWI5vWY9xXJF0YJR3Y5Z4ZZd5ZZd6Z5h9apq0qcW1qsW1q8a6sMqpnLyrn76tocCvpMGwpMJoUoprVYxeRoJjS4abjLGilLemmbrDutDFvdLPx9nX0eDa1OLb1uPd1+Td2OXe2eXh3Ofj3+nk4Orl4evp5u7u7PLv7fPx7/T08vb08/f19Pf29Pj39vn6+fuEcZ9YP35aQn/8/P1ZQH5fR4PINAOdAAAAB3RSTlMAIWWOw/P002ipnAAAAPhJREFUeF6NldWOhEAUBRvtRsfdfd3d3e3/v2ZPmGSWZNPDqScqqaSBSy4CGJbtSi2ubRkiwXRkBo6ZdJIApeEwoWMIS1JYwuZCW7hc6ApJkgrr+T/eW1V9uKXS5I5GXAjW2VAV9KFfSfgJpk+w4yXhwoqwl5AIGwp4RPgdK3XNHD2ETYiwe6nUa18f5jYSxle4vulw7/EtoCdzvqkPv3bn7M0eYbc7xFPXzqCrRCgH0Hsm/IjgTSb04W0i7EGjz+xw+wR6oZ1MnJ9TWrtToEx+4QfcZJ5X6tnhw+nhvqebdVhZUJX/oFcKvaTotUcvUnY188ue/n38AunzPPE8yg7bAAAAAElFTkSuQmCC" alt="Logo du Gestionnaire de congés">-->
                <span class="hidden-xs hidden-sm">Congés <?=$GLOBALS['loginApp']->textTruncate($GLOBALS['_setting_enterprise_name'],20) ?></span>
              </a>
            </div>
            <ul class="nav navbar-nav navbar-header navbar-right">
              <li class="divider-vertical"></li>
              <li><button type="button" data-toggle="popover" data-toggle="tooltip" id="notifications-menu-button" class="help-tooltip btn btn-default navbar-btn"><span class="navbar-text glyphicon glyphicon-envelope menu-button-icon" id="notifications-menu-icon" aria-hidden="true"></span><span id="new-notifications-badge" class="badge badge-notify">1</span></button></li>
              <li class="divider-vertical"></li>
              <li><p class="navbar-text" id="user-resume"><span class="glyphicon glyphicon-user"></span>&nbsp;Bonjour <strong><?=$GLOBALS['loginApp']->textTruncate($_SESSION['user_firstname'],30) ?></strong><?php if(in_array("worker",$_SESSION['user_role'])): ?> <span data-toggle="tooltip" data-placement="bottom" title="Grade standard. Vous pouvez demander vos jours de congés, voir les jours de congés des autres employés si la fonction est activée dans cette entreprise, et gérer vos paramètres personnels." class="help-tooltip label label-default">employé</span><?php endif;
              if(in_array("manager",$_SESSION['user_role'])): ?> <span data-toggle="tooltip" data-placement="bottom" title="Grade supérieur. Destiné aux chefs de groupe et managers qui pourront gérer les demandes de congés des employés, les accepter ou les refuser, les contacter via l'application... Il peut gérer ses paramètres personnels et créer des comptes pour de nouveaux employés." class="help-tooltip label label-primary">gestionnaire</span><?php endif;
              if(in_array("sysadmin",$_SESSION['user_role'])): ?> <span data-toggle="tooltip" data-placement="bottom" title="Grade maximal. L'administrateur système est le responsable informatique chargé de la gestion de l'application. Il est en charge de mettre à jour l'application, s'occuper de son installation et créer des comptes gestionnaires. Il peux également effacer toute la base de données et gérer les paramètres globaux de l'application." class="label label-danger help-tooltip">admin</span><?php endif; ?></p></li>
              <li><button type="button" data-toggle="tooltip" data-toggle="popover" id="usersettings-menu-button" class="help-tooltip btn btn-primary navbar-btn"><span class="navbar-text glyphicon glyphicon-cog menu-button-icon" id="usersettings-menu-icon" aria-hidden="true"></span></button>&nbsp;</li>
              <li><button type="button" data-toggle="tooltip" data-placement="left" title="Se déconnecter, supprimer vos données d'identification de cet ordinateur et quitter l'application." onclick='location.href="<?=$_SERVER['SCRIPT_NAME']."?action=logout" ?>"' id="logout-menu-button" class="help-tooltip btn btn-danger navbar-btn"><span class="navbar-text glyphicon glyphicon-off menu-button-icon" id="logout-menu-icon" aria-hidden="true"></span></button></li>
            </ul>
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
          </div>

          <?=$this->buildTabsNav() ?>
        
        </nav>
      </header>
      <?php
        // On demande au module générateur de contenu d'écrire le contenu
        switch($this->content_mode) {
          case "vacationAsk":
            $this->showPageVacationAsk();
          break;

          case "vacationManage":
            $this->showPageVacationManage();
          break;

          case "manageUsers":
            $this->showPageManageUsers();
          break;

          case "registerUsers":
            $this->showPageRegisterUsers();
          break;

          case "systemSettings":
            $this->showPageSystemSettings();
          break;

          default:
            $GLOBALS['loginApp']->doLogout("ERREUR - Vous avez été déconnecté car une erreur fatale s'est produite, le module qui devait s'afficher est introuvable.<br>Veuillez réessayer. Si le problème persiste merci de contacter le support technique.");
        }

        // On affiche le footer de l'application

      ?>
      <span id="top-link-block" class="hidden">
        <a href="#top" class="well well-sm"  onclick="$('html,body').animate({scrollTop:0},'slow');return false;">
          <i class="glyphicon glyphicon-chevron-up"></i> Haut de page
        </a>
      </span>
      <footer>
        <div class="container">
          <p class="text-muted">Copyright &copy; <?=$GLOBALS['_setting_enterprise_name'] ?> 2015-<?=date("Y") ?></p>
        </div>
      </footer>

		<?php
		// }
	}

  private function identifyRequestedModule() { // On identifie le module demandé par l'utilisateur
    if(isset($_GET['action']) || isset($_SESSION['last_module']) || isset($_SESSION['user_defaultTab'])) { // On regarde si l'utilisateur peut lancer le module demandé via GET
      if(isset($_GET['action'])) { // GET a priorité sur la session
        $moduleRequested = $_GET['action'];
      } elseif(isset($_SESSION['last_module'])) {
        $moduleRequested = $_SESSION['last_module'];
      } elseif(isset($_SESSION['user_defaultTab'])) {
        $moduleRequested = $_SESSION['user_defaultTab'];
      } else {
        $this->runPreferredModule();
        return false;
      }

      switch($moduleRequested) {
        case "vacationask":
          if(in_array("worker",$_SESSION['user_role'])) {
            $this->setHeadersPageVacationAsk();
          } else {
            pushNotification("banner_warning","Vous n'êtes pas autorisé à accéder à l'onglet demandé.");
            $this->runPreferredModule();
          }
        break;

        case "vacationmanage":
          if(in_array("manager",$_SESSION['user_role'])) {
            $this->setHeadersPageVacationManage();
          } else {
            pushNotification("banner_warning","Vous n'êtes pas autorisé à accéder à l'onglet demandé.");
            $this->runPreferredModule();
          }
        break;

        case "manageusers":
          if(in_array("manager",$_SESSION['user_role']) || in_array("sysadmin",$_SESSION['user_role'])) {
            $this->setHeadersPageManageUsers();
          } else {
            pushNotification("banner_warning","Vous n'êtes pas autorisé à accéder à l'onglet demandé.");
            $this->runPreferredModule();
          }
        break;

        case "registerusers":
          if(in_array("manager",$_SESSION['user_role']) || in_array("sysadmin",$_SESSION['user_role'])) {
            $this->setHeadersPageRegisterUsers();
          } else {
            pushNotification("banner_warning","Vous n'êtes pas autorisé à accéder à l'onglet demandé.");
            $this->runPreferredModule();
          }
        break;

        case "systemsettings":
          if(in_array("sysadmin",$_SESSION['user_role'])) {
            $this->setHeadersPageSystemSettings();
          } else {
            pushNotification("banner_warning","Vous n'êtes pas autorisé à accéder à l'onglet demandé.");
            $this->runPreferredModule();
          }
        break;

        default:
		  pushNotification("banner_warning","Vous n'êtes pas autorisé à accéder à l'onglet demandé.");
          $this->runPreferredModule();
          return false;
      }
    } else {
      $this->runPreferredModule();
      return false;
    }
  }

  private function runPreferredModule() { // On identifie le module par défaut pour son rôle majeur
    if(in_array("sysadmin",$_SESSION['user_role'])) {
      $this->setHeadersPageRegisterUsers();
    } elseif(in_array("manager",$_SESSION['user_role'])) {
      $this->setHeadersPageVacationManage();
    } elseif(in_array("worker",$_SESSION['user_role'])) {
      $this->setHeadersPageVacationAsk();
    } else {
      $GLOBALS['loginApp']->doLogout("ERREUR - Vous avez été déconnecté car une erreur fatale s'est produite, impossible de déterminer un onglet à ouvrir.<br>Votre compte existe mais il n'a le droit d'utiliser aucun module de l'application. Il est donc invalide.<br>Veuillez contacter le support technique");
    }
  }

  private function setHeadersPageVacationAsk() {
    $GLOBALS["html_header_title"] = $GLOBALS["html_header_title"]." - Demander mes congés";
    
    $GLOBALS["html_scripts_imported"][] = "assets/javascript/vacationask-ui.js";

    $GLOBALS["html_stylesheets"][] = "assets/stylesheets/bootstrap-datepicker3.css";
    $GLOBALS["html_scripts_imported"][] = "assets/javascript/bootstrap-datepicker.js";
    $GLOBALS["html_scripts_imported"][] = "assets/locales/bootstrap-datepicker.fr.min.js";
    $GLOBALS["html_scripts_imported"][] = "assets/javascript/jquery.initialize.js";
    $GLOBALS["html_scripts_imported"][] = "assets/javascript/datepicker.js";
    $GLOBALS["html_scripts_imported"][] = "assets/javascript/moment-with-locales.js";
    $GLOBALS["html_scripts_imported"][] = "assets/javascript/moment-ferie-fr.min.js";

    $this->content_mode = "vacationAsk";
    $_SESSION['last_module'] = "vacationask";
  }

  private function showPageVacationAsk() {
    ?>

      <div class="container">
        <aside id="affix" class="col-xs-12 col-sm-push-9 col-sm-3">
          <div class="well">
            <label>Affichage :</label>
            <div class="btn-group" role="group" aria-label="layout-settings-buttons">
              <button type="button" id="btn-calendar-mode" class="btn btn-danger"><span class="glyphicon glyphicon-calendar"></span><span class="hidden-xs"> Calendrier</span></button>
              <button type="button" id="btn-list-mode" class="btn btn-primary"><span class="glyphicon glyphicon-list"></span><span class="hidden-xs"> Liste</span></button>
            </div>
            <p class="help-block localStorageNote hidden hidden-sm hidden-xs">Votre choix sera sauvegardé pour les prochains lancements de l'application.</p>
            <br>
            <label>Votre solde de congés :</label>
            <ul class="list-unstyled">
              <li id="days-available-row" class="text-primary text-uppercase"><span class="icon-calendar-day"></span> <span id="days-available-val" class="badge badge-primary">NaN</span> jours de congés à prendre</li>
              <li id="days-waiting-row" class="text-uppercase"><span class="glyphicon glyphicon-time"></span> <span id="days-waiting-val" class="badge">NaN</span> jours en attente de validation</li>
              <li id="days-accepted-row" class="text-success text-uppercase"><span class="glyphicon glyphicon-ok"></span> <span id="days-accepted-val" class="badge badge-success">NaN</span> congés acceptés <a href="#" class="text-info">(plus d'infos)</a></li>
              <li id="days-refused-row" class="text-danger text-uppercase"><span class="glyphicon glyphicon-remove"></span> <span id="days-refused-val" class="badge badge-danger">NaN</span> congés refusés <a href="#" class="text-info">(plus d'infos)</a></li>
            </ul>
          </div>
        </aside>
        <div id="module-title" class="page-header col-md-9 col-xs-10">
          <h1>Demandez vos jours de congés ici<br>
		  <!-- Bug : le mode ne s'initialise pas correctement. Workaround TODO TODO : ne rien afficher par défaut et demander à l'utilisateur d'en choisir un à droite -->
            <small class="mode-dependent mode-calendar-only" id="small-title-calendar">Cliquez sur des jours de travail dans le calendrier puis validez pour demander un congé</small>
            <small class="mode-dependent mode-list-only" id="small-title-list" visibility="hidden">Tous vos congés en cours ou à venir apparaissent, cliquez sur <button type="button" class="btn-list-new-vacation btn btn-success"><span class="glyphicon glyphicon-plus"></span> Nouveau</button> pour demander un congé</small>
          </h1>
        </div>
        <section class="col-md-9 col-xs-12">
          <ul class="hidden" aria-hidden="true">
            <li class="active"><a id="toggle-calendar-mode" href="#panel-calendar-mode" aria-controls="panel-calendar-mode" role="tab" data-toggle="tab"></a></li>
            <li><a id="toggle-list-mode" href="#panel-list-mode" aria-controls="panel-list-mode" role="tab" data-toggle="tab"></a></li>
          </ul>
           <div class="tab-content" id="app-mode-content">
            <div role="tabpanel" active class="tab-pane fade in active" id="panel-calendar-mode">
              <div class="panel panel-default">
                <div class="panel-body">
                  <div class="color-legend">
                    <ul class="list-inline">
                      <li><span class="label label-warning">&nbsp;</span> <label>Aujourd'hui</label></li>
                    </ul>
                    <ul class="list-inline">
                      <li><span class="label label-primary">&nbsp;</span> <label>Congé souhaité</label></li>
                      <li><span class="label label-success">&nbsp;</span> <label>Congé confirmé</label></li>
                      <li><span class="label label-danger">&nbsp;</span> <label>Congé refusé</label></li>
                    </ul>
                    <ul class="list-inline">
                      <li><span class="label label-purple">&nbsp;</span> <label>Jour de repos</label></li>
                      <?php if($GLOBALS['_setting_show_colleague_vacations']): ?><li><span class="label label-aqua">&nbsp;</span> <label>Congé d'un collègue</label></li><?php endif; ?>
                    </ul>
                  </div>
                </div>
              </div>
              <div id="calendar-view" class="datepicker-xxl">
                <img alt="" class="datepicker-ajax-spinner" aria-hidden="true" src="assets/pictures/flipflop-preloader.gif">
              </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="panel-list-mode">



              (list mode)



            </div>
          </div>
        </section>
      </div>
    <?php

  }

  private function setHeadersPageVacationManage() {
    $GLOBALS["html_header_title"] = $GLOBALS["html_header_title"]." - Gérer les congés de l'entreprise";
    $this->content_mode = "vacationManage";
    $_SESSION['last_module'] = "vacationmanage";
  }

  private function setHeadersPageManageUsers() {
    if(in_array("manager",$_SESSION['user_role']) && in_array("sysadmin",$_SESSION['user_role'])) { // On définit les rôles possibles des utilisateurs supervisés en fonction du propre rôle de l'utilisateur logué
      $this->userrole_tutored = array("manager","worker");
      $this->userrole_string = "gestionnaire et administrateur système";
      $this->userstutored_string = "employés et gestionnaires";
    } elseif(in_array("sysadmin",$_SESSION['user_role'])) {
      $this->userrole_tutored = array("manager");
      $this->userrole_string = "administrateur système";
      $this->userstutored_string = "gestionnaires";
    } elseif(in_array("manager",$_SESSION['user_role'])) {
      $this->userrole_tutored = array("worker");
      $this->userrole_string = "gestionnaire";
      $this->userstutored_string = "employés";
    }


    $GLOBALS["html_header_title"] = $GLOBALS["html_header_title"]." - Gérer les ".$this->userstutored_string;
    $this->content_mode = "manageUsers";
    $_SESSION['last_module'] = "manageusers";
  }



  private function setHeadersPageSystemSettings() {
    $GLOBALS["html_header_title"] = $GLOBALS["html_header_title"]." - Paramètres du serveur";
    $this->content_mode = "systemSettings";
    $_SESSION['last_module'] = "systemsettings";
  }



  private function setHeadersPageRegisterUsers() {
    
    if(isset($_POST['register_input_username'])) { // On vérifie que l'on ne demande pas d'inscrire un nouvel utilisateur
      $GLOBALS['loginApp']->doRegistration(); // On lance le processus d'inscription dans la classe de login
    }

    if(in_array("manager",$_SESSION['user_role']) && in_array("sysadmin",$_SESSION['user_role'])) { // On définit les rôles possibles des nouveaux utilisateurs créés par l'utilisateur logué en fonction de son propre rôle
      $this->userrole_tutored = array("manager","worker");
      $this->userrole_string = "gestionnaire et administrateur système";
      $this->userstutored_string = "employés ou des gestionnaires";
    } elseif(in_array("sysadmin",$_SESSION['user_role'])) {
      $this->userrole_tutored = array("manager");
      $this->userrole_string = "administrateur système";
      $this->userstutored_string = "gestionnaires";
    } elseif(in_array("manager",$_SESSION['user_role'])) {
      $this->userrole_tutored = array("worker");
      $this->userrole_string = "gestionnaire";
      $this->userstutored_string = "employés";
    }


    $GLOBALS["html_header_title"] = $GLOBALS["html_header_title"]." - Ajouter des ".$this->userstutored_string;
    $this->content_mode = "registerUsers";
    $_SESSION['last_module'] = "registerusers";
  }

  private function showPageRegisterUsers() {
    ?>

      <section class="container">
        <div class="page-header">
          <h1>Créer de nouveaux comptes utilisateurs<br><small>En tant que <?=$this->userrole_string ?>, vous pouvez créer des comptes pour des <?=$this->userstutored_string ?></small></h1>
        </div>
        <form accept-charset="utf-8" method="post" action="index.php?action=registerusers" name="registerform" class="col-md-8 col-md-offset-2 well">
          <legend class="text-center"><h1>Nouvel utilisateur</h1></legend>
          <fieldset>
            <div class="<?php if($GLOBALS["register_input_username_haserror"] == true || $GLOBALS["register_input_username_pattern_haserror"] == true){echo "has-error ";}?>form-group">
              <label for="register_input_username">Identifiant (obligatoire) (ne pourra plus être modifié)</label>
              <div class="input-group">
                <span class="input-group-btn">
                  <button onclick="checkUsernameAvailability($('#register_input_username'));" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-search"></span></button>
                </span>
                <img alt="" class="field-ajax-spinner" aria-hidden="true" src="assets/pictures/flipflop-preloader.gif">
                <input <?php if($GLOBALS["register_input_haserror"] == false || $GLOBALS["register_input_username_haserror"] == true){echo "autofocus ";}?>required id="register_input_username" type="text" pattern="[a-z0-9\.]{2,64}" class="form-control input-lg" name="register_input_username" placeholder="Exemple : prenom.nom" />
                <span class="input-group-btn">
                  <button onclick="$('#register_input_username').val('')" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-remove-circle"></span></button>
                </span>
              </div>
              <span class="<?php if($GLOBALS["register_input_username_pattern_haserror"] == true){echo "pattern-error ";}?>help-block">Identifiant à retenir par l'utilisateur.<br>Lettres minuscules, chiffres et points "." seulement autorisés.<br>CONSEIL : Cliquez sur la loupe pour vérifier que le nom d'utilisateur choisi est disponible.<br>De 2 à 64 caractères au total.</span>
            </div>
            <div class="form-group">
              <label for="register_input_password_new">Mot de passe (obligatoire)</label>
              <div class="<?php if($GLOBALS["register_input_password_mismatch"] == true || $GLOBALS["register_input_passwordnew_haserror"] == true || $GLOBALS["register_input_password_pattern_haserror"] == true){echo "has-error ";}?>input-group">
                <span onmouseenter="$('#register_input_password_new').attr('type','text');$('#register_input_password_repeat').attr('type','text');" onmouseleave="$('#register_input_password_repeat').attr('type','password');$('#register_input_password_new').attr('type','password');" class="input-group-addon input-lg">
                  <span class="glyphicon glyphicon-eye-open"></span>
                </span>
                <input <?php if($GLOBALS["register_input_password_mismatch"] == true || $GLOBALS["register_input_passwordnew_haserror"] == true || $GLOBALS["register_input_passwordrepeat_haserror"] == true || $GLOBALS["register_input_password_pattern_haserror"] == true){echo "autofocus ";}?>required id="register_input_password_new" type="password" pattern=".{6,}" class="form-control input-lg" name="register_input_password_new" autocomplete="off" />
                <span class="input-group-btn">
                  <button onclick="$('#register_input_password_new').val('');$('#register_input_password_repeat').val('');" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-remove-circle"></span></button>
                </span>
              </div>
              <span class="<?php if($GLOBALS["register_input_password_pattern_haserror"] == true){echo "pattern-error ";}?>help-block">Mot de passe confidentiel à retenir par l'utilisateur, il ne pourra pas être récupéré une fois perdu.<br>Au moins 6 caractères.</span>
            </div>
            <div class="form-group">
              <label for="register_input_password_repeat">Mot de passe (répétez) (obligatoire)</label>
              <div class="<?php if($GLOBALS["register_input_password_mismatch"] == true || $GLOBALS["register_input_passwordrepeat_haserror"] == true){echo "has-error ";}?>input-group">
                <span onmouseenter="$('#register_input_password_new').attr('type','text');$('#register_input_password_repeat').attr('type','text');" onmouseleave="$('#register_input_password_repeat').attr('type','password');$('#register_input_password_new').attr('type','password');" class="input-group-addon input-lg">
                  <span class="glyphicon glyphicon-eye-open"></span>
                </span>
                <input required id="register_input_password_repeat" type="password" pattern=".{6,}" class="form-control input-lg" name="register_input_password_repeat" autocomplete="off" placeholder="Entrez le même mot de passe qu'indiqué ci-dessus" />
                <span class="input-group-btn">
                  <button onclick="$('#register_input_password_new').val('');$('#register_input_password_repeat').val('');" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-remove-circle"></span></button>
                </span>
              </div>
              <span class="<?php if($GLOBALS["register_input_password_mismatch"] == true){echo "pattern-error ";}?>help-block">Afin de nous assurer que vous vous souviendrez du mot de passe, veuillez ré-entrer le même mot de passe une deuxième fois.</span>
            </div>
          </fieldset>
          <div class="<?php if($GLOBALS["register_input_role_haserror"] == true){echo "has-error ";}?>form-group">
            <?php if(in_array("manager",$_SESSION['user_role']) && in_array("sysadmin",$_SESSION['user_role'])): ?>

            <label for="register_input_role">Type de compte (obligatoire) (si l'utilisateur est un Gestionnaire il pourra devenir Employé)</label>
            <select <?php if($GLOBALS["register_input_role_haserror"] == true){echo "autofocus ";}?>required id="register_input_role" class="form-control input-lg" name="register_input_role">
              <option selected value="worker">Employé</option>
              <option value="manager">Gestionnaire</option>
              <option value="manager-worker">Gestionnaire et employé</option>
            </select>
            <span class="<?php if($GLOBALS["register_input_role_haserror"] == true){echo "pattern-error ";}?>help-block">Le type de compte est très important, il détermine les autorisations de l'utilisateur sur le serveur.<br>
              <ul>
                <li>Un employé peut seulement demander des jours de congés, voir les jours de congés des autres employés si la fonction est activée dans cette entreprise, et gérer ses paramètres personnels.</li>
                <li>Un gestionnaire peut gérer les demandes de congés des employés, les accepter ou les refuser, et contacter ceux-ci via l'application. Il peut également gérer ses paramètres personnels et créer à son tour des comptes pour de nouveaux employés. Par contre, s'il n'est pas également employé, il ne peut pas demander ses jours de congés.</li>
              </ul>
            </span>
            <?php elseif(in_array("sysadmin",$_SESSION['user_role'])): ?>
  
            <label for="register_input_role">Type de compte (vous ne pouvez que choisir de créer un gestionnaire mais l'utilisateur pourra devenir Employé)</label>
            <select id="register_input_role" class="form-control input-lg" name="register_input_role" disabled>
              <option selected value="manager">Vous ne pouvez que choisir de créer un gestionnaire</option>
            </select>
            <span class="<?php if($GLOBALS["register_input_role_haserror"] == true){echo "pattern-error ";}?>help-block">Vous ne pouvez que choisir de créer un gestionnaire vu que vous n'êtes vous-même qu'un administrateur système. Vous pouvez cependant vous auto-proclamer gestionnaire dans vos Paramètres utilisateur <a id="openUserSettingsMenu" href="#">(cliquez ici pour ouvrir le menu)</a> afin de pouvoir créer un compte employé.</span>
            <?php elseif(in_array("manager",$_SESSION['user_role'])): ?>
  
            <label for="register_input_role">Type de compte (vous ne pouvez que choisir de créer un employé, statut non modifiable par l'utilisateur)</label>
            <select id="register_input_role" class="form-control input-lg" name="register_input_role" disabled>
              <option selected value="worker">Vous ne pouvez que choisir de créer un employé</option>
            </select>
            <span class="<?php if($GLOBALS["register_input_role_haserror"] == true){echo "pattern-error ";}?>help-block">Vous ne pouvez que choisir de créer un employé vu que vous n'êtes vous-même qu'un gestionnaire.</span>
            <?php endif; ?>

          </div>
          <div class="form-inline">
		  <!-- Mauvais affichage en responsive phablette -->
            <label>(obligatoire)</label>
            <div class="<?php if($GLOBALS["register_input_firstname_haserror"] == true || $GLOBALS["register_input_firstname_pattern_haserror"] == true){echo "has-error ";}?>form-group">
              <div class="input-group">
                <span class="input-group-btn">
                  <button onclick="$('#register_input_firstname').val($('#register_input_username').val().split('.')[0].capitalize());$('#register_input_lastname').val($('#register_input_username').val().split('.')[1].capitalize());" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-flash"></span></button>
                </span>
                <label class="sr-only" for="register_input_firstname">Prénom</label>
                <input <?php if($GLOBALS["register_input_firstname_haserror"] == true || $GLOBALS["register_input_firstname_pattern_haserror"] == true){echo "autofocus ";}?>required name="register_input_firstname" type="text" class="form-control input-lg" pattern=".{2,255}" id="register_input_firstname" placeholder="Prénom" />
              </div>
            </div>
            <div class="<?php if($GLOBALS["register_input_lastname_haserror"] == true || $GLOBALS["register_input_lastname_pattern_haserror"] == true){echo "has-error ";}?>form-group">
              <div class="input-group">
                <label class="sr-only" for="register_input_lastname">Nom</label>
                <input <?php if($GLOBALS["register_input_lastname_haserror"] == true || $GLOBALS["register_input_lastname_pattern_haserror"] == true){echo "autofocus ";}?>required name="register_input_lastname" type="text" class="form-control input-lg" pattern=".{2,255}" id="register_input_lastname" placeholder="Nom" />
                <span class="input-group-btn">
                  <button onclick="$('#register_input_lastname').val('');$('#register_input_firstname').val('');" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-remove-circle"></span></button>
                </span>
              </div>
            </div>
            <span class="<?php if($GLOBALS["register_input_firstname_pattern_haserror"] == true || $GLOBALS["register_input_lastname_pattern_haserror"] == true){echo "pattern-error ";}?>help-block">Les noms et prénoms de l'utilisateur sont utilisés dans l'interface graphique (notamment dans les menus, les calendriers...) et dans les e-mailings à des fins de personnalisation.<br>CONSEIL : Cliquez sur l'éclair pour remplir automatiquement les champs si l'identifiant est au format prenom.nom.<br>Entre 2 et 255 caractères.</span>
          </div>
          <div class="<?php if($GLOBALS["register_input_email_haserror"] == true || $GLOBALS["register_input_email_pattern_haserror"] == true){echo "has-error ";}?>form-group">
            <label for="register_input_email">Adresse e-mail (obligatoire)</label>
            <div class="input-group">
              <span class="input-group-btn">
                <button onclick="$('#register_input_email').val($('#register_input_username').val()+'@')" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-flash"></span></button>
              </span>
              <input <?php if($GLOBALS["register_input_email_haserror"] == true || $GLOBALS["register_input_email_pattern_haserror"] == true){echo "autofocus ";}?>required id="register_input_email" type="email" class="form-control input-lg" name="register_input_email" placeholder="Cliquez sur l'éclair pour insérer l'identifiant" />
              <span class="input-group-btn">
                <button onclick="$('#register_input_email').val('')" class="btn btn-default btn-lg" type="button"><span class="glyphicon glyphicon-remove-circle"></span></button>
              </span>
            </div>
            <span class="<?php if($GLOBALS["register_input_email_pattern_haserror"] == true){echo "pattern-error ";}?>help-block">Entrez une adresse e-mail au format correct.<br>CONSEIL : cliquez sur l'éclair pour insérer dans le champ l'identifiant de l'utilisateur, au cas où ce serait le même pour son adresse e-mail.<br>L'utilisateur doit pouvoir consulter les e-mails importants (à propos de ses congés notamment) qui seront envoyés à cette adresse.</span>
          </div>
          <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
            <div class="panel panel-default">
              <div class="panel-heading" role="tab" id="headingOne">
                <h4 class="panel-title">
                  <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne" class="collapsed">Autres paramètres qui seront réglés sur les valeurs par défaut sans modification de votre part (cliquez pour déplier)</a>
                </h4>
              </div>
              <div id="collapseOne" class="panel-collapse collapse<?php if($GLOBALS["register_input_days_pattern_haserror"] == true || $GLOBALS["register_input_defaulttab_haserror"] == true){echo ' in';}?>" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">
                  <?php if(in_array("manager",$_SESSION['user_role'])): ?>

                  <div class="form-group" id="register_input_days_formgroup">
                    <label for="register_input_days">Solde de congés disponible au départ (seulement pour les employés, par défaut 25)</label>
                    <div class="<?php if($GLOBALS["register_input_days_pattern_haserror"] == true){echo "has-error ";}?>input-group">
                      <input <?php if($GLOBALS["register_input_days_pattern_haserror"] == true){echo "autofocus ";}?>id="register_input_days" type="number" class="form-control" name="register_input_days" min="0" value="25" />
                      <span class="input-group-btn">
                        <button onclick="$('#register_input_days').val('25')" class="btn btn-default" type="button"><span class="glyphicon glyphicon-remove-circle"></span></button>
                      </span>
                    </div>
                    <span class="<?php if($GLOBALS["register_input_days_pattern_haserror"] == true){echo "pattern-error ";}?>help-block">Détermine le solde de congés qu'aura à disposition l'employé dès la création de son compte.<br>L'employé pourra demander un nombre de jours de congés égal à son solde restant.</span>
                  </div>
  <?php endif; ?>

                  <div class="form-group">
                    <label for="register_input_isFirstLogin">
                    <input type="checkbox" id="register_input_isFirstLogin" name="register_input_isFirstLogin" checked />
                      Activer le didacticiel au premier démarrage (activé par défaut)</label>
                    <span class="help-block">Lancer ou non le didacticiel de découverte de l'application dès la première connexion de l'utilisateur.<br>L'utilisateur pourra choisir d'ignorer ce didacticiel mais il lui sera proposé d'office.<br>Activé par défaut.</span>
                  </div>
                  <div class="form-group">
                    <label for="register_input_isEnabled" class="important-field">
                    <input type="checkbox" id="register_input_isEnabled" name="register_input_isEnabled" checked />
                    IMPORTANT - Activer le compte (activé par défaut)</label>
                    <span class="help-block important-field">Ce paramètre est important car si vous décochez la case, le compte sera créé mais l'utilisateur ne pourra pas se connecter à son compte tant que vous n'aurez pas activé manuellement le compte dans le <a href="index.php?action=manageusers&quickAction=enableaccount">panneau de gestion des utilisateurs</a>.<br>Vous pouvez également activer d'office le compte mais le désactiver après (ex: vacances, sanction envers l'utilisateur, etc...) dans le même panneau de gestion.<br>Activé par défaut.</span>
                  </div>
                  <div id="register_input_defaulttab_formgroup" class="<?php if($GLOBALS["register_input_defaulttab_haserror"] == true){echo "has-error ";}?>form-group">
            <?php if(in_array("manager",$_SESSION['user_role']) && in_array("sysadmin",$_SESSION['user_role'])): ?>

                    <label for="register_input_defaulttab">Onglet par défaut (par défaut "Gérer les congés" pour les gestionnaires et "Demander mes congés" pour les employés)</label>
                    <select disabled <?php if($GLOBALS["register_input_defaulttab_haserror"] == true){echo "autofocus ";}?>required id="register_input_defaulttab" class="form-control input-lg" name="register_input_defaulttab">
                      <optgroup id="worker-tabs" label="Employés">
                        <option id="preferredtab-worker" selected value="vacationask">Demander mes congés</option>
                      </optgroup>
                      <optgroup id="manager-tabs" label="Gestionnaires" disabled>
                        <option id="preferredtab-manager" value="vacationmanage">Gérer les congés</option>
                        <option value="manageusers">Gérer les employés</option>
                        <option value="registerusers">Ajouter des employés</option>
                      </optgroup>
                    </select>
                    <span class="<?php if($GLOBALS["register_input_defaulttab_haserror"] == true){echo "pattern-error ";}?>help-block">Choisissez l'onglet par défaut de l'utilisateur, c'est-à-dire l'onglet qui sera affiché après connexion complète de l'utilisateur (sauf si un onglet particulier est demandé dans la requête).<br><a style="pattern-error" onclick="$('#register_input_role').focus()">Si l'utilisateur est un gestionnaire et employé (cliquez pour changer son rôle)</a>, il peut sélectionner l'onglet "Demander mes congés".</span>
            <?php elseif(in_array("sysadmin",$_SESSION['user_role'])): ?>
  
                    <label for="register_input_defaulttab">Onglet par défaut (par défaut "Gérer les congés")</label>
                    <select <?php if($GLOBALS["register_input_defaulttab_haserror"] == true){echo "autofocus ";}?>required id="register_input_defaulttab" class="form-control input-lg" name="register_input_defaulttab">
                      <optgroup label="Gestionnaires">
                        <option selected value="vacationmanage">Gérer les congés</option>
                        <option value="manageusers">Gérer les employés</option>
                        <option value="registerusers">Ajouter des employés</option>
                      </optgroup>
                    </select>
                    <span class="<?php if($GLOBALS["register_input_defaulttab_haserror"] == true){echo "pattern-error ";}?>help-block">Choisissez l'onglet par défaut de l'utilisateur, c'est-à-dire l'onglet qui sera affiché après connexion complète de l'utilisateur (sauf si un onglet particulier est demandé dans la requête).<br>Si vous étiez également un gestionnaire <a id="openUserSettingsMenu" href="#">(devenez gestionnaire dans vos Paramètres utilisateur en cliquant ici)</a>, vous pourriez créer un gestionnaire et employé et  ainsi sélectionner l'onglet "Demander mes congés".</span>
                    <?php elseif(in_array("manager",$_SESSION['user_role'])): ?>
  
                    <label for="register_input_defaulttab">Onglet par défaut (obligatoirement "Demander mes congés" pour les employés)</label>
                    <select disabled <?php if($GLOBALS["register_input_defaulttab_haserror"] == true){echo "autofocus ";}?>required id="register_input_defaulttab" class="form-control input-lg" name="register_input_defaulttab">
                      <optgroup label="Employés">
                        <option selected value="vacationask">Demander mes congés</option>
                      </optgroup>
                    </select>
                    <span class="<?php if($GLOBALS["register_input_defaulttab_haserror"] == true){echo "pattern-error ";}?>help-block">Choisissez l'onglet par défaut de l'utilisateur, c'est-à-dire l'onglet qui sera affiché après connexion complète de l'utilisateur (sauf si un onglet particulier est demandé dans la requête).<br>NOTE : Vu que vous êtes gestionnaire, vous ne pouvez créer que des employés, et ceux-ci ne peuvent accéder qu'à l'onglet "Demander mes congés", c'est pour cela que l'option n'est pas modifiable.</span>
  <?php endif; ?>

          </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row form-group">
            <input type="submit" name="register_input_submit" value="Créer" id="register_input_submit" class="btn-lg col-xs-4 col-xs-offset-1 btn btn-primary" />
			<!-- Ici, big bogue : quand on reset le formulaire, bah le select de l'onglet par défaut n'est pas réinitialisé et reste sur une option sélectionnée (et des options désactivées) complètement incompatibles avec le nouveau type de compte. Workaround : on actualise la page -->
            <button name="register_input_reset" id="register_input_reset" onclick="location.reload()" class="btn-lg col-xs-4 col-xs-offset-2 btn btn-danger">Effacer</button>
          </div>
        </form>
      </section>
  <?php

  }

	private function setDefaultHeaders() {
    $GLOBALS["content_generator"] = "vacationPanelApp";
    $GLOBALS["html_stylesheets"][] = "assets/stylesheets/vacationpanel.css";
    $GLOBALS["html_scripts_imported"][] = "assets/javascript/animations.js";
    $GLOBALS["html_scripts_imported"][] = "assets/javascript/ui.js";
    $GLOBALS["html_scripts_imported"][] = "assets/javascript/controller.js";
	}

  private function buildTabsNav() { // Vérifie les grades de l'utilisateur ainsi que le générateur de contenu actuel afin de générer les onglets du header
    $tabs_list = array();

    $vacationAsk_isSelected=false;
    $vacationManage_isSelected=false;
    $manageUsers_isSelected=false;
    $registerUsers_isSelected=false;
    $systemSettings_isSelected=false;

    // On vérifie quel générateur de contenu est actif
    switch($this->content_mode) {
      case "vacationAsk":
        $vacationAsk_isSelected = true;
      break;

      case "vacationManage":
        $vacationManage_isSelected = true;
      break;

      case "manageUsers":
        $manageUsers_isSelected = true;
      break;

      case "registerUsers":
        $registerUsers_isSelected = true;
      break;

      case "systemSettings":
        $systemSettings_isSelected = true;
      break;
    }

    if(in_array("worker",$_SESSION['user_role'])) {
      // L'utilisateur est un worker, on initialise ses onglets
      $tabs_list[] = array("Demander mes congés","vacationask",$vacationAsk_isSelected);
    }

    if(in_array("manager",$_SESSION['user_role'])) {
      // L'utilisateur est un manager, on initialise ses onglets
      $tabs_list[] = array("Gérer les congés","vacationmanage",$vacationManage_isSelected);
      $tabs_list[] = array("Gérer les employés","manageusers",$manageUsers_isSelected);
      $tabs_list[] = array("Ajouter des employés","registerusers",$registerUsers_isSelected);
    }

    if(in_array("sysadmin",$_SESSION['user_role'])) {
      // L'utilisateur est un sysadmin, on initialise ses onglets
      $tabs_list[] = array("Gérer les gestionnaires","manageusers",$manageUsers_isSelected);
      $tabs_list[] = array("Ajouter des gestionnaires","registerusers",$registerUsers_isSelected);
      $tabs_list[] = array("Paramètres système","systemsettings",$systemSettings_isSelected);
    }

    // On écrit les onglets en HTML
    if(!empty($tabs_list)): ?>
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav nav-tabs" id="content-mode-tabs-nav" role="navigation">
                <li class="disabled"><a href="#" id="tab-help-label">Navigation&nbsp;:&nbsp;</a></li>
                <?php foreach($tabs_list as $temp_tabnav_data): ?>
                <li role="presentation"<?php if($temp_tabnav_data[2]): ?> class="active"<?php endif; ?>><a href="<?=$_SERVER['SCRIPT_NAME']."?action=".$temp_tabnav_data[1] ?>"><?=$temp_tabnav_data[0] ?></a></li>
  <?php endforeach; ?>

              </ul>
            </div>
  <?php endif;
  }
}

?>