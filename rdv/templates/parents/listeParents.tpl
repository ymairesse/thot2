{foreach from=$listeParents key=userName item=data}

    <div class="col-md-6 col-sm-12">
        <h4>Parent {$data@iteration}:</h4>
        <p>Nom d'utilisateur: <strong>{$userName}</strong></p>
        <p>Nom: <strong>{$data.formule} {$data.nom}</strong></p>
        <p>Prénom: <strong>{$data.prenom}</strong></p>
        <p>Adresse mail:<strong> <a href="mailto:{$data.mail}">{$data.mail}</a></strong></p>
        <p>Parenté: <strong>{$data.lien}</strong></p>
    </div>

{/foreach}
