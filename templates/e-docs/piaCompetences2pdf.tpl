<page backtop="18mm" backbottom="18mm" backleft="15mm" backright="15mm">
	{if $typeDoc == 'pia'} {include file="../e-docs/entetePIA.tpl"} {/if}
	{if $typeDoc == 'competences'}{include file="../e-docs/enteteCompetences.tpl"} {/if}

	{include file="../e-docs/corpsCompetences.tpl"}

	<page_footer>
		{$unEleve.nom} {$unEleve.prenom}
	</page_footer>
</page>
