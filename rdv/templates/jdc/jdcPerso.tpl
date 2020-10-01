<h3>Notes personnelles de
{if $identite.type == 'eleve'}
	{$identite.prenom} {$identite.nom}
	{else}
	{$identite.prenomEl} {$identite.nomEl}
{/if}
</h3>
<div class="container-fluid">

	<div class="row">

		<div class="col-md-7 col-xs-12">

			<div id="calendar" style="background:#e6c34b; color:#662121;">

			</div>

		</div>

		<div class="col-md-5 col-xs-12" style="max-height:50em; overflow: auto">


			<form action="index.php" method="POST" name="detailsJour" id="detailsJour" role="form" class="form-vertical ombre">
				<!-- champs destinés à être lus pour d'autres formulaires -->
				<input type="hidden" name="startDate" id="startDate" value="{$startDate|default:''}">
				<input type="hidden" name="viewState" id="viewState" value="">

				<div id="unTravail">
					{include file="jdc/selectItem.html"}
				</div>
				<div id="finForm"></div>
			</form>

		</div>
		<!-- col-md-... -->

	</div>
	<!-- row -->

	<div class="row">
		{* légende et couleurs *}
		<div class="btn-group" id="legend">
			{foreach from=$categories key=cat item=travail}
			<button type="btn btn-default" class="cat_{$cat} voir" data-categorie="{$cat}" title="{$travail.categorie}">{$travail.categorie|truncate:12}</button>
			{/foreach}
		</div>

	</div>
	<!-- row -->

	<div id="zoneDel">
	</div>

</div>
<!-- container -->

