<div style="max-height: 30em; overflow: auto">

    <table class="table table-condensed">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Début de charge</th>
                <th>Fin de charge</th>
            </tr>
        </thead>

        {foreach from=$listeCharges key=matricule item=data}
        <tr{if isset($data.selected)} class="selected"{/if}>
            <td>{$data.nom} {$data.prenom}</td>
            <td>{$data.dateDebut}</td>
            <td>{$data.dateFin}</td>
        </tr>
        {/foreach}

    </table>

</div>

<p class="discret selected">Élève actuellement en charge</p>
