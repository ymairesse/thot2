<div class="modal fade" id="modalDelFile" tabindex="-1" role="dialog" aria-labelledby="titleModalDelFile" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="titleModalDelFile">Effacement d'un travail</h4>
      </div>
      <div class="modal-body">
          <div class="alert alert-danger">
              <p>Veuillez confirmer l'effacement définitif du document <strong id="modalDelFileName"></strong> <br>
              qui est un fichier de <strong id="modalFileSize"></strong></p>
          </div>
      </div>
      <div class="modal-footer">
          <div class="btn-group pull-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-danger" id="modalBtnDel" data-idtravail="">Effacer ce fichier</button>
        </div>
      </div>
    </div>
  </div>
</div>
