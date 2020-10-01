<h3>Changement du mot de passe</h3>
<div class="row">

    <div class="col-md-9 col-sm-12">
        {if isset($texte)}
        <p>Le mot de passe n'a pas été changé. Peut-être avez-vous repris l'ancien?</p>
        {else}
        <p>Félicitations! Le mot de passe a été changé avec succès.</p>
        <p>Vous pouvez maintenant vous connecter avec votre nom d'utilisateur et votre nouveau mot de passe.</p>
        {/if}

        <p class="pull-right">Pour revenir à la page d'accueil, <a href="../index.php">Cliquez ici</a></p>
    </div>

    <div class="col-md-3 col-sm-12">
        <img src="../images/hautThot.png" alt="Thot">
    </div>

</div>
