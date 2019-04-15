
// On fait en sorte que le header ne cache jamais le contenu
	// Exécution au chargement de la page (on n'a pas encore redimensionné la page)
  	var newPadding = $("nav").outerHeight()+20;
  	$("body").css("padding-top",newPadding+"px");
	// On met en place l'évènement qui actualise le padding à chaque redimensionnement de la page
	$( window ).resize(function() {
  		var newPadding = $("nav").outerHeight()+20;
  		$("body").css("padding-top",newPadding+"px");
	});

// On active tous les popovers dès le chargement de la page
$(function(){
  $('[data-toggle="popover"]').popover()
});

// On active toutes les infobulles d'aide dès le chargement de la page
$(function () {
  $('.help-tooltip').tooltip({
  	container:"body",
  	delay: { "show": 1000, "hide": 0 }
  })
})

// Gestion du popover de notifications
$('#notifications-menu-button').popover({
	html:true,
	container:"body",
	placement:"bottom",
	title:"Mes notifications",
	content: function(){
		// Oh, on a regardé les notifications ! Le bouton arrête de clignoter
		clearNewNotificationsAnimation();
		// On vérifie s'il y a des notifications
		return "<strong>ROCK'N'ROLL!</strong>";
	}
});

// Gestion du popover des paramètres utilisateur
$('#usersettings-menu-button').popover({
	html:true,
	container:"body",
	placement:"bottom",
	title: "Mes paramètres",
	content: function(){
		// On récupère les valeurs actuelles des paramètres
		return "Voici les paramètres utilisateur.<br><strong>EXCELLENT!</strong>";
	}
});

// Gestion de l'infobulle des paramètres utilisateur
$('#usersettings-menu-button').tooltip({
	container: "body",
	title:"Cliquez ici pour gérer vos paramètres personnels (coordonnées, mot de passe, préférences d'affichage et d'e-mailing...)",
	delay: { "show": 1000, "hide": 0 },
	placement: "left"
});

// Gestion de l'infobulle des notifications
$('#notifications-menu-button').tooltip({
	container: "body",
	title:"Cliquez ici pour ouvrir le panneau des notifications pour voir vos nouveaux messages",
	delay: { "show": 1000, "hide": 0 },
	placement: "left"
});

// Pour éviter d'embêter l'utilisateur avec un tooltip et un popover à la fois, on enlève le tooltip manuellement
$('#usersettings-menu-button').on('click', function(e){
	$(this).tooltip('destroy');
});
$('#notifications-menu-button').on('click', function(e){
	$(this).tooltip('destroy');
});

// Gestion du bouton pour ouvrir les paramètres utilisateur
$('#openUserSettingsMenu').on('click', function(e){
	e.preventDefault();
	$('#usersettings-menu-button').popover('show');
	function toggleBackgroundColor() {
		$(".popover").toggleClass("highlight-background");
	}

	setTimeout(function(){$(".popover").toggleClass("highlight-background");}, 100);
	setTimeout(function(){$(".popover").toggleClass("highlight-background");}, 600);
	setTimeout(function(){$(".popover").toggleClass("highlight-background");}, 1100);
	setTimeout(function(){$(".popover").toggleClass("highlight-background");}, 1600);
	setTimeout(function(){$(".popover").toggleClass("highlight-background");}, 2100);
	setTimeout(function(){$(".popover").toggleClass("highlight-background");}, 2600);
	setTimeout(function(){$(".popover").toggleClass("highlight-background");}, 3100);
});

// Gestion des champs du solde de congés (à n'activer que si l'utilisateur créé est un employé) et des onglets par défaut (choix variable)
$("#register_input_role").change(function(){

	if($(this).val() == "worker" || $(this).val() == "manager-worker") // Solde de congés
	{
		$("#register_input_days_formgroup").show();
	} else {
		$("#register_input_days_formgroup").hide();
	}

	if($(this).val() == "manager" || $(this).val() == "manager-worker") { // Onglets par défaut
		$("#register_input_defaulttab").removeAttr("disabled");
		$("#manager-tabs").removeAttr("disabled");
		$("#preferredtab-worker").removeAttr("selected");
		$("#preferredtab-manager").prop("selected","selected");

		if($(this).val() == "manager") {
			$("#worker-tabs").prop("disabled","disabled");
		} else {
			$("#worker-tabs").removeAttr("disabled");
		}
	} else {
		$("#register_input_defaulttab").prop("disabled","disabled");
		$("#manager-tabs").prop("disabled","disabled");
		$("#worker-tabs").removeAttr("disabled");
		$("#preferredtab-manager").removeAttr("selected");
		$("#preferredtab-worker").prop("selected","selected");
	}
});

// Gestion de l'erreur en cas de mot de passe incorrectement répété
$("#register_input_submit").click(function(e){
	if($("#register_input_password_repeat").val() != $("#register_input_password_new").val()) {
		e.preventDefault();
		$("#password_mismatch_alert").remove();
		$("body").prepend('<div class="alert_banner alert alert-danger col-xs-12 col-sm-offset-1 col-sm-10 col-lg-offset-2 col-lg-8 alert-dismissible fade in" role="alert" id="password_mismatch_alert">ERREUR - Veuillez renseigner des mots de passe identiques dans les champs "Mot de passe" et "Mot de passe (répétez).<button type="button" class="close" data-dismiss="alert" aria-label="Fermer"><span aria-hidden="true">&times;</span></button></div>');
	}
});

// Gestion du bouton "Haut de page"
// Only enable if the document has a long scroll bar
// Note the window height + offset
if ( ($(window).height() + 100) < $(document).height() ) {
    $('#top-link-block').removeClass('hidden').affix({
        // how far to scroll down before link "slides" into view
        offset: {top:100}
    });
}