/* Toute la logique associée au datepicker */
function startCalendarView() {
	$('#calendar-view').datepicker({
		autoclose: false,
	    todayBtn: "linked",
	    clearBtn: true,
	    container: "#calendar-view",
	    language: "fr-FR",
	    multidate: 25, // MODIFIE en fonction du solde de congés disponibles
	    multidateSeparator: ";",
	    forceParse: true,
	    // inputs: // Objets jQuery dans lesquels les dates sélectionnées seront insérées en valeur
	    daysOfWeekDisabled: "0,6",
	    keyboardNavigation: true,
	    calendarWeeks: true,
	    todayHighlight: true,
	    startView: 0,
	    title: "Cliquez sur les jours de congés à demander",
	    // startDate:"d", // Aujourd'hui ?
	    beforeShowDay: function(date) {
	    	var selectedYear = date.getFullYear();
	    	var isDaySelectable = true;
	    	var dayCaption;
	    	var dayStylize = "";
	    	if(notWorkingDaysArray.yearsProcessed.indexOf(selectedYear) == -1) {
	    		addNotWorkingDaysToArray(selectedYear);
	    	}
	    	for(var i=0;i<notWorkingDaysArray.datesDisabled.length;i++){
    			if(notWorkingDaysArray.datesDisabled[i].getTime() == date.getTime()){
		    		isDaySelectable = false;
		    		dayStylize+="notWorkingDay";
		    		var mDate = moment(date);
		    		switch(date.getTime())
					{
						case mDate.jourDeLAn().toDate().getTime():
							dayCaption = "Jour de l'an";
							break;

						case mDate.lundiDePaques().toDate().getTime():
							dayCaption = "Lundi de Pâques";
							break;

						case mDate.feteDuTravail().toDate().getTime():
							dayCaption = "Fête du travail";
							break;

						case mDate.victoireDeAllies().toDate().getTime():
							dayCaption = "Victoire des Alliés";
							break;

						case mDate.feteNationale().toDate().getTime():
							dayCaption = "Fête nationale";
							break;

						case mDate.pentecote().toDate().getTime():
							dayCaption = "Lundi de Pentecôte";
						break;

						case mDate.ascension().toDate().getTime():
							dayCaption = "Ascension";
						break;

						case mDate.assomption().toDate().getTime():
							dayCaption = "Assomption";
						break;

						case mDate.toussaint().toDate().getTime():
							dayCaption = "Toussaint";
						break;

						case mDate.armistice().toDate().getTime():
							dayCaption = "Armistice";
						break;

						case mDate.noel().toDate().getTime():
							dayCaption = "Noël";
						break;

						default:
							console.error("Erreur fatale : label introuvable pour ce jour férié : "+date);
							
					}
					dayCaption = "Jour férié : "+dayCaption;
					break;
				}
	    	}


	    	var dayProperties = {
	    		enabled: isDaySelectable,
	    		//classes: dayCustomClasses,
	    		classes: dayStylize,
	    		tooltip: dayCaption
	    	}
	    	return dayProperties;
	    },
	    toggleActive: true
	});
}