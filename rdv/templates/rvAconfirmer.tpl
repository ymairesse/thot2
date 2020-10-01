<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Confirmation de votre demande de RV</h3>
    </div>
    <div class="panel-body">

        <p>Nous avons bien reçu votre demande de rendez-vous pour une pré-inscription au nom de <strong>{$rv.prenom} {$rv.nom}</strong>.</p>
        <p>Nous vous avons réservé la période que vous avez demandée: le <strong>{$rv.date}</strong> à <strong>{$rv.heure}</strong>.</p>

        <p>Nous venons de vous envoyer ces informations dans un courrier électronique à l'adresse <a href="mailto:{$rv.email}">{$rv.email}</a>. Est-ce correct?</p>

        <div class="alert alert-danger">
            <p><i class="fa fa-warning fa-2x"></i> Attention! Dans ce courrier électronique figure un lien qui vous permettra de confirmer votre demande de rendez-vous.</p>
            <p>VOUS DEVEZ OBLIGATOIREMENT ACTIVER CE LIEN.</p>
            <p>Au-delà d'un délai de 4 heures après votre demande (et l'envoi du courriel), le lien deviendra inactif et vous devrez faire une nouvelle demande.</p>
            <p>La période de rendez-vous sélectionnée sera alors à nouveau disponible pour une autre personne.</p>
        </div>
    </div>
    <div class="panel-footer">
        <p>Si vous n'avez pas reçu ce courriel, veuillez vérifier votre dossier des "Indésirables".</p>
        <p>En cas de problème, contactez <a href="mailto:admin@isnd.be">admin@isnd.be</a> en indiquant l'adresse mail utilisée lors de votre demande.</p>
    </div>
</div>
