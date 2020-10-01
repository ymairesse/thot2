<div id="modalPrintJDC" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalPrintJDCLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
				<h4 class="modal-title" id="modalPrintJDCLabel">Impression du JDC</h4>
			</div>
			<div class="modal-body row">

				<form id="printForm">
					<div class="col-xs-6">
						<div class="form-group">
							<label for="modalDepuis">Depuis</label>
							<input type="text" name="from" class="datepicker form-control" value="">
							<div class="helpBlock">
								Date de début
							</div>
						</div>
					</div>
					<div class="col-xs-6">
						<div class="form-group">
							<label for="modalDepuis">Jusqu'à</label>
							<input type="text" name="to" class="datepicker form-control" value="">
							<div class="helpBlock">
								Date de fin
							</div>
						</div>
					</div>

					<div class="col-xs-12" id="listeCours">

					</div>


					<div class="col-xs-12">
						<div class="form-group">
							<label for="categories">Sélection des catégories</label>
							<select class="form-control" name="categories[]" id="categories" multiple>
								{foreach from=$categories key=idCategorie item=data}
									<option value="{$data.idCategorie}" selected>{$data.categorie}</option>
								{/foreach}
							</select>
							<div class="helpBlock">Maintenir une touche CTRL enfoncée pour choisir plusieurs catégories</div>
						</div>
					</div>

					<div class="clearfix"></div>
				</form>

			</div>
			<div class="modal-footer">
				<div class="btn-group">
					<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
					<button type="button" class="btn btn-primary" id="btnModalViewJDC">Générer la vue <i class="fa fa-eye fa-lg"></i></button>
				</div>
			</div>
		</div>
	</div>
</div>
