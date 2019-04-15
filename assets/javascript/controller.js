// On ajoute une méthode permettant à chaque string d'avoir sa première lettre en majuscule
String.prototype.capitalize = function() {
	return this.charAt(0).toUpperCase() + this.slice(1);
}
// Système de vérification de disponibilité d'un nom d'utilisateur
function checkUsernameAvailability(object){
	// On verrouille le champ
	$(object).prop("readonly","readonly"); // On met l'input en lecture seule
	$(object).parent().children().last().children().last().prop("disabled","disabled"); // On désactive les boutons latéraux
	$(object).parent().children().first().children().last().prop("disabled","disabled");
	$(object).parent().children("img").css("visibility","visible");
	$.ajax({
		complete:function(){},
		data:{action:"xhr_checkUsernameAvailability",username:$(object).val()},
		dataType:"json",
		error:function(jqXHR,textStatus,errorThrown){
			$("#username_check_alert").remove();
			$("body").prepend('<div class="alert_banner alert alert-warning col-xs-12 col-sm-offset-1 col-sm-10 col-lg-offset-2 col-lg-8 alert-dismissible fade in" role="alert" id="username_check_alert">Une erreur s\'est produite en tentant de vérifier la disponibilité du nom d\'utilisateur.<br>Le serveur n\'a pas pu être contacté.<br>Actualisez la page en pressant la touche <kbd>F5</kbd> et réessayez.<br>Si le problème persiste contactez le support technique.<button type="button" class="close" data-dismiss="alert" aria-label="Fermer"><span aria-hidden="true">&times;</span></button></div>');
		},
		success:function(data,jqXHR){
			if(data['status']=="ok" && data['username']==$(object).val() && data['isAvailable']=="yes") {
				$(object).parent().removeClass("has-error");
				$(object).parent().addClass("has-success");
				$("#username_check_alert").remove();
				$("body").prepend('<div class="alert_banner alert alert-success col-xs-12 col-sm-offset-1 col-sm-10 col-lg-offset-2 col-lg-8 alert-dismissible fade in" role="alert" id="username_check_alert">Bonne nouvelle, cet identifiant est disponible :D<button type="button" class="close" data-dismiss="alert" aria-label="Fermer"><span aria-hidden="true">&times;</span></button></div>');
			} else {
				$(object).parent().removeClass("has-success");
				$(object).parent().addClass("has-error");
				$("#username_check_alert").remove();
				$("body").prepend('<div class="alert_banner alert alert-danger col-xs-12 col-sm-offset-1 col-sm-10 col-lg-offset-2 col-lg-8 alert-dismissible fade in" role="alert" id="username_check_alert">Oops, il semblerait qu\'un utilisateur ayant le même identifiant existe déjà sur ce serveur...<br>Si vous cherchez à accéder à ce compte, vous pouvez facilement <a href="index.php?action=manageusers&quickAction=changepassword&username='+$(object).val()+'" class="alert-link">modifier le mot de passe</a> ou <a href="index.php?action=manageusers&quickAction=removeuser&username='+$(object).val()+'" class="alert-link">supprimer</a> ce compte si vous n\'en avez plus besoin.<button type="button" class="close" data-dismiss="alert" aria-label="Fermer"><span aria-hidden="true">&times;</span></button></div>');
			}
		}
	});
	$(object).removeAttr("readonly"); // On désactive la protection
	$(object).parent().children().last().children().last().removeAttr("disabled"); // On active les boutons latéraux
	$(object).parent().children().first().children().last().removeAttr("disabled");
	$(object).parent().children("img").css("visibility","hidden");
}

