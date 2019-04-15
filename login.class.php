<?php
mb_internal_encoding('UTF-8');
mb_http_output("UTF-8");
/**
 * Class OneFileLoginApplication
 *
 * An entire php application with user registration, login and logout in one file.
 * Uses very modern password hashing via the PHP 5.5 password hashing functions.
 * This project includes a compatibility file to make these functions available in PHP 5.3.7+ and PHP 5.4+.
 *
 * @author Panique
 * @link https://github.com/panique/php-login-one-file/
 * @license http://opensource.org/licenses/MIT MIT License
 */
class OneFileLoginApplication
{
    /**
     * @var string Type of used database 
     */
    private $db_type = "mysql"; // TODO FILL YOUR INFORMATIONS HERE
    private $db_mysql_host = ""; // TODO FILL YOUR INFORMATIONS HERE
    private $db_mysql_dbname = ""; // TODO FILL YOUR INFORMATIONS HERE
    private $db_mysql_username = ""; // TODO FILL YOUR INFORMATIONS HERE
    private $db_mysql_password = ""; // TODO FILL YOUR INFORMATIONS HERE

    private $isXHR = false; // Indique si la requête demandait une réponse en JSON, donc sans autre processus de traitement

    private $content_mode; // Garde en mémoire la fonction qui sera lancée pour l'affichage

    private $user_isFirstLogin;
    private $newuser_role = "worker";
    private $newuser_defaulttab = "vacationask";

    /**
     * @var string Path of the database 
     */
    /**
     * @var object Database connection
     */
    private $db_connection;

    /**
     * @var bool Login status of user
     */
    private $user_is_logged_in = false;

    /**
     * @var string System messages, likes errors, notices, etc.
     */


    /**
     * Does necessary checks for PHP version and PHP password compatibility library and runs the application
     */
    public function __construct()
    {
        require_once("libraries/password_compatibility_library.php");
    }

    /**
     * Performs a check for minimum requirements to run this application.
     * Does not run the further application when PHP version is lower than 5.3.7
     * Does include the PHP password compatibility library when PHP version lower than 5.5.0
     * (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
     * @return bool Success status of minimum requirements check, default is false
     */

    /**
     * This is basically the controller that handles the entire flow of the application.
     * @return bool Launch main application
     */
    public function runLoginSystem()
    {
        // check for possible user interactions (login with session/post data or logout)
        $this->performUserLoginAction();
        // show "page", according to user's login status
        if ($this->isXHR) {
          return false;
        } elseif ($this->getUserLoginStatus()) {
          return true;
        } else {
          $this->setHeadersPageLoginForm();
          return false;
        }
    }

    public function writeContent()
    {
        if(isset($this->content_mode)) {
            switch($this->content_mode)
            {
                case "LoginForm":
                    $this->showPageLoginForm();
                break;
            }
        }
    }
    /**
     * Creates a PDO database connection (in this case to a SQLite flat-file database)
     * @return bool Database creation success status, false by default
     */
    private function createDatabaseConnection()
    {
        try {
            $this->db_connection = new PDO($this->db_type.':'."host=".$this->db_mysql_host.";dbname=".$this->db_mysql_dbname.";charset=utf8",$this->db_mysql_username,$this->db_mysql_password);
            return true;
        } catch (PDOException $e) {
            pushNotification("banner_error","ERREUR FATALE - Le serveur rencontre actuellement des problèmes, veuillez réessayer ultérieurement. Code d'erreur pour le support technique : <pre>PDO database connection problem: " . $e->getMessage() . "</pre>");
        } catch (Exception $e) {
            pushNotification("banner_error","ERREUR FATALE - Le système de connexion a rencontré une erreur inconnue. Code d'erreur pour le support technique : <pre>General problem: " . $e->getMessage()."</pre>");
        }
        return false;
    }

    /**
     * Set a marker (NOTE: is this method necessary ?)
     * @return bool
     */
    private function doLoginWithSessionData()
    {
        if($this->checkSessionTimestampAfterRelogDBTimestamp()) {
            $this->user_is_logged_in = true; // ?
        } else {
            $this->user_is_logged_in = false;
        }
    }

