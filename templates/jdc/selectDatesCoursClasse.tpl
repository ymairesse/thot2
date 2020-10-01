<form id="selectDatesCours" name="selectDateCours" method="POST" action="inc/jdc/jdc4PDF.inc.php">

    <div class="col-md-2 col-xs-6 form-group">
        <label for="dateStart">Début</label>
        <input type="text" name="dateStart" id="dateStart" value="{$smarty.now|date_format:'%d/%m/%Y'}" class="datepicker input-sm form-control">
    </div>

    <div class="col-md-2 col-xs-6 form-group">
        <label for="dateEnd">Fin</label>
        <input type="text" name="dateEnd" id="dateEnd" value="{$smarty.now|date_format:'%d/%m/%Y'}" class="datepicker form-control input-sm">
    </div>

    <div class="col-md-3 col-xs-12 form-group">
        <label for="coursGrpClasse">Infos sur</label>
        <select class="form-control input-sm" name="coursGrpClasse" id='coursGrpClasse'>
            <option value="all" {if !(isset($coursGrp))} selected{/if}>Tous les cours</option>
            <option value="{$classe}">Notes pour la classe {$classe}</option>
            {foreach from=$listeCours key=unCoursGrp item=data}
                <option value="{$unCoursGrp}">
                    {$data.dataCours.libelle} {$data.dataCours.nbheures}h ({$data.profs.nom})</option>
            {/foreach}
        </select>
    </div>

    <div class="col-md-3 col-xs-10 form-group">
        <label for="categories">Catégories</label>
        <select class="form-control input-sm" name="categories" id="categories">
            <option value="all">Toutes les catégories</option>
            {foreach from=$categories key=id item=data}
            <option value="{$data.idCategorie}">{$data.categorie}</option>
            {/foreach}
        </select>
    </div>

    <div class="col-md-2 col-xs-2">

        <button type="submit" class="btn btn-success btn-block" id="btn-printPdf"><i class="fa fa-file-pdf-o fa-lg"></i> </button>

    </div>

</form>
