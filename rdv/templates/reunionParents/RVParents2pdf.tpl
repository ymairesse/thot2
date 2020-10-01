<page backtop="20mm" backbottom="20mm" backleft="10mm" backright="10mm">
    <page_header>

    </page_header>

<p><img src="../../images/logo1.jpg" alt="Logo École" style="float:right; width: 100px">{$entete} </p>

<h1>Réunion de parents du {$date}</h1>

    <h2>{$eleve.prenom} {$eleve.nom} [{$eleve.groupe}]</h2>

    <!-- Les RV fixées -->
    <h3>Liste des rendez-vous</h3>
    {if (isset($listeRV))}
    <table class="table table-condensed" border="0" cellpadding="5" cellspacing="5" width="100%">

        <thead>
            <tr>
                <th style="width:20%">Heure</th>
                <th style="width:30%">Professeur</th>
                <th style="width:10%">Local</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$listeRV item=dataRV}
            <tr>
                <td>{$dataRV.heure}</td>
                <td>{if ($dataRV.sexe == 'F')}Mme{else}M.{/if} {$dataRV.prenom} {$dataRV.nom}</td>
                <td>{if isset($dataRV.local)}{$dataRV.local}{else}???{/if}</td>
            </tr>
            {/foreach}
        </tbody>

    </table>
    {else}
    <p>Aucun rendez-vous n'est fixé.</p>
    {/if}

    <!-- La liste d'attente -->
    <h3>Liste d'attente</h3>
    {if isset($listeAttente)}
    <table class="table table-condensed" border="0" cellpadding="5" cellspacing="5" width="100%">
        <thead>
            <tr>
                <th>Période</th>
                <th>Professeur</th>
            </tr>
        </thead>
        <tbody>
        {foreach from=$listeAttente item=data}

            <tr>
                {assign var=periode value=$data.periode}
                <td>{$listePeriodes.$periode.min} - {$listePeriodes.$periode.max}</td>
                <td>{if ($dataRV.sexe == 'F')}Mme{else}M.{/if} {$dataRV.prenom} {$dataRV.nom}</td>
            </tr>

        {/foreach}
        </tbody>
    </table>
    {else}
    <p>Aucune demande de rendez-vous en liste d'attente.</p>
    {/if}

    <h3>Liste des cours de {$eleve.prenom}</h3>
    <table class="table table-condensed">
        <thead>
            <tr>
                <th>Cours</th>
                <th>Nom</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$listeProfs item=unProf}
            <tr>
                <td>{$unProf.libelle} {$unProf.nbheures}h</td>
                <td>{if $unProf.sexe == 'F'}Mme{else}M.{/if} {$unProf.nom} {$unProf.prenom}</td>
            </tr>
            {/foreach}
        </tbody>
    </table>

    <h3>Éducateur(s) référent(s)</h3>

    <page_footer>
    Date d'impression: {$smarty.now|date_format:'%d/%m/%Y %H:%M'}
    </page_footer>

</page>
