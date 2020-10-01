{if $remediationsEleve|@count > 0}
<div class="table-responsive">
    <table class="table table-condensed">
        <thead>
            <tr>
                <th>Date</th>
                <th>Heure</th>
                <th>Durée</th>
                <th>Matière</th>
                <th>Professeur</th>
                <th>Local</th>
                <th>&nbsp;</th>
            </tr>
        </thead>

    {foreach from=$remediationsEleve key=idOffre item=data}
        <tr class="pop {$data.type}"
            data-content="<h4>{$data.title}</h4>{$data.contenu}"
            data-container="body"
            data-placement="top"
            data-html="true">
            <td>{$data.date|truncate:5:''}</td>
            <td>{$data.heure}</td>
            <td>{$data.duree|truncate:5:''}</td>
            <td>{$data.title}</td>
            <td>{if $data.sexe == 'M'}M. {else}Mme {/if} {$data.initiale}. {$data.nom}</td>
            <td>{$data.local}</td>
            <td >
                {if $data.obligatoire == 1}
                <i class="fa fa-graduation-cap"
                    title="Inscription par le professeur">
                </i>
                {else}
                <i class="fa fa-user"
                    title="Inscription personnelle">
                </i>
                {/if}
            </td>
    </tr>
    {/foreach}
    </table>
</div>
{else}
<p class="demiavertissement">Pas de remédiation prévue</p>
{/if}

<script type="text/javascript">

    $('document').ready(function(){

        $(".pop").popover({
            trigger:'hover'
            });

    })

</script>
