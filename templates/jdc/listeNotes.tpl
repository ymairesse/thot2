<table class="table table-condensed">
    <thead>
        <tr>
            <th>Date</th>
            <th>Heure</th>
            <th>Cours</th>
            <th>Note</th>
            <th style="width:3em;">&nbsp;</th>
        </tr>
    </thead>
    {foreach from=$listeNotes key=id item=data}
        <tr data-id="{$id}">
            <td>{$data.startDate}</td>
            <td>{$data.startHeure|truncate:5:''}</td>
            <td title="{$data.destinataire}">{$data.libelle}</td>
            <td>{$data.enonce|truncate:150:'...'}</td>
            <td>{if $data.enonce|count_characters > 150}
                <button title="Voir tout" type="button" class="btn btn-primary btn-xs btn-show">&nbsp;<i class="fa fa-eye"></i>&nbsp;</button>
                {/if}
            </td>
        </tr>
    {/foreach}
</table>
