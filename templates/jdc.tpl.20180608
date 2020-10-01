<ul class="nav nav-pills">
    <li class="active"><a data-toggle="tab" href="#calendrier"><i class="fa fa-calendar fa-lg"></i> JDC calendrier</a></li>
    <li><a data-toggle="tab" href="#journalier"><i class="fa fa-file-pdf-o fa-lg"></i> Imprimer</a></li>
</ul>

<div class="tab-content">

	<div class="row tab-pane fade in active" id="calendrier">

		<div class="col-md-7 col-xs-12">

			{if $identite.type == 'eleve'}
			<h3>Journal de classe de {$identite.prenom} {$identite.nom}: {$identite.groupe}</h3>
			{else}
			<h3>Journal de classe de {$identite.prenomEl} {$identite.nomEl}: {$identite.groupe}</h3>
			{/if}

			<div id="calendar"></div>

		</div>
		<div class="col-md-5 col-xs-12">
			<div class="encadre">
				<form action="index.php" method="POST" name="detailsJour" id="detailsJour" role="form" class="form-vertical">
					<input type="hidden" name="ladate" id="ladate" class="ladate" value="">
					<input type="hidden" name="view" id="view" value="">
					<div id="unTravail">
						<strong>Sélectionner un événement dans le calendrier</strong>
					</div>
				</form>
			</div>
		</div>

	</div>
	<!-- row -->

	<div class="row tab-pane fade" id="journalier">

        <div class="col-xs-8">
            {include file="jdc/selectDatesCoursClasse.tpl"}

            <div class="clearfix"></div>

            <div id="jdcJournalier" style="height: 35em; overflow: auto; border: 1px solid black">

            </div>

        </div>

        <div class="col-xs-4">
            {include file="jdc/memoPrint.tpl"}
        </div>

    </div>

</div>
<!-- tab-content -->

</div>

<div class="row">

	{foreach from=$legendeCouleurs key=cat item=travail}
	<div class="col-md-1 col-sm-6">
		<div class="cat_{$cat} discret" title="{$travail.categorie}">{$travail.categorie|truncate:10}</div>
	</div>
	<!-- col-md-... -->
	{/foreach}

</div>
<!-- row -->

<script type="text/javascript">

	$(document).ready(function() {

        $('#dateStart, #dateEnd, #coursGrpClasse, #categories').change(function(){
            var formulaire = $('#selectDatesCours').serialize();
            $.post('inc/jdc/jdcJournalier.inc.php', {
                formulaire: formulaire
            }, function(resultat){
                $('#jdcJournalier').html(resultat);
            })
        })

        $('body').on('click', '.btn-show', function(){
            var bouton = $(this);
            var id = $(this).closest('tr').data('id');
            $.post('inc/jdc/getPostId.inc.php', {
                id: id,
                show: 1
            }, function(resultat){
                bouton.closest('td').prev().html(resultat);
                bouton.addClass('btn-unShow btn-success').removeClass('btn-show');
            })
        })

        $('body').on('click', '.btn-unShow', function(){
            var bouton = $(this);
            var id = $(this).closest('tr').data('id');
            $.post('inc/jdc/getPostId.inc.php', {
                id: id,
                show: 0
            }, function(resultat){
                bouton.closest('td').prev().html(resultat);
                bouton.removeClass('btn-unShow btn-success').addClass('btn-show');
            })
        })

		$('.datepicker').datepicker({
            format: "dd/mm/yyyy",
            clearBtn: true,
            language: "fr",
            calendarWeeks: true,
            autoclose: true,
            todayHighlight: true
            });

		$("#calendar").fullCalendar({
			events: {
				url: 'inc/events.json.php'
			},
			eventLimit: 2,
            defaultView: 'agendaWeek',
            weekends: false,
            weekNumbers: true,
            navLinks: true,
			header: {
				left: 'prev, today, next',
				center: 'title',
				right: 'month,agendaWeek,agendaDay,listMonth,listWeek,list'
			},
            buttonText: {
                listMonth: 'Liste Mois',
                listWeek: 'Liste Semaine',
                list: 'Liste Jour'
            },
			eventClick: function(calEvent, jsEvent, view) {
				var id = calEvent.id; // l'id de l'événement
				$.post('inc/jdc/getTravail.inc.php', {
						id: id,
						origine: 'jdc'
					},
					function(resultat) {
						$("#unTravail").fadeOut(400, function() {
							$("#unTravail").html(resultat);
						});
						$("#unTravail").fadeIn();
						// $("#unTravail").html(resultat)
					}
				)
			},
            eventResize: function(event, delta, revertFunc) {
                var startDate = moment(event.start).format('YYYY-MM-DD HH:mm');
                var endDate = moment(event.end).format('YYYY-MM-DD HH:mm');
                // mémoriser la date, pour le retour
                $("#startDate").val(startDate);
                var id = event.id;
                $.post('inc/getDragDrop.inc.php', {
                        id: id,
                        startDate: startDate,
                        endDate: endDate
                    },
                    function(resultat) {
                        $("#unTravail").html(resultat);
                        $('#calendar').fullCalendar('refetchEvents');
                    }
                )
            },
            eventDrop: function(event, delta, revertFunc) {
                var startDate = moment(event.start).format('YYYY-MM-DD HH:mm');
                // mémoriser la date pour le retour
                $("#startDate").val(startDate);
                // si l'événement est draggé sur allDay, la date de fin est incorrecte
                if (moment.isMoment(event.end))
                    var endDate = moment(event.end).format('YYYY-MM-DD HH:mm');
                else var endDate = '0000-00-00 00:00';
                var id = event.id;
                $.post('inc/getDragDrop.inc.php', {
                        id: id,
                        startDate: startDate,
                        endDate: endDate
                    },
                    function(resultat) {
                        $("#unTravail").html(resultat);
                        $('#calendar').fullCalendar('gotoDate', startDate);
                        // forcer le mode "agendaDay" pour voir finement la modification
                        $('#calendar').fullCalendar('changeView', 'agendaDay');
                    }
                )
            },
			eventRender: function(event, element) {
                if (event.cours == '')
                    cours = "<i class='fa fa-info fa-lg'></i> Information";
                    else if (event.cours == undefined)
                        cours = "<i class='fa fa-user fa-lg'></i> Note personnelle";
                        else cours = event.cours;

                element.html('<strong>' + cours + '</strong><br>' + event.title),
                element.popover({
                    title: event.title,
                    content: event.enonce,
                    trigger: "hover",
                    placement: "top",
                    container: "body"
                });
                },
			defaultTimedEventDuration: '00:50',
			businessHours: {
				start: '08:15',
				end: '16:25',
				dow: [1, 2, 3, 4, 5]
			},
			minTime: "08:00:00",
			maxTime: "22:00:00",
			firstDay: 1,
			dayClick: function(date, event, view) {
				$('#calendar').fullCalendar('gotoDate', date);
				$('#calendar').fullCalendar('changeView', 'agendaDay');
			}
		});

	})
</script>