<script type="text/javascript">

	// bootstrap-ckeditor-fix.js
	// hack to fix ckeditor/bootstrap compatiability bug when ckeditor appears in a bootstrap modal dialog
	//
	// Include this file AFTER both jQuery and bootstrap are loaded.
	// http://ckeditor.com/comment/127719#comment-127719
	$.fn.modal.Constructor.prototype.enforceFocus = function() {
		modal_this = this
		$(document).on('focusin.modal', function(e) {
			if (modal_this.$element[0] !== e.target && !modal_this.$element.has(e.target).length &&
				!$(e.target.parentNode).hasClass('cke_dialog_ui_input_select') &&
				!$(e.target.parentNode).hasClass('cke_dialog_ui_input_text')) {
				modal_this.$element.focus()
			}
		})
	};

	function dateFromFr(uneDate) {
		var laDate = uneDate.split('/');
		return laDate[2] + '-' + laDate[1] + '-' + laDate[0];
	}


	var datePassee = 'Cette date est passée';

	$(document).ready(function() {

		// http://jsfiddle.net/slyvain/6vmjt9rb/
		var popTemplate = [
			'<div class="popover" style="max-width:600px;" >',
			'<div class="popover-header">',
			'<h3 class="popover-title"></h3>',
			'</div>',
			'<div class="popover-content"></div>',
			'</div>'].join('');

		var popoverElement;

		function closePopovers() {
			$('.popover').not(this).popover('hide');
		}

		function goToByScroll(link){
			$('html,body').animate({
				scrollTop: $("#" + link).offset().top+200
				},
				'slow'
				);
			}

		$('#calendar').fullCalendar({
			weekends: true,
			defaultView: 'agendaWeek',
			eventLimit: 3,
			height: 600,
			timeFormat: 'HH:mm',
			header: {
				left: 'prev, today, next',
				center: 'title',
				right: 'month,agendaWeek,agendaDay,listMonth,listWeek'
			},
			buttonText: {
				listMonth: 'Liste Mois',
				listWeek: 'Liste Semaine'
			},
			businessHours: {
				start: '06:00',
				end: '23:00',
				dow: [1, 2, 3, 4, 5]
				},
			minTime: "06:00:00",
			maxTime: "24:00:00",
			weekNumbers: true,
			navLinks: true,
			eventStartEditable: function(event){
				return false;
			},
			eventDurationEditable: false,
			defaultTimedEventDuration: '00:50',
			firstDay: 1,
			events: {
				url: 'inc/eventsPerso.json.php'
				},
			error: function() {
				alert('Une erreur s\'est produite. Merci de la signaler à l\'administrateur.');
				},
			eventRender: function(event, element, view) {
				element.popover({
					title: '<strong>' + event.title + '</strong>',
					content: event.enonce,
					html: true,
					template: popTemplate,
					trigger: 'hover',
					placement: 'top',
					container: 'body'
				});
				element.attr('data-type', event.type);
			},
			// on clique sur un événement
			eventClick: function (calEvent, jsEvent, view) {
				var debut = moment(calEvent.start);
                var today = moment().format('YYYY-MM-DD');
				var unlockedPast = $('#unlocked').val();
				var locked = (debut.isBefore(today) && (unlockedPast == "false")) ;
				popoverElement = $(jsEvent.currentTarget);
				var id = calEvent.id; // l'id de l'événement

				$.post('inc/jdc/getTravail.inc.php', {
					id: id,
					editable: true,
					locked: locked
					},
					function(resultat) {
						$('#unTravail').fadeOut(400, function() {
						$('#unTravail').html(resultat).fadeIn();
						goToByScroll('finForm');
						});
					}
				)
			},
			// on clique dans le calendrier (ajout d'événement)
			dayClick: function(calEvent, jsEvent, view) {
				var debut = moment(calEvent);
                var today = moment().format('YYYY-MM-DD');
				var unlockedPast = $('#unlocked').val();
                if (debut.isBefore(today) && (unlockedPast == "false")) {
                    bootbox.alert({
                        title: 'Erreur',
                        message: datePassee
                    })
                } else {
					if (view.type == 'agendaDay'){
						var heure = moment(calEvent).format('HH:mm');
						var startDate = moment(calEvent).format('DD/MM/YYYY');
						$.post('inc/jdc/getAdd.inc.php', {
							startDate: startDate,
							heure: heure,
							},
							function(resultat){
								$('#unTravail').html(resultat);
								goToByScroll('finForm');
							})
						}
					else {
						$('#calendar').fullCalendar('gotoDate', debut);
						// forcer le mode "agendaDay" pour permettre la modification
						$('#calendar').fullCalendar('changeView', 'agendaDay');
					}
				}
			},
			eventResize: function(calEvent, delta, revertFunc) {
				var startDate = moment(calEvent.start).format('YYYY-MM-DD HH:mm');
				var endDate = moment(calEvent.end).format('YYYY-MM-DD HH:mm');
				var id = calEvent.id;

				$.post('inc/jdc/getDragDrop.inc.php', {
						id: id,
						startDate: startDate,
						endDate: endDate,
						editable: true,
						allDay: false
					},
					function(resultat) {
						$("#unTravail").html(resultat);
					}
				)
			},
			eventDrop: function(calEvent, delta, revertFunc, jsEvent, ui, view) {
				var debut = moment(calEvent.start);
				var today = moment().format('YYYY-MM-DD');
				$('.popover').remove();
				if (debut.isBefore(today)) {
					bootbox.alert({
						title: 'Erreur',
						message: datePassee,
						callback: function(){
							$('.popover').remove();
							}
					});
					$('#calendar').fullCalendar('refetchEvents');
				}
				else {
					var startDate = moment(calEvent.start).format('YYYY-MM-DD HH:mm');
					var endDate = moment(calEvent.end).format('YYYY-MM-DD HH:mm');
					// si l'événement est draggé sur allDay, la date de fin est incorrecte
					if (calEvent.allDay == true) {
						var endDate = startDate;
						}

					// si l'événement est draggé depuis allDay, la date de fin est Null
					if (calEvent.endDate == undefined) {
						endDate = startDate;
					}

					var id = calEvent.id;
					$.post('inc/jdc/getDragDrop.inc.php', {
							id: id,
							startDate: startDate,
							endDate: endDate,
							editable: true,
							allDay: calEvent.allDay
						},
						function(resultat) {
							$("#unTravail").html(resultat);
							$(".popover").remove();
						}
					)
			}
		}
		})


		$("#unTravail").on('click', '#journee', function() {
			if ($(this).prop('checked') == true) {
				$("#duree").prop('disabled', true);
				$('#heure').prop('disabled', true).val('');
				$("#timepicker").prop('disabled', true);
				$("#listeDurees").addClass('disabled');
			} else {
				$("#duree").prop('disabled', false);
				$('#heure').prop('disabled', false);
				$("#timepicker").prop('disabled', false);
				$("#listeDurees").removeClass('disabled');
			}
		})

		$("#unTravail").on('change', '#categorie', function(){
	        if (($('#titre').val() == '') && ($('#categorie').val() != '')) {
	            var texte = $("#categorie option:selected" ).text();
	            $('#titre').val(texte);
	        }
	    })

		{if isset($startDate)}
			$('#calendar').fullCalendar('gotoDate', moment("{$startDate}"));
		{/if}

})
</script>
