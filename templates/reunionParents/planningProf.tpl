<table class="table table-condensed">
    <thead>
        <tr>
            <th>Heure</th>
            <th>Veuillez choisir une période</th>
        </tr>
    </thead>
    <tbody>
    {foreach from=$planningProf key=idRV item=unRV}
        <tr{if $unRV.dispo == 0} class="indisponible"{/if}>
            <td>{$unRV.heure}</td>
            <td style="text-align:center">{if $unRV.dispo == 1}
                <input type="radio"
                name="idRV"
                value="{$idRV}"
                class="radioRv" required>
                {else}
                indisponible
                {/if}
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>
