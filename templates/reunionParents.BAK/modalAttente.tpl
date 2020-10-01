<div class="modal fade" id="modalAttente" tabindex="-1" role="dialog" aria-labelledby="titleModalAttente" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php" method="POST" role="form" class="form-inline" name="formListeAttente" id="formListeAttente">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="titleModalAttente">Inscription en liste d'attente</h4>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-7 col-sm-12" id="modalTableauAttente" style="max-height: 20em; overflow: auto;">
                            <!-- ici la table des péroides de liste d'attente -->

                            <!-- ici la table des péroides de liste d'attente -->
                        </div>
                        <div class="col-md-5 col-sm-12">
                            <p>Dans quelle période seriez-vous disponible si une place se libérait?</p>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="btn-group pull-right">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Confirmer</button>
                    </div>
                </div>
            <input type="hidden" name="action" value="{$action}">
            <input type="hidden" name="mode" value="saveAttente">
            <input type="hidden" name="acronyme" id="modalAcronyme" value="">
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(document).ready(function(){

        $("formListeAttente").validate();

    })

</script>
