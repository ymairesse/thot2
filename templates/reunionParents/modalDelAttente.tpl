<div class="modal fade" id="modalDelAttente" tabindex="-1" role="dialog" aria-labelledby="titleDelAttente" aria-hidden="true">
    <div class="modal-dialog">
        <form action="index.php" method="POST" role="form" class="form-vertical">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="titleDelAttente">Sortie de la liste d'attente</h4>
                </div>
                <div class="modal-body alert alert-danger">

                    <p>Souhaitez-vous vraiment supprimer votre demande de rendez-vous en liste d'attente de <strong id="modalHeures"></strong> avec <strong id="modalNomProfAttente"></strong>?</p>
                    <p>L'effacement est définitif.</p>
                    <p><i class='fa fa-warning fa-2x'></i> Vous n'aurez aucune priorité pour reprendre place dans la liste d'attente pour ce professeur.</p>

                </div>
                <div class="modal-footer">
                    <div class="btn-group pull-right">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                        <button type="button"
                            id="confDelAttente"
                            data-idrp=""
                            date-acronyme=""
                            date-periode=""
                            class="btn btn-danger">
                            Confirmer
                        </button>
                    </div>
                </div>
            </div>


        </form>
    </div>
</div>
