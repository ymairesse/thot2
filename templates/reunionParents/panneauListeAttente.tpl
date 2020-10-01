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
                {foreach from=$listeAttente key=n item=data}
                {assign var=periode value=$data.periode}
                <tr class="attente{$periode}" data-acronyme="{$data.acronyme}" data-periode="{$periode}">
                    <td>{$listePeriodes.$periode.min} à {$listePeriodes.$periode.max}</td>
                    {assign var=acronyme value=$data.acronyme}
                    {assign var=sexe value=$data.sexe}

                    <td>{if $sexe == 'F'}Mme{else}M.{/if} {$data.prenomProf} {$data.nomProf}</td>
                    <td>{$data.formule} {$data.prenomParent} {$data.nomParent}</td>
                    <td>

                        <button type="button"
                            class="btn btn-danger btn-xs delAttente"
                            data-nomprof="{if $sexe == 'F'}Mme{else}M.{/if} {$data.prenomProf} {$data.nomProf}"
                            data-acronyme="{$acronyme}"
                            data-periode="{$periode}"
                            data-heures="{$listePeriodes.$periode.min} à {$listePeriodes.$periode.max}"
                            title="Effacer"><i class="fa fa-undo"></i></button>

                    </td>
                </tr>
                {/foreach}
            </tbody>

        </table>

    </div>
</div>
