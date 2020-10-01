<fieldset>
    <legend><i class="fa fa-info-circle"></i> Commentaire du professeur</legend>
    <div style="max-height: 15em; overflow: auto; background-color:#ddd; border: 1px solid #55f; padding: 0.5em">
        {$evaluation.commentaire}
    </div>
</fieldset>

<fieldset>
    <legend><i class="fa fa-calculator"></i> Cotation du travail</legend>
    <table class="table table-condensed">
        <thead>
            <tr>
                <th>Compétence</th>
                <th>Cote</th>
                <th>Maximum</th>
            </tr>
        </thead>
        {foreach $evaluation.cotes key=idCompetence item=cotation}
        <tr>
            <td>{$listeCompetences.$idCompetence.libelle}</td>
            <td>{$cotation.cote}</td>
            <td> {$cotation.max}</li></td>
        </tr>
        {/foreach}
    </table>

</fieldset>
