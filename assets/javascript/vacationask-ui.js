/* Gestion de l'affix en cas de basse résolution d'écran */
$(window).resize(function(){
	if($(window).width() < 768) {
		$('#affix').css("position","relative");
		$('#affix').css("z-index",1);
		$('#affix').css("opacity",1);
	} else {
		$('#affix').css("position","fixed");
		$('#affix').css("z-index",1031);
		$('#affix').css("opacity",0.5);
	}
});

$(window).trigger('resize');

// Gestion du choix de mode d'affichage
$(function(){
	if(localStorage['vacationask_layoutmode']) {
		$("#toggle-"+localStorage['vacationask_layoutmode']+"-mode").tab("show");
		$(".mode-dependent").hide();
		$(".mode-"+localStorage['vacationask_layoutmode']+"-only").show();
		if(localStorage["vacationask_layoutmode"] == "calendar") {
			startCalendarView();
		}
	}
});

$("#btn-calendar-mode").click(function(){
	$("#toggle-calendar-mode").tab("show");
	$(".mode-list-only").hide();
	$(".mode-calendar-only").show();
	startCalendarView();
	if(localStorage) {
		localStorage['vacationask_layoutmode'] = "calendar";
	}
});

$("#btn-list-mode").click(function(){
	$("#toggle-list-mode").tab("show");
	$(".mode-list-only").show();
	$(".mode-calendar-only").hide();
	if(localStorage) {
		localStorage['vacationask_layoutmode'] = "list";
	}
});

// Gestion de localStorage
$(function(){
	if(localStorage) {
		$(".localStorageNote").removeClass("hidden");
	}
});