// Système de vérification de disponibilité d'un nom d'utilisateur
function checkDay(datepicker,date){
	// On verrouille le champ

	$(datepicker).children("img").css("visibility","visible");
	/*$.ajax({
		complete:function(){
			$(datepicker).children("img").css("visibility","hidden");
		},
		data:{day:date.getDate(),month:(date.getMonth()+1),year:date.getFullYear()},
		dataType:"json",
		error:function(jqXHR,textStatus,errorThrown){
			$("#datepicker-workingdayapi-error").remove();
			$("body").prepend('<div class="alert_banner alert alert-warning col-xs-12 col-sm-offset-1 col-sm-10 col-lg-offset-2 col-lg-8 alert-dismissible fade in" role="alert" id="datepicker-dayapi-error">Impossible de se connecter au serveur afin de vérifier quels jours sont fériés.<br>Actualisez la page en pressant la touche <kbd>F5</kbd> et réessayez.<br>Si le problème persiste contactez le support technique.<button type="button" class="close" data-dismiss="alert" aria-label="Fermer"><span aria-hidden="true">&times;</span></button></div>');
		},
		success:function(data,jqXHR){
			if(data['isWorkingDay']==true) {
				$("#datepicker-workingdayapi-error").remove();
			} else {
				$("#datepicker-workingdayapi-error").remove();
				$(datepicker).datepicker('setDatesDisabled',date.getDate()+"/"+(date.getMonth()+1)+"/"+date.getFullYear);
			}
		},
		url:"checkWorkingDay_api.php"
	});*/
}

// Gestion du dimanche de Pâques
function getEasterDate(Y) {
	var C = Math.floor(Y/100);
    var N = Y - 19*Math.floor(Y/19);
    var K = Math.floor((C - 17)/25);
    var I = C - Math.floor(C/4) - Math.floor((C - K)/3) + 19*N + 15;
    I = I - 30*Math.floor((I/30));
    I = I - Math.floor(I/28)*(1 - Math.floor(I/28)*Math.floor(29/(I + 1))*Math.floor((21 - N)/11));
    var J = Y + Math.floor(Y/4) + I + 2 - C + Math.floor(C/4);
    J = J - 7*Math.floor(J/7);
    var L = I - J;
    var M = 3 + Math.floor((L + 40)/44);
    var D = L + 28 - 31*Math.floor(M/4);

    return new Date(Y,M,D,0,0,0,0);
}

// Calcul des jours fériés d'une année
/*$(function(){
	$("#calendar-view").initialize(function(){
	    $("#calendar-view").datepicker("setDatesDisabled",notWorkingDaysArray.datesDisabled);
	});
});
	
function updateDatepicker(){
	$("#calendar-view").datepicker("setDatesDisabled",notWorkingDaysArray.datesDisabled);
}*/
	

var notWorkingDaysArray = {
	yearsProcessed: [],
	datesDisabled: []
};

function addNotWorkingDaysToArray(year) {
	var day = moment("1-1-"+year,"DD-MM-YYYY");
	var frenchformat = "DD/MM/YYYY";
	var tempnwd = [];

	tempnwd.push(day.jourDeLAn().toDate());
	tempnwd.push(day.lundiDePaques().toDate());
	tempnwd.push(day.feteDuTravail().toDate());
	tempnwd.push(day.victoireDeAllies().toDate());
	tempnwd.push(day.ascension().toDate());
	tempnwd.push(day.pentecote().toDate());
	tempnwd.push(day.feteNationale().toDate());
	tempnwd.push(day.assomption().toDate());
	tempnwd.push(day.toussaint().toDate());
	tempnwd.push(day.armistice().toDate());
	tempnwd.push(day.noel().toDate());
	window.notWorkingDaysArray.datesDisabled = window.notWorkingDaysArray.datesDisabled.concat(tempnwd);
	window.notWorkingDaysArray.yearsProcessed.push(year);
}

/* 
Au départ on calcule les jours de l'année actuelle, année précédente et année suivante

Mon idée : on calcule de manière asynchrone les jours fériés dans une fonction jquery,
on les ajoute à un array au fur et à mesure,
et puis à chaque beforeShowMonth on regarde si on a les jrs fériés de la bonne année, si oui
on passe l'array COMPLET à datepicker
$("#calendar-view").datepicker("setDatesDisabled",["23/07/2015","24/07/2015"]);
sino, on relance de manière asynchrone les jours fériés en jquery pour l'année sélectionnée.
En asynchrome on a également les jours de congés des collègues qui sont récupérés en AJAX
à chaque beforeShowMonth, mois par mois */