<div style="padding-bottom: 60px"></div>
<div class="hidden-print navbar-xs navbar-default navbar-fixed-bottom" style="padding-top:10px">
	<span class="hidden-xs">
		Le {$smarty.now|date_format:"%A, %e %b %Y"} à {$smarty.now|date_format:"%Hh%M"} Adresse IP:
		<strong>{$identiteReseau.ip}</strong> {$identiteReseau.hostname} Votre passage est enregistré
		<span id="execTime" class="pull-right">{if $executionTime}Temps d'exécution du script: {$executionTime}s{/if}</span>
	</span>

	<span class="visible-xs">
		{$identiteReseau.ip} {$identiteReseau.hostname} {$smarty.now|date_format:"%A, %e %b %Y"} {$smarty.now|date_format:"%Hh%M"}
	</span>

</div>
<!-- navbar -->
