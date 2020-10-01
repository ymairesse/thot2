<div id="modalDislike" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalDislikeLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="modalDislikeLabel">Problème</h4>
      </div>
      <div class="modal-body">
		  <p class="notice">Ce formulaire ne sert qu'à indiquer des erreurs ou des imprécisions dans le journal de classe électronique.<br>
		  Pour toute autre question, contacte ton professeur.</p>

        <div class="form-group">
			<label for="dislikeReason">Je promets de rester courtois-e et poli-e et je signale une erreur ou une imprécision</label>
			<input type="text" name="commentaire" id='commentaire' value="" class="form-control" maxlength="80">
        	<div class="help-block">
        		Explique en quelques mots le problème que tu remarques dans cette note au JDC (max 80 caractères)
        	</div>
        </div>

      </div>
      <div class="modal-footer">
		  <div class="btn-group">
			<button type="button" class="btn btn-default" data-dismiss="modal">Oups, je n'ai rien dit...</button>
		  	<button type="button" class="btn btn-danger" id="confirmDislike">Je confirme <i class="fa fa-thumbs-down"></i></button>
		  </div>
      </div>
    </div>
  </div>
</div>