    /**
     * Handles the flow of the login/logout process. According to the circumstances, a logout, a login with session
     * data or a login with post data will be performed
     */
    private function performUserLoginAction()
    {
        if (isset($_GET["action"]) && $_GET["action"] == "logout") {
            $this->doLogout();
        } elseif (!empty($_SESSION['user_name']) && ($_SESSION['user_is_logged_in'])) {
            $this->doLoginWithSessionData();
            if($this->user_is_logged_in==false){$this->doLogout("ERREUR - Vous avez été déconnecté car vos informations d'identification ont été modifiées depuis votre dernière connexion.<br>Contactez votre gestionnaire pour plus d'informations");}
            if(isset($_GET['action']) && $_GET['action'] == "xhr_checkUsernameAvailability" && isset($_GET['username'])) {
                $this->isXHR = true;
                $this->xhrCheckUsernameAvailability();
            }
            if(isset($_GET['action']) && $_GET['action'] == "xhr_getColleagueVacations" && isset($_GET['targetDay'])) {
                $this->isXHR = true;
                $this->xhrGetColleagueVacations();
            }
        } elseif (isset($_POST["login_input_username"])) {
            $this->doLoginWithPostData();
        }
    }

    /**
     * Process flow of login with POST data
     */
    private function doLoginWithPostData()
    {
        if ($this->checkLoginFormDataNotEmpty()) {
            if ($this->createDatabaseConnection()) {
                $this->checkPasswordCorrectnessAndLogin();
            }
        }
    }

    /**
     * Logs the user out
     */
    private function doLogout($reason="Vous avez été déconnecté du Gestionnaire de congés.<br>Vous ne serez plus automatiquement reconnecté lors de votre prochaine visite.<br>Vos cookies ont été effacés et vos données sont en sécurité.")
    {
        $_SESSION = array();
        session_destroy();
        $this->user_is_logged_in = false;
        pushNotification("banner_warning",$reason);
    }

    /**
     * The registration flow
     * @return bool
     */
    public function doRegistration()
    {
        if ($this->checkRegistrationData()) {
            if ($this->createDatabaseConnection()) {
                if ($this->createNewUser()) {
                  return true;
                } else {
                  return false;
                }
            }
        }
        // default return
        return false;
    }

