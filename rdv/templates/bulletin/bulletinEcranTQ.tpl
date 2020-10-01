<div class="container">

	<h1 title="{$eleve.matricule}">{$eleve.prenom} {$eleve.nom} {$eleve.classe} | Bulletin {$noBulletin}</h1>

		{foreach from=$listeCoursGrp key=coursGrp item=dataCours}

			{assign var=cours value=$listeCoursGrp.$coursGrp.cours}

			<h3>{$dataCours.libelle} {$dataCours.nbheures}h ({$listeProfs.$coursGrp})</h3>

			{assign var=matricule value=$eleve.matricule}
			<div class="row">

				<div class="col-md-3 col-sm-12">

					<h4>Mentions pour la période</h4>

					<table class="table table-condensed table-striped">
						<tr>
							<td>TJ</td>
							<td><strong>{$cotesGlobales.$coursGrp.$matricule.Tj|default:'&nbsp;'}</strong></td>
						</tr>
						<tr>
							<td>Examen</td>
							<td><strong>{$cotesGlobales.$coursGrp.$matricule.Ex|default:'&nbsp;'}</strong></td>
						</tr>
						<tr>
							<td>Période</td>
							<td><strong>{$cotesGlobales.$coursGrp.$matricule.periode|default:'&nbsp;'}</strong></td>
						</tr>
						<tr>
							<td>Global</td>
							<td><strong>{$cotesGlobales.$coursGrp.$matricule.global|default:'&nbsp;'}</strong></td>
						</tr>

					</table>


				</div>

				<div class="col-md-9 col-sm-12">

					{if isset($listeCotesGeneraux.$noBulletin.$matricule.$coursGrp)}
					{assign var=competences value=1}
					<h4>Détails par compétence</h4>
					<table class="table table-striped">
						<tr>
							<th style="width:60%; text-align:center">Compétence</th>
							<th style="width:25%; text-align:center">Travail Journalier</th>
							<th style="width:15%; text-align:center">Examen</th>
						</tr>
						{foreach from=$listeCompetences.$cours key=idComp item=data}
						{assign var=cotes value=$listeCotesGeneraux.$noBulletin.$matricule.$coursGrp.$idComp}
						{if ($cotes.Tj != '') || ($cotes.Ex != '')}
						<tr>
							<td style="text-align:right">{$data.libelle}</td>
							<td style="text-align: center;"><strong> {$listeCotesGeneraux.$noBulletin.$matricule.$coursGrp.$idComp.Tj|default:'&nbsp'} </strong></td>
							<td style="text-align: center;"><strong> {$listeCotesGeneraux.$noBulletin.$matricule.$coursGrp.$idComp.Ex|default:'&nbsp;'} </strong></td>
						</tr>
						{/if}
						{/foreach}
					</table>

					{else}
						{assign var=competences value=0}
						<h4>Commentaire du professeur</h4>
						<div class="remarqueProf">
						{$commentaires.$noBulletin.$coursGrp.$matricule|default:''|nl2br}
						</div>
					{/if}

				</div>

			</div>

			{if $competences == 1}
			<h4>Commentaire du professeur</h4>
			<div class="remarqueProf">
			{$commentaires.$noBulletin.$coursGrp.$matricule|default:''|nl2br}
			</div>
			{/if}

		{/foreach}

		<h3>Qualification</h3>
		<table class="table table-condensed">
			<tr>
				<th>Année</th>
				<th>Épreuve</th>
				<th>Mention</th>
			</tr>
		{foreach $listeEpreuvesQualif item=epreuve}
			<tr>
				<td>{$epreuve.annee}e année</td>
				<td>{$epreuve.legende}</td>
				{assign var=sigle value=$epreuve.sigle}
				<td>{$qualification.$sigle}</td>
			</tr>
		{/foreach}
		</table>

		<h3>Commentaire du titulaire</h3>
			<div class="remarqueProf">
				{$remarqueTitu.$noBulletin.$matricule|default:'&nbsp;'}
			</div>

</div>  <!-- container -->
