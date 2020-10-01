<div class="modal fade" id="modalRV" tabindex="-1" role="dialog" aria-labelledby="titleModalRv" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php" method="POST" role="form" class="form-vertical" name="formRV" id="formRV">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="titleModalRv">Veuiller sélectionner une heure de RV</h4>
                </div>
                <div class="modal-body row">

                    <div class="col-md-6 col-sm-12" id="modalTableRV" style="max-height: 20em; overflow: auto;">
                        <!-- ici la table des RV possibles -->

                        <!-- ici la table des RV possibles -->
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <h4>Information</h4>
                        <p>Les périodes marquées <span class="indisponible">en grisé</span> ne sont pas disponibles.</p>
                        <p>Si un professeur n'est plus du tout disponible, il vous est possible de vous inscrire en liste d'attente.</p>
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="btn-group pull-right">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Confirmer</button>
                    </div>
                </div>
                <input type="hidden" name="action" value="{$action}">
                <input type="hidden" name="mode" value="saveRV">
                <input type="hidden" name="date" value="{$date}">
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        $("formRV").validate();

    })
</script>
