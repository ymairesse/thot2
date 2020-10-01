<div class="modal fade" id="modalDelRv" tabindex="-1" role="dialog" aria-labelledby="titleDelRv" aria-hidden="true">
    <div class="modal-dialog">
        <form action="index.php" method="POST" role="form" class="form-vertical">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="titleDelRv">Suppression d'un rendez-vous</h4>
                </div>
                <div class="modal-body alert alert-danger">


                    <p>Souhaitez-vous vraiment supprimer ce rendez-vous de <strong id="modalHeure"></strong> avec <strong id="modalNomProfRV"></strong>?</p>
                    <p>L'effacement est définitif.</p>
                    <p><i class='fa fa-warning fa-2x'></i> Vous n'aurez aucune priorité pour reprendre rendez-vous à ce moment et avec ce professeur.</p>

                </div>
                <div class="modal-footer">
                    <div class="btn-group pull-right">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Confirmer</button>
                    </div>
                </div>
            </div>
            <input type="hidden" name="date" value="{$date}">
            <input type="hidden" name="id" value="" id="modalId">
            <input type="hidden" name="action" value="{$action}">
            <input type="hidden" name="mode" value="delRv">
        </form>
    </div>
</div>
