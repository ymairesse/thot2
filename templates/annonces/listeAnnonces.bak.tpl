<div id="annoncesPerso">

	{if isset($listeAnnonces.eleves)}

		{foreach from=$listeAnnonces.eleves key=id item=uneAnnonce}
			<div id="note_{$id}" class="lesAnnonces">
				{if ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure == Null)}
				<span id="span{$id}" class="pull-right">
					<button class="btn btn-danger pull-right lecture" id="button_{$id}" data-id="{$id}" data-type="eleve" type="button">J'ai lu cette annonce</button>
				</span>
				{elseif ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure != Null)}
				<span class="pull-right dateLecture">Lu le {$uneAnnonce.dateHeure}</span>
				{/if}
				<h4 class="urgence{$uneAnnonce.urgence}">{$uneAnnonce.dateDebut}: {$uneAnnonce.objet}</h4>
				{$uneAnnonce.texte}
				<span class="pull-right contact">Contact: {$uneAnnonce.proprietaire}</span>
			</div>
		{/foreach}

	{/if}

</div>

<div id="annoncesCours">

	{if isset($listeAnnonces.cours)}

		{foreach from=$listeAnnonces.cours key=id item=uneAnnonce}
			<div id="note_{$id}" class="lesAnnonces">
				{if ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure == Null)}
				<span id="span{$id}" class="pull-right">
					<button class="btn btn-danger pull-right lecture" id="button_{$id}" data-id="{$id}" data-type="eleve" type="button">J'ai lu cette annonce</button>
				</span>
				{elseif ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure != Null)}
				<span class="pull-right dateLecture">Lu le {$uneAnnonce.dateHeure}</span>
				{/if}
				<h4 class="urgence{$uneAnnonce.urgence}">{$uneAnnonce.dateDebut}: {$uneAnnonce.objet}</h4>
				{$uneAnnonce.texte}
				<span class="pull-right contact">Contact: {$uneAnnonce.proprietaire}</span>
			</div>
		{/foreach}

	{/if}

</div>


<div id="annoncesClasse">

	{if isset($listeAnnonces.classes)}

		{foreach from=$listeAnnonces.classes key=id item=uneAnnonce}
			<div id="note_{$id}" class="lesAnnonces">
				{if ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure == Null)}
				<span id="span{$id}" class="pull-right">
					<button class="btn btn-danger pull-right lecture" data-id="{$id}" data-type="classe" type="button">J'ai lu cette annonce</button>
				</span>
				{elseif ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure != Null)}
				<span class="pull-right dateLecture">Lu le {$uneAnnonce.dateHeure}</span>
				{/if}
				<h4 class="urgence{$uneAnnonce.urgence}">{$uneAnnonce.dateDebut}: {$uneAnnonce.objet}</h4>
				{$uneAnnonce.texte}
				<span class="pull-right contact">Contact: {$uneAnnonce.proprietaire}</span>
			</div>
		{/foreach}

	{/if}

</div>

<div id="annoncesNiveau">

	{if isset($listeAnnonces.niveau)}

		{foreach from=$listeAnnonces.niveau key=id item=uneAnnonce}
			<div id="note_{$id}" class="lesAnnonces">
				{if ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure == Null)}
				<span id="span{$id}" class="pull-right">
					<button class="btn btn-danger pull-right lecture" data-id="{$id}" data-type="niveau" type="button">J'ai lu cette annonce</button>
				</span>
				{elseif ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure != Null)}
				<span class="pull-right dateLecture">Lu le {$uneAnnonce.dateHeure}</span>
				{/if}
				<h4 class="urgence{$uneAnnonce.urgence}">{$uneAnnonce.dateDebut}: {$uneAnnonce.objet}</h4>
				{$uneAnnonce.texte}
				<p><span  class="pull-right contact">Contact: {$uneAnnonce.proprietaire}</span></p>
			</div>
		{/foreach}

	{/if}

</div>

<div id="annoncesEcole">

	{if isset($listeAnnonces.ecole)}

		{foreach from=$listeAnnonces.ecole key=id item=uneAnnonce}
			<div id="note_{$id}" class="lesAnnonces">
				{if ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure == Null)}
				<span id="span{$id}" class="pull-right">
					<button class="btn btn-danger pull-right lecture" data-id="{$id}" data-type="ecole" type="button">J'ai lu cette annonce</button>
				</span>
				{elseif ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure != Null)}
				<span class="pull-right dateLecture">Lu le {$uneAnnonce.dateHeure}</span>
				{/if}
				<h4 class="urgence{$uneAnnonce.urgence}">{$uneAnnonce.dateDebut}: {$uneAnnonce.objet}</h4>
				{$uneAnnonce.texte}
				<p><span  class="pull-right contact">Contact: {$uneAnnonce.proprietaire}</span></p>
			</div>
		{/foreach}

	{/if}

</div>
