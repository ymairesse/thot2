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
            <p class="discret">Attention! Internet Explorer pose problème pour le JDC. Utilisez plutôt un autre navigateur.</p>
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

        var fcView = Cookies.get('fc-view');
        var views = ['month', 'agendaWeek', 'agendaDay', 'listMonth', 'listWeek'];
        if (!(views.includes(fcView)))
            fcView = 'agendaWeek';

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

        // cookie sur le type de vue retenu pour le JDC
        $('#calendar').on('click', '.fc-button', function(){
            if ($(this).hasClass('fc-month-button')) {
                Cookies.set('fc-view', 'month', { expires: 7 });
                }
                else if ($(this).hasClass('fc-agendaWeek-button')) {
                    Cookies.set('fc-view', 'agendaWeek', { expires: 7 });
                }
                else if ($(this).hasClass('fc-agendaDay-button')) {
                    Cookies.set('fc-view', 'agendaDay', { expires: 7 });
                }
                else if ($(this).hasClass('fc-listMonth-button')) {
                    Cookies.set('fc-view', 'listMonth', { expires: 7 });
                }
                else if ($(this).hasClass('fc-listWeek-button')) {
                    Cookies.set('fc-view', 'listWeek', { expires: 7 });
                }
        })

		$("#calendar").fullCalendar({
			events: {
				url: 'inc/events.json.php'
			},
			eventLimit: 2,
            defaultView: fcView,
            weekends: false,
            weekNumbers: true,
            navLinks: true,
            eventStartEditable: false,
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
				console.log(id);
                $('.popover').hide();
				$.post('inc/jdc/getTravail.inc.php', {
						id: id
					},
					function(resultat) {
						$("#unTravail").fadeOut(400, function() {
							$("#unTravail").html(resultat);
						});
						$("#unTravail").fadeIn();
					}
				)
			},
    		eventRender: function(event, element) {
                if (event.cours == '')
                    titre = "<i class='fa fa-info fa-lg'></i> Information";
                    else if (event.type == 'personnal')
                        titre = "<i class='fa fa-user fa-lg'></i> Note personnelle";
                        else if (event.type == 'shared')
                            titre = "<i class='fa fa-user-circle-o'></i> Note partagée";
                            else titre = event.cours; 
                element.html('<strong>' + titre + '</strong><br>' + event.title),
                element.popover({
                    title: event.title,
                    content: event.enonce,
                    trigger: "hover",
                    placement: "top",
                    container: "body",
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
