<script type="text/javascript" src="ckeditor/ckeditor.js"></script>

<h3>Contacter un professeur</h3>

<form action="index.php" method="POST" name="contact" id="contact" role="form" class="form-vertical">

    <div class="row">

        <div class="col-md-4 col-sm-12">
            <div class="panel panel-warning">

                <div class="panel-heading">
                    <p>Merci de vérifier vos coordonnées</p>
                </div>
                <div class="panel-body">
                    <p>Vous êtes:</p>
                    <p>
                        <strong>{$user.formule} {$user.prenom} {$user.nom}</strong>
                    </p>
                    <p>Votre adresse mail est:</p>
                    <p>
                        <strong><a href="mailto:{$user.mail}">{$user.mail}</a></strong>
                    </p>
                    <p>Une copie de votre message sera envoyée à cette adresse.</p>

                    <p>Votre enfant est </p>
                    <p>
                        <strong>{$user.prenomEl} {$user.nomEl}</strong> en classe de
                        <strong>{$user.classe}</strong>
                    </p>


                </div>

            </div>
            <!-- panel -->

        </div>
        <!-- col-md-... -->

        <div class="col-md-8 col-sm-12">

            <div class="row">

                <div class="col-md-8">

                    <select name="acronyme" id="acronyme" class="form-control fa-select">
                        <option value="">Veuillez choisir le destinataire</option>
                        {foreach from=$listeProfs item=data}
                        {assign var=acronyme value=$data.acronyme}
                        {assign var=coursGrp value=$data.coursGrp}
                        <option value="{$acronyme}">
                            {$data.adresse} {$data.prenom} {$data.nom} -> {$data.libelle} {$data.nbheures}h
                        </option>
                        {/foreach}
                        {foreach from=$listeEducs key=acronyme item=data}
                        <option value="{$acronyme}">
                            {$data.adresse} {$data.prenom} {$data.nom} -> éducateur référent de {$data.groupe}
                        </option>
                        {/foreach}
                    </select>
                </div>

                <div class="col-md-2">
                    <i class="fa fa-graduation-cap"></i> Titulaire
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-paper-plane"></i> Envoyer</button>
                </div>

            </div>

            <p>
                <input type="text" name="objet" id="objet" placeholder="Objet" class="form-control" value="À propos de {$user.prenomEl} {$user.nomEl} de {$user.classe}">
            </p>
            <textarea name="texte" id="texte" cols="30" rows="10" class="ckeditor form-control" placeholder="Frappez votre texte ici"></textarea>

        </div>
        <!-- col-md-... -->

    </div>
    <!-- row -->

    <input type="hidden" name="userName" value="{$user.userName}">
    <input type="hidden" name="action" value="{$action}">
    <input type="hidden" name="mode" value="envoyer">
</form>

<script type="text/javascript">
    $(document).ready(function() {

        $("#contact").validate({
            ignore: [],
            rules: {
                acronyme: 'required',
                objet: 'required',
                texte: {
                    required: function() {
                        CKEDITOR.instances.texte.updateElement();
                    }
                }
            }
        });

    })
</script>
