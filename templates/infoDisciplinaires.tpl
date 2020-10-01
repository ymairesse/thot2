<h2>Fiche de comportement</h2>

{if $listeFaits != Null}

	{if $userType == 'eleve'}

		<p class="info">La fiche de comportement est accessible aux parents uniquement.</p>
		<p>Veuillez vous connecter avec votre identifiant "parent" et votre mot de passe.</p>

	   {else}

		<div style="max-height: 35em; overflow: auto">

			{foreach from=$listeTypesFaits key=typeFait item=descriptionTypeFait}

				{* si un fait de ce type figure dans la fiche disciplinaire *}
				{if isset($listeFaits[$typeFait])}
					{* on se trouve dans le contexte "tableau" *}
					{assign var=contexte value='tableau'}
					{* on indique le titre de ce type de faits *}
					<h3 style="clear:both;background-color: {$descriptionTypeFait.couleurFond}; color: {$descriptionTypeFait.couleurTexte}">
						{$descriptionTypeFait.titreFait}
						<span class="badge pull-right" style="background:red"> {$listeFaits.$typeFait|@count}</span>
					</h3>

					<div class="table-responsive">

						<table class="table table-striped table-condensed">
							{* ----------------- ligne de titre du tableau -------------------------- *}
							<tr>
								<th>&nbsp;</th>
								{strip}
								{if $descriptionTypeFait.typeRetenue != 0}
									<th style="width:1em">&nbsp;</th>
									<th style="width:1em">&nbsp;</th>
								{/if}

								{* on examine chacun des champs qui décrivent le fait *}
								{foreach from=$descriptionTypeFait.listeChamps item=champ}
									{* si le champ intervient dans le contexte (ici, "tableau"), on écrit le label corredpondant *}
									{if in_array($contexte, $descriptionChamps.$champ.contextes)}
									<th>{$descriptionChamps.$champ.label}</th>
									{/if}
								{/foreach}
								{/strip}
							</tr>
							{* // ----------------- ligne de titre du tableau -------------------------- *}
							{* ------------------ description du fait -------------------------------- *}
							{foreach from=$listeFaits.$typeFait key=idfait item=unFaitDeCeType}
							<tr data-idfait="{$idfait}">
								<td style="width:1em">
								</td>

								{if $descriptionTypeFait.typeRetenue != 0}
								<td style="width:1em">
								</td>

								<td style="width:1em">
								</td>
								{/if}
								{foreach from=$descriptionTypeFait.listeChamps item=champ}
									{strip}
										{if in_array($contexte, $descriptionChamps.$champ.contextes)}
										<td>
											{* s'il s'agit d'une retenue, les informations suivantes se trouvent dans la liste des retenues de cet élève *}
											{assign var=type value=$descriptionTypeFait.type}
											{if ($listeTypesFaits.$type.typeRetenue > 0) && (in_array($champ,array('dateRetenue','heure','duree','local')))}
												{assign var=idretenue value=$unFaitDeCeType.idretenue}
												{$listeRetenuesEleves.$idretenue.$champ}
											{else}
												{$unFaitDeCeType.$champ|default:'&nbsp;'}
											{/if}
										</td>
										{/if}
									{/strip}
								{/foreach}

								<td style="width:16px">
								</td>
							</tr>
							{/foreach}
							{* // ------------------ description du fait -------------------------------- *}

						</table>
					</div>
					<!-- table -->
				{/if}
			{/foreach}
		</div>
		{/if}
	{else}
		<p class="avertissement">Fiche de comportement vierge</p>
{/if}
