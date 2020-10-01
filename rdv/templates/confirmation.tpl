{if $nb > 0}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Confirmation de votre demande de RV</h3>
    </div>
    <div class="panel-body">

        <div class="alert alert-success">
            <p><i class="fa fa-smile-o fa-2x"></i> Votre demande de rendez-vous pour la pré-inscription de {$rv.prenom} {$rv.nom} est maintenant confirmée.</p>
            <p>Vous serez reçu-e le <strong>{$rv.date} à {$rv.heure}</strong> à l'Institut des Sœurs de Notre-Dame, rue de Veeweyde 40 à 1070 Bruxelles.</p>
            <p>Un mail automatique de confirmation vous a été adressé.</p>
        </div>
    </div>
    <div class="panel-footer">
        <p>En cas de problème, contactez <a href="mailto:admin@isnd.be">admin@isnd.be</a> en indiquant l'adresse mail utilisée lors de votre demande.</p>
    </div>
</div>
{else}

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Confirmation de votre demande de RV</h3>
    </div>
    <div class="panel-body">
        <div class="alert alert-danger">
            <p><i class="fa fa-warning fa-4x"></i> Attention! Nous n'avons pas pu confirmer votre demande de rendez-vous.</p>
            <p>Voici quelques hypothèses</p>
            <ul>
                <li>Vous avez déjà confirmé cette demande de rendez-vous</li>
                <li>Votre "token" {$token} provenant du mail de confirmation est incorrect (l'avez-vous bien recopié?)</li>
                <li>Vous avez dépassé le délai de péremption de <strong>4 heures</strong> après la demande de rendez-vous</li>
            </ul>
            <p><a href="http://isnd.be/thot/rdv">Veuillez faire une nouvelle demande de rendez-vous.</a></p>
        </div>
    </div>
    <div class="panel-footer">
        <p>En cas de problème persistant, contactez <a href="mailto:admin@isnd.be">admin@isnd.be</a> en indiquant l'adresse mail utilisée lors de votre demande.</p>
    </div>
</div>


{/if}
