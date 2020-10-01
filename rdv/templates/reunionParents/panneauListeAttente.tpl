<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">Liste d'attente</h3>
    </div>
    <div class="panel-body">
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Période</th>
                    <th>Nom du professeur</th>
                    <th>Demandé par</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$listeAttente item=data}
                {assign var=periode value=$data.periode}
                <tr class="attente{$periode}">
                    <td>{$listePeriodes.$periode.min} à {$listePeriodes.$periode.max}</td>
                    {assign var=acronyme value=$data.acronyme} {assign var=sexe value=$listeEncadrement.$acronyme.sexe}
                    <td>{if $sexe == 'F'}Mme{else}M.{/if} {$listeEncadrement.$acronyme.prenom} {$listeEncadrement.$acronyme.nom}</td>
                    <td>{$data.formule} {$data.prenomParent} {$data.nomParent}</td>
                    <td>
                        {if ($ACTIVE == 1) && ($OUVERT == 1)}
                        <button type="button" class="btn btn-danger btn-xs delAttente" data-nomprof="{if $sexe == 'F'}Mme{else}M.{/if} {$listeEncadrement.$acronyme.prenom} {$listeEncadrement.$acronyme.nom}" data-acronyme="{$acronyme}" data-periode="{$periode}" data-heures="{$listePeriodes.$periode.min} à {$listePeriodes.$periode.max}"
                            title="Effacer"><i class="fa fa-undo"></i></button>
                        {else} &nbsp; {/if}
                    </td>
                </tr>
                {/foreach}
            </tbody>

        </table>

    </div>
</div>
