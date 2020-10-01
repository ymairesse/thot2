<div id="listeAnnonces" style="max-height: 25em; overflow: auto">

	<table class="table table-condensed table-hover">
		<tr>
			<th style="width:1em">&nbsp;</th>
			<th>Date et heure</th>
			<th>Visible le</th>
			<th>Objet</th>
			<th>PJ</th>
			<th>Responsable</th>
			<th>Destinataire</th>
			<th>Acc. lecture</th>
		</tr>
		{foreach from=$listeAnnonces key=id item=dataAnnonce name=n}
			<tr
				data-id="{$id}"
				data-accuse="
					{if ($dataAnnonce.accuse == 1)}
					 	{if $dataAnnonce.flags.dateHeure == Null}
						1
						{else}
						0
						{/if}
					{else}
					0
					{/if}"

				class="notification {$dataAnnonce.type}{if (!(isset($dataAnnonce.flags)) || $dataAnnonce.flags.lu == 0 )} nonLu{/if}">
				<td>{$smarty.foreach.n.iteration}</td>
				<td>{if $dataAnnonce.dateEnvoi != Null}{$dataAnnonce.dateEnvoi|truncate:16:'':true}{else}Inconnue{/if}</td>
				<td>{$dataAnnonce.dateDebut}</td>
				<td data-id="{$id}" class="texteAnnonce">{$dataAnnonce.objet}</td>
				<td>{if ($dataAnnonce.PJ != Null)}<i class="fa fa-paperclip"></i>{else}&nbsp;{/if}</td>
				<td>{$dataAnnonce.proprietaire}</td>
				<td>
					{if $dataAnnonce.destinataire == $matricule}
						<i class="fa fa-user text-success" title="{$dataAnnonce.pourQui}"></i>
						{else}
						<i class="fa fa-users text-info" title="{$dataAnnonce.pourQui}"></i>
					{/if}
				</td>
				<td class="dateHeure">
					{if $dataAnnonce.accuse == 1}
						{if ($dataAnnonce.flags.dateHeure == Null)}
							<i class="fa fa-warning fa-lg faa-flash animated text-danger"></i>
							{else}
							{$dataAnnonce.flags.dateHeure}
						{/if}
					{else}
					-
					{/if}
				</td>

			</tr>

		{/foreach}

	</table>

</div>

<p class="help-block pull-right">Total: {$listeAnnonces|@count} annonces.</p>

<h4>Code des couleurs</h4>
<ul class="list-inline">
	<li class="ecole">Pour tous les élèves</li>
	<li class="niveau">Pour tous les élèves de ton niveau</li>
	<li class="cours">Pour un ou plusieurs élèves d'un cours</li>
	<li class="classes">Pour un ou plusieurs élèves de ta classe</li>
</ul>

{include file='annonces/modal/modalAccuseLecture.tpl'}
{include file='annonces/modal/modalLecture.tpl'}

<script type="text/javascript">

	$(document).ready(function() {

		var nbAccuses = {$nbAccuses};
		var nbNonLus = {$nbNonLus};
		var texte = 'Vous avez ';
		if (nbNonLus > 0) {
			texte += '<strong>' + nbNonLus + '</strong> message(s) non lu(s) ';
			if (nbAccuses > 0)
				texte += 'et ';
		}
		if (nbAccuses > 0) {
			texte += '<strong>' + nbAccuses + '</strong> accusés de lecture en attente <i class="fa fa-warning faa-flash animated text-danger"></i>';
		}

		if (nbAccuses > 0 || nbNonLus > 0)
			bootbox.alert({
				message: texte,
				className: 'animated rubberBand',
				backdrop: false
			});

		$('#listeAnnonces tr').click(function(){
			var ligne = $(this);
			var notifId = $(this).data('id');
			// marquer l'annonce lue
			if ($(this).hasClass('nonLu')) {
				$.post('inc/annonces/marqueLu.inc.php', {
					notifId: notifId
					},
					function(resultat){
						ligne.removeClass('nonLu');
					})
				}

			$('#listeAnnonces tr').removeClass('active');
			$(this).addClass('active');

			$.post('inc/annonces/listePJ4notif.inc.php', {
				notifId: notifId
				}, function (resultat){
					$('#modalLecture .modal-body .PJ').html(resultat);
					$('#modalAccuseLecture .modal-body .PJ').html(resultat);
				})

			var accuse = $(this).data('accuse');

			$.post('inc/annonces/getTexteAnnonce.inc.php', {
				notifId: notifId
			}, function(texteHTML){
				if (accuse == 1) {
					$('#modalAccuseLecture .modal-body .texteAnnonce').html(texteHTML);
					$('#cbConfirmation').prop('checked', false).prop('disabled', false);

					$('#cbConfirmation').data('id', notifId);
					$('#modalAccuseLecture').modal('show');
				}
					else {
						$('#modalLecture .modal-body .texteAnnonce').html(texteHTML);
						$('#modalLecture').modal('show');
					}
			})

		})

		$('#modalAccuseLecture').on('hide.bs.modal', function (e) {
			var confirme = $('#cbConfirmation').prop('checked');
			if (confirme == false)
				e.preventDefault();
			})

		$('#cbConfirmation').change(function(){
			var cb = $(this);
			cb.prop('disabled', true);
			var id = cb.data('id');
            if (cb.prop('checked') == true) {
				$.post("inc/accuseLecture.inc.php", {
						id: id
					},
					function(resultat) {
						cb.next('span').text(' le '+resultat);
						$('#modalConfirmationLecture').data('id', id);
						$('#modalConfirmationLecture').prop('disabled', false);
						$('#listeAnnonces table tr[data-id="' + id + '"]').find('td.dateHeure').html(resultat);
						$('#listeAnnonces table tr[data-id="' + id + '"]').data('accuse', 0);
					});
                }
        	})

		$('#modalConfirmationLecture').click(function(){
			$('#modalAccuseLecture').modal('hide');

		})

	})
</script>