    /**
     * Créé par François
     * Vérifie si le compte a été modifié dans la base depuis la dernière connexion POST
     * Appelé à chaque connexion SESSION
     * @return bool isSessionTimestampAfterRelog_TimeInDB
     */
    private function checkSessionTimestampAfterRelogDBTimestamp()
    {
        $lastLoginToken = $_SESSION['login_token'];
        $this->createDatabaseConnection();
        $sql = 'SELECT user, relog_time FROM dims_mod_vacationpanel_users WHERE user = :user_name AND relog_time <= :lastLoginToken LIMIT 1';
        $query = $this->db_connection->prepare($sql);
        $query->bindValue(':user_name', $_SESSION['user_name']);
        $query->bindValue(':lastLoginToken', $lastLoginToken);
        $query->execute();

        if($query->fetchObject()) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Validates the login form data, checks if username and password are provided
     * @return bool Login form data check success state
     */
    private function checkLoginFormDataNotEmpty()
    {
        if (!empty($_POST['login_input_username']) && !empty($_POST['login_input_password'])) {
            return true;
        } elseif (empty($_POST['login_input_username'])) {
            pushNotification("banner_warning","Veuillez entrer votre identifiant pour vous connecter.");
        } elseif (empty($_POST['login_input_password'])) {
            pushNotification("banner_warning","Veuillez entrer le mot de passe pour cet identifiant pour vous connecter.");
        }
        // default return
        return false;
    }

    /**
     * Checks if user exits, if so: check if provided password matches the one in the database
     * @return bool User login success status
     */
    private function checkPasswordCorrectnessAndLogin()
    {
        $sql = 'SELECT user, password, days, email, relog_time, defaultTab, lastname, firstname, role, isEnabled, isFirstLogin
                FROM dims_mod_vacationpanel_users
                WHERE user = :user_name
                LIMIT 1';
        $query = $this->db_connection->prepare($sql);
        $query->bindValue(':user_name', $_POST['login_input_username']);
        $query->execute();

        // Btw that's the weird way to get num_rows in PDO with SQLite:
        // if (count($query->fetchAll(PDO::FETCH_NUM)) == 1) {
        // Holy! But that's how it is. $result->numRows() works with SQLite pure, but not with SQLite PDO.
        // This is so crappy, but that's how PDO works.
        // As there is no numRows() in SQLite/PDO (!!) we have to do it this way:
        // If you meet the inventor of PDO, punch him. Seriously.
        $result_row = $query->fetchObject();
        if ($result_row) {
            // using PHP 5.5's password_verify() function to check password
            if (password_verify($_POST['login_input_password'], $result_row->password)) {
                if($result_row->isEnabled==1) {
                    $_SESSION['user_role'] = explode(';',$result_row->role);

                    if($_SESSION['user_role'][0] != "") { // On s'assure que l'utilisateur possède au moins un rôle, sinon il est invalide
                        // write user data into PHP SESSION [a file on your server]
                        $_SESSION['user_name'] = $result_row->user;
                        $_SESSION['login_token'] = date('Y-m-d H:i:s');
                        $_SESSION['user_lastname'] = $result_row->lastname;
                        $_SESSION['user_firstname'] = $result_row->firstname;
                        $_SESSION['user_isFirstLogin'] = $result_row->isFirstLogin;
                        $_SESSION['user_days'] = $result_row->days;
                        $_SESSION['user_email'] = $result_row->email;
                        $_SESSION['user_relogtime'] = $result_row->relog_time;
                        $_SESSION['user_defaultTab'] = $result_row->defaultTab;
                        $_SESSION['user_is_logged_in'] = true;
                        $this->user_is_logged_in = true;
                        pushNotification("banner_success","Bonjour ".$_SESSION['user_firstname'].", vous êtes désormais connecté.");
                        
                        return true;
                    } else {
                        session_destroy();
                        pushNotification("banner_error","ERREUR - Votre compte existe mais il n'a le droit d'utiliser aucun module de l'application. Il est donc invalide.<br>Veuillez contacter le support technique");
                        return false;
                    }
                } else {
                    pushNotification("banner_error","ERREUR - Votre compte est désactivé. Veuillez réessayer dans quelques heures, il est peut-être en cours de maintenance.<br>Pour plus d'informations, contactez votre gestionnaire.");
                }               
            } else {
                pushNotification("banner_warning","L'identifiant ou le mot de passe est incorrect. Vérifiez les valeurs entrées et réessayez.");
            }
        } else {
            pushNotification("banner_warning","L'identifiant ou le mot de passe est incorrect. Vérifiez les valeurs entrées et réessayez.");
        }
        // default return
        return false;
    }

    /**
     * Validates the user's registration input
     * @return bool Success status of user's registration data validation
     */
    private function checkRegistrationData()
    {
        mb_internal_encoding('UTF-8');
        // if no registration form submitted: exit the method
        if (!isset($_POST["register_input_submit"])) {
            pushNotification("banner_error","ERREUR - Le système d'inscription a rencontré une erreur inattendue (données reçues mais pas de demande d'inscription).<br>Actualisez la page en pressant la touche <kbd>F5</kbd> et réessayez.<br>Si le problème persiste réinstallez les fichiers du Gestionnaire de congés.");
            return false;
        }

        if(isset($_POST['register_input_role'])) {
            $newuser_role = $_POST['register_input_role'];
        }

        if(isset($_POST['register_input_defaulttab'])) {
            $newuser_defaulttab = $_POST['register_input_defaulttab'];
        }

        if(in_array("manager",$_SESSION['user_role']) && in_array("sysadmin",$_SESSION['user_role'])) { // On définit les rôles et onglets par défaut possibles des nouveaux utilisateurs créés par l'utilisateur logué en fonction de son propre rôle
          $userrole_tutored = array("manager","worker","manager-worker");
          $defaulttab_tutored = array("vacationask","vacationmanage","manageusers","registerusers");
          $userrole_string = "gestionnaire et administrateur système";
          $userstutored_string = "employés ou des gestionnaires";
        } elseif(in_array("sysadmin",$_SESSION['user_role'])) {
          $defaulttab_tutored = array("vacationmanage","manageusers","registerusers");
          $userrole_tutored = array("manager");
          $newuser_role = "manager";
          $userrole_string = "administrateur système";
          $userstutored_string = "gestionnaires";
        } elseif(in_array("manager",$_SESSION['user_role'])) {
          $defaulttab_tutored = array("vacationask");
          $userrole_tutored = array("worker");
          $newuser_defaulttab = "vacationask";
          $newuser_role = "worker";
          $userrole_string = "gestionnaire";
          $userstutored_string = "employés";
        }


        if(isset($newuser_role) && in_array($newuser_role, array("worker","manager-worker"))) {
          if(!empty($_POST['register_input_days']) && preg_match('/^[0-9]{0,}$/', $_POST['register_input_days'])) {
            $isDayInputCorrect = true;
          } else {
            $isDayInputCorrect = false;
          }
        } else {
          $isDayInputCorrect = true;
        }

        if(!isset($newuser_defaulttab) && $newuser_role == "worker") {
          $newuser_defaulttab = "vacationask"; 
        }
        $GLOBALS["register_input_haserror"] = true;
        // validating the input
        if (!empty($_POST['register_input_username'])
            && mb_strlen($_POST['register_input_username']) <= 64
            && mb_strlen($_POST['register_input_username']) >= 2
            && preg_match('/^[a-z0-9\.]{2,64}$/', $_POST['register_input_username'])
            && isset($newuser_role)
            && in_array($newuser_role, $userrole_tutored)
            && isset($newuser_defaulttab)
            && in_array($newuser_defaulttab, $defaulttab_tutored)
            && !empty($_POST['register_input_firstname'])
            && preg_match('/.{2,255}/', $_POST['register_input_firstname'])
            && !empty($_POST['register_input_lastname'])
            && preg_match('/.{2,255}/', $_POST['register_input_lastname'])
            && $isDayInputCorrect
            && !empty($_POST['register_input_email'])
            && filter_var($_POST['register_input_email'], FILTER_VALIDATE_EMAIL)
            && !empty($_POST['register_input_password_new'])
            && !empty($_POST['register_input_password_repeat'])
            && ($_POST['register_input_password_new'] === $_POST['register_input_password_repeat'])
        ) {
            // only this case return true, only this case is valid
            $GLOBALS["register_input_haserror"] = false;
            $this->newuser_role=$newuser_role;
            $this->newuser_defaulttab=$newuser_defaulttab;
            return true;
        } elseif (empty($_POST['register_input_username'])) {
            pushNotification("banner_warning","Veuillez entrer un identifiant comprenant entre 2 et 64 lettres de a à z ou nombres pour ce nouveau compte.<br>REMARQUE : Votre navigateur web ne semble pas supporter le HTML5, veuillez le mettre à jour pour une navigation optimale.");
            $GLOBALS["register_input_username_haserror"] = true;
        } elseif (!preg_match('/^[a-z0-9\.]{2,64}$/', $_POST['register_input_username'])) {
            pushNotification("banner_warning","Veuillez entrer un identifiant comprenant entre 2 et 64 lettres de a à z ou nombres ou points \".\" pour ce nouveau compte.<br>REMARQUE : Votre navigateur web ne semble pas supporter le HTML5, veuillez le mettre à jour pour une navigation optimale.");
            $GLOBALS["register_input_username_haserror"] = true;
            $GLOBALS["register_input_username_pattern_haserror"] = true;
        } elseif (mb_strlen($_POST['register_input_username']) > 64 || mb_strlen($_POST['register_input_username']) < 2) {
            pushNotification("banner_warning","L'identifiant entré est trop long ou trop court.<br>Veuillez entrer un identifiant comprenant entre 2 et 64 lettres de a à z ou nombres ou points \".\" pour ce nouveau compte.<br>REMARQUE : Votre navigateur web ne semble pas supporter le HTML5, veuillez le mettre à jour pour une navigation optimale.");
            $GLOBALS["register_input_username_haserror"] = true;
            $GLOBALS["register_input_username_pattern_haserror"] = true;
        } elseif (empty($_POST['register_input_password_new']) || empty($_POST['register_input_password_repeat'])) {
            pushNotification("banner_warning","Pour des raisons de sécurité, chaque compte doit être protégé par un mot de passe d'au moins six caractères.<br>Veuillez entrer un même mot de passe pour ce nouveau compte dans les champs \"Mot de passe\" et \"Mot de passe (répétez)\".<br>REMARQUE : Votre navigateur web ne semble pas supporter le HTML5, veuillez le mettre à jour pour une navigation optimale.");
            if(empty($_POST['register_input_password_new'])) {
              $GLOBALS["register_input_passwordnew_haserror"] = true;
            }
            if(empty($_POST['register_input_password_repeat'])) {
              $GLOBALS["register_input_passwordrepeat_haserror"] = true;
            }
        } elseif ($_POST['register_input_password_new'] !== $_POST['register_input_password_repeat']) {
            pushNotification("banner_warning","Vous avez entré des mots de passe différents dans les cases \"Mot de passe\" et \"Répétez le mot de passe\".<br>Veuillez réessayer et entrer le même mot de passe dans les deux cases afin de prouver que vous vous souviendrez du mot de passe.<br>REMARQUE : Vous ne semblez pas avoir activé le JavaScript dans votre navigateur, activez cette option pour une navigation optimale.");
            $GLOBALS["register_input_password_mismatch"] = true;
        } elseif (mb_strlen($_POST['register_input_password_new']) < 6) {
            pushNotification("banner_warning","Le mot de passe entré est trop court.<br>Pour des raisons de sécurité, chaque compte doit être protégé par un mot de passe d'au moins six caractères.<br>Veuillez entrer un mot de passe d'au moins six caractères pour ce nouveau compte.<br>REMARQUE : Votre navigateur web ne semble pas supporter le HTML5, veuillez le mettre à jour pour une navigation optimale.");
            $GLOBALS["register_input_password_pattern_haserror"] = true;
        } elseif (empty($newuser_role)) {
            pushNotification("banner_warning","Veuillez choisir un type de compte pour ce nouvel utilisateur.<br>Nous vous rappelons que vous DEVEZ accéder à l'application grâce à une configuration légitime.");
            $GLOBALS["register_input_role_haserror"] = true;
        } elseif (!in_array($newuser_role, $userrole_tutored)) {
            pushNotification("banner_error","ERREUR - Vous avez tenté de créer un compte d'utilisateur ayant un type dont vous n'avez pas la permission d'utiliser.<br>Nous vous rappelons que vous DEVEZ accéder à l'application grâce à une configuration légitime.");
            $GLOBALS["register_input_role_haserror"] = true;
        } elseif (!isset($newuser_defaulttab)) {
            pushNotification("banner_warning","Veuillez choisir un onglet par défaut pour ce nouvel utilisateur.<br>Nous vous rappelons que vous DEVEZ accéder à l'application grâce à une configuration légitime.");
            $GLOBALS["register_input_defaulttab_haserror"] = true;
        } elseif (!in_array($newuser_defaulttab, $defaulttab_tutored)) {
            pushNotification("banner_error","ERREUR - Vous avez tenté de créer un compte d'utilisateur ayant un onglet par défaut auquel il n'a pas le droit d'accéder.<br>Nous vous rappelons que vous DEVEZ accéder à l'application grâce à une configuration légitime.");
            $GLOBALS["register_input_defaulttab_haserror"] = true;
        } elseif (empty($_POST['register_input_firstname']) || !preg_match('/.{2,255}/', $_POST['register_input_lastname']) || !preg_match('/.{2,255}/', $_POST['register_input_firstname']) || empty($_POST['register_input_lastname'])) {
            pushNotification("banner_warning","Veuillez entrer le nom et le prénom de l'utilisateur (comprenant chacun entre 2 et 255 caractères).<br>Les noms et prénoms de l'utilisateur sont utilisés dans l'interface graphique (notamment dans les menus, les calendriers...) et dans les e-mailings à des fins de personnalisation.<br>REMARQUE : Votre navigateur web ne semble pas supporter le HTML5, veuillez le mettre à jour pour une navigation optimale.");
            if(empty($_POST['register_input_firstname'])) {
              $GLOBALS["register_input_firstname_haserror"] = true;
            }
            if(!preg_match('/.{2,255}/', $_POST['register_input_firstname'])) {
              $GLOBALS["register_input_firstname_pattern_haserror"] = true;
            }
            if(empty($_POST['register_input_lastname'])) {
              $GLOBALS["register_input_lastname_haserror"] = true;
            }
            if(!preg_match('/.{2,255}/', $_POST['register_input_lastname'])) {
              $GLOBALS["register_input_lastname_pattern_haserror"] = true;
            }
        } elseif (empty($_POST['register_input_email'])) {
            pushNotification("banner_warning","Veuillez entrer une adresse e-mail pour ce nouveau compte, afin que l'utilisateur puisse recevoir des notifications importantes du logiciel.<br>REMARQUE : Votre navigateur web ne semble pas supporter le HTML5, veuillez le mettre à jour pour une navigation optimale.");
            $GLOBALS["register_input_email_haserror"] = true;
        } elseif (!filter_var($_POST['register_input_email'], FILTER_VALIDATE_EMAIL)) {
            pushNotification("banner_warning","L'adresse e-mail entrée ne semble pas être correcte... Vérifiez votre saisie.<br>REMARQUE : Votre navigateur web ne semble pas supporter le HTML5, veuillez le mettre à jour pour une navigation optimale.");
            $GLOBALS["register_input_email_pattern_haserror"] = true;
        } elseif ($isDayInputCorrect == false) {
            pushNotification("banner_warning","Veuillez entrer un solde de congés valide, c'est-à-dire un nombre positif.<br>REMARQUE : Votre navigateur web ne semble pas supporter le HTML5, veuillez le mettre à jour pour une navigation optimale.");
            $GLOBALS["register_input_days_pattern_haserror"] = true;
        } else {
            pushNotification("banner_warning","ERREUR - Le système d'inscription a rencontré une erreur inconnue.<br>Veuillez réessayer. Si le problème persiste réinstallez les fichiers du Gestionnaire de congés.");
        }
        // default return
        return false;
    }

    /**
     * Creates a new user.
     * @return bool Success status of user registration
     */
    private function createNewUser()
    {

      /**
       * Récupération et formatage final des données
       */

        // remove html code etc. from input
        $user = htmlentities($_POST['register_input_username'], ENT_QUOTES, "UTF-8");
        $password = $_POST['register_input_password_new'];
        // crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 char hash string.
        // the constant PASSWORD_DEFAULT comes from PHP 5.5 or the password_compatibility_library
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        if($this->newuser_role=="manager-worker") {
          $role = "worker;manager";
          $days = intval($_POST['register_input_days']);
        } elseif($this->newuser_role=="worker") {
          $role = "worker";
          $days = intval($_POST['register_input_days']);
        } else {
          $role = "manager";
          $days = 0;
        }
        
        $lastname = htmlentities($_POST['register_input_lastname'], ENT_QUOTES, "UTF-8");
        $firstname = htmlentities($_POST['register_input_firstname'], ENT_QUOTES, "UTF-8");
        $email = htmlentities($_POST['register_input_email'], ENT_QUOTES, "UTF-8");

        if(isset($_POST['register_input_isFirstLogin']) && $_POST['register_input_isFirstLogin'] == "on") {
          $isFirstLogin = 1;
        } else {
          $isFirstLogin = 0;
        }

        if(isset($_POST['register_input_isEnabled']) && $_POST['register_input_isEnabled'] == "on") {
          $isEnabled = 1;
        } else {
          $isEnabled = 0;
        }

        $register_sql_timestamp = date('Y-m-d H:i:s');
        $defaultTab = $this->newuser_defaulttab;

        /**
         * On vérifie si le compte à créer n'existe pas déjà
         */
        $this->db_connection->exec("SET NAMES 'utf8'");

        $sql = 'SELECT * FROM dims_mod_vacationpanel_users WHERE user = :user';
        $query = $this->db_connection->prepare($sql);
        $query->bindValue(':user', $user);
        $query->execute();

        // As there is no numRows() in SQLite/PDO (!!) we have to do it this way:
        // If you meet the inventor of PDO, punch him. Seriously.
        $result_row = $query->fetchObject();
        if ($result_row) {
            pushNotification("banner_error","ERREUR - Oops, il semblerait qu\'un utilisateur ayant le même identifiant existe déjà sur ce serveur...<br>Si vous cherchez à accéder à ce compte, vous pouvez facilement <a href=\"index.php?action=manageusers&quickAction=changepassword&username=".$user."\" class=\"alert-link\">modifier le mot de passe</a> ou <a href=\"index.php?action=manageusers&quickAction=removeuser&username=".$user."\" class=\"alert-link\">supprimer</a> ce compte si vous n'en avez plus besoin.");
            $GLOBALS["register_input_username_haserror"] = true;
        } else {
            $this->db_connection->exec("SET NAMES 'utf8'");
            $sql = "INSERT INTO dims_mod_vacationpanel_users
            (user, password, role, days, lastname, firstname, email, isFirstLogin, isEnabled, relog_time, defaultTab)
            VALUES(:user, :password_hash, :role, :days, :lastname, :firstname, :email, :isFirstLogin, :isEnabled, :time, :defaultTab)";
            $query = $this->db_connection->prepare($sql);
            $query->bindValue(':user', $user);
            $query->bindValue(':password_hash', $password_hash);
            $query->bindValue(':role', $role);
            $query->bindValue(':days', $days);
            $query->bindValue(':lastname', $lastname);
            $query->bindValue(':firstname', $firstname);
            $query->bindValue(':email', $email);
            $query->bindValue(':isFirstLogin', $isFirstLogin);
            $query->bindValue(':isEnabled', $isEnabled);
            $query->bindValue(':time', $register_sql_timestamp);
            $query->bindValue(':defaultTab', $defaultTab);
                // PDO's execute() gives back TRUE when successful, FALSE when not
                // @link http://stackoverflow.com/q/1661863/1114320
            $registration_success_state = $query->execute();
            if ($registration_success_state) {
                pushNotification("banner_success","Le nouveau compte utilisateur a bien été créé :D<br>L'utilisateur peur désormais utiliser les identifiants de connexion que vous venez de saisir pour se connecter.");
                return true;
            } else {
                pushNotification("banner_error","ERREUR - Le système d'inscription a rencontré une erreur inconnue en enregistrant les données dans la base.<br>Veuillez vous rendre à la page précédente et réessayer. Si le problème persiste réinstallez les fichiers du Gestionnaire de congés.");
            }
        }
        // default return
        return false;
    }

    /**
     * Simply returns the current status of the user's login
     * @return bool User's login status
     */
    public function getUserLoginStatus()
    {
        return $this->user_is_logged_in;
    }

    /**
     * Simple demo-"page" with the login form.
     * In a real application you would probably include an html-template here, but for this extremely simple
     * demo the "echo" statements are totally okay.
     */
    private function setHeadersPageLoginForm()
    { 
        $GLOBALS["html_header_title"] = $GLOBALS["html_header_title"]." - Connexion requise";
        $GLOBALS["html_stylesheets"][] = "assets/stylesheets/login.css";
        $this->content_mode = "LoginForm";
    }

    private function showPageLoginForm()
    {
        // PAGE DE LOGIN
        ?>

            <div class="vertical-center">
                <div class="container">
                    <form method="post" action="<?=$_SERVER['SCRIPT_NAME'] ?>" class="col-md-6 col-md-offset-3 well">
                        <legend class="text-center"><h1>Connexion</h1></legend>
                        <header class="panel panel-default">
                            <div class="panel-body">
                                Bienvenue dans le Gestionnaire de Congés !<br>
                                Vous devez obligatoirement vous connecter avec votre compte d'utilisateur pour pouvoir utiliser l'application.<br>
                                Vos identifiants vous ont sûrement été remis par votre gestionnaire ou par l'administrateur système.
                            </div>
                        </header>
                        <div class="form-group">
                            <label for="login_input_username">Identifiant :</label>
                            <input autofocus required type="text" class="form-control input-lg" name="login_input_username" id="login_input_username" placeholder="Par défaut : prenom.nom" <? if(isset($_POST['login_input_username'])){?>value="<?=$_POST['login_input_username'] ?>" <? } ?>size="30" maxlength="255"/>
                        </div>
                        <div class="form-group">
                            <label for="login_input_password">Mot de passe : </label>
                            <input required type="password" class="form-control input-lg" name="login_input_password" id="login_input_password" size="30">
                        </div>
                        <div class="row form-group">
                            <input type="submit" name="login_input_submit" value="Connexion" id="login_input_submit" class="btn-lg col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 btn btn-primary" />
                        </div>
                    </form>
                </div>
            </div>
        <?
    }

    private function xhrCheckUsernameAvailability() {
        if (!isset($_GET['username'])) {
            echo json_encode(array('status'=>"error",'message'=>'Aucun identifiant entré'));
            return false;
        }
        if (!$this->createDatabaseConnection()) {
            echo json_encode(array('status'=>"error",'message'=>'Erreur de connexion à la base de données'));
            return false;
        }
        // validating the input
        if (!empty($_GET['username'])
            && mb_strlen($_GET['username']) <= 64
            && mb_strlen($_GET['username']) >= 2
            && preg_match('/^[a-z0-9\.\d]{2,64}$/', $_GET['username'])
        ) {
            // only this case return true, only this case is valid
            // remove html code etc. from username and email
            $user_name = htmlentities($_GET['username'], ENT_QUOTES, "UTF-8");
            $this->db_connection->exec("SET NAMES 'utf8'");
            $sql = 'SELECT * FROM dims_mod_vacationpanel_users WHERE user = :user_name';
            $query = $this->db_connection->prepare($sql);
            $query->bindValue(':user_name', $user_name);
            $query->execute();

            // As there is no numRows() in SQLite/PDO (!!) we have to do it this way:
            // If you meet the inventor of PDO, punch him. Seriously.
            $result_row = $query->fetchObject();
            if ($result_row) {
                echo json_encode(array('status'=>"ok",'username'=>$user_name,'isAvailable'=>"no"));
                return true;
            } else {
                echo json_encode(array('status'=>"ok",'username'=>$user_name,'isAvailable'=>"yes"));
                return true;
            }
        } elseif (empty($_GET['username'])) {
            echo json_encode(array('status'=>"error",'message'=>'Aucun identifiant entré'));
        } elseif (mb_strlen($_GET['username']) > 64 || mb_strlen($_GET['username']) < 2) {
            echo json_encode(array('status'=>"error",'message'=>'Format de l\'identifiant incorrect'));
        } elseif (!preg_match('/^[a-z\d]{2,64}$/i', $_GET['username'])) {
            echo json_encode(array('status'=>"error",'message'=>'Format de l\'identifiant incorrect'));
        } else {
            echo json_encode(array('status'=>"error",'message'=>'Erreur inconnue à la vérification des données entrées'));
        }
    }

    public function textTruncate($string, $limit, $break=".", $pad="...")
    {
      // Original PHP code by Chirp Internet: www.chirp.com.au
      // Please acknowledge use of this code by including this header.

      // return with no change if string is shorter than $limit
      if(mb_strlen($string) <= $limit) return $string;
      // is $break present between $limit and the end of the string?
          $string = mb_substr($string, 0, $limit-3) . $pad;

      return $string;
    }

    private function xhrGetColleagueVacations() {
        if (!isset($_GET['username'])) {
            echo json_encode(array('status'=>"error",'message'=>'Aucun identifiant entré'));
            return false;
        }
        if (!$this->createDatabaseConnection()) {
            echo json_encode(array('status'=>"error",'message'=>'Erreur de connexion à la base de données'));
            return false;
        }
        // validating the input
        if (!empty($_GET['username'])
            && mb_strlen($_GET['username']) <= 64
            && mb_strlen($_GET['username']) >= 2
            && preg_match('/^[a-z0-9\.\d]{2,64}$/', $_GET['username'])
        ) {
            // only this case return true, only this case is valid
            // remove html code etc. from username and email
            $user_name = htmlentities($_GET['username'], ENT_QUOTES, "UTF-8");
            $this->db_connection->exec("SET NAMES 'utf8'");
            $sql = 'SELECT * FROM dims_mod_vacationpanel_users WHERE user = :user_name';
            $query = $this->db_connection->prepare($sql);
            $query->bindValue(':user_name', $user_name);
            $query->execute();

            // As there is no numRows() in SQLite/PDO (!!) we have to do it this way:
            // If you meet the inventor of PDO, punch him. Seriously.
            $result_row = $query->fetchObject();
            if ($result_row) {
                echo json_encode(array('status'=>"ok",'username'=>$user_name,'isAvailable'=>"no"));
                return true;
            } else {
                echo json_encode(array('status'=>"ok",'username'=>$user_name,'isAvailable'=>"yes"));
                return true;
            }
        } elseif (empty($_GET['username'])) {
            echo json_encode(array('status'=>"error",'message'=>'Aucun identifiant entré'));
        } elseif (mb_strlen($_GET['username']) > 64 || mb_strlen($_GET['username']) < 2) {
            echo json_encode(array('status'=>"error",'message'=>'Format de l\'identifiant incorrect'));
        } elseif (!preg_match('/^[a-z\d]{2,64}$/i', $_GET['username'])) {
            echo json_encode(array('status'=>"error",'message'=>'Format de l\'identifiant incorrect'));
        } else {
            echo json_encode(array('status'=>"error",'message'=>'Erreur inconnue à la vérification des données entrées'));
        }
    }
}
