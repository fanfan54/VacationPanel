
/* Animation du bouton affichant le menu des notifications s'il porte un badge (et donc s'il y a des nouvelles notifs) */
if($("#new-notifications-badge").length) {
	var newNotificationColorAlert = setInterval(function(){
		$("#notifications-menu-button").toggleClass("btn-danger");
		$("#notifications-menu-icon").toggleClass("color-white");
	},500)
}

/* Arrêt de la boucle d'animation */
function clearNewNotificationsAnimation(){
	clearInterval(newNotificationColorAlert);
	$("#notifications-menu-button").removeClass("btn-danger");
	$("#notifications-menu-icon").removeClass("color-white");
}

/* Animation de l'affix de vacationask qui est transparent s'il n'est pas survolé */
$("#affix").mouseenter(function(){
	if($(document).width() > 767) {
		$(this).fadeTo("fast",1);
	}
});
$("#affix").mouseleave(function(){
	if($(document).width() > 767) {
		$(this).fadeTo("slow",0.5);
	}
});