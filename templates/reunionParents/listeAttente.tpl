<table class="table table-condensed">
    <thead>
        <tr>
            <th>Période</th>
            <th>Veuillez choisir</th>
        </tr>
    </thead>
    <tbody>

        {foreach from=$listePeriodes key=periode item=limites}
            <tr class="attente{$periode}">
                <td>Entre {$limites.min} et {$limites.max}</td>
                <td style="text-align:center">
                    <input type="radio" class="periode" name="periode" value="{$periode}" required>
                </td>
            </tr>
        {/foreach}

    </tbody>
</table>

<input type="hidden" name="idRP" value="{$idRP}">
<input type="hidden" name="acronyme" value="{$acronyme}">
