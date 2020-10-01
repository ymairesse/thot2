<div class="form-group">
    <label for="coursGrpClasse">Sélection des items</label>

    <select class="form-control" name="coursGrpClasse[]" id='coursGrpClasse' multiple>
        <option value="{$classe}" selected>Notes pour la classe {$classe}</option>
        {foreach from=$listeCours key=unCoursGrp item=data}
            <option value="{$unCoursGrp}" selected>
                {$data.dataCours.libelle} {$data.dataCours.nbheures}h ({$data.profs.nom})</option>
        {/foreach}
    </select>

    <div class="helpBlock">
        Maintenir une touche CTRL enfoncée pour une sélection multiple
    </div>

</div>
