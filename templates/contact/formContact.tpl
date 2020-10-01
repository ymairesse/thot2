<link rel="stylesheet" href="../summernote/summernote.min.css">
<script src="../summernote/summernote.min.js"></script>
<script src="../summernote/lang/summernote-fr-FR.min.js"></script>

<h3>Contacter un professeur ou un éducateur</h3>

<form action="index.php" method="POST" name="contact" id="contact" role="form" class="form-vertical">

    <div class="row">

        <div class="col-md-4 col-sm-12">
            <div class="panel panel-warning">

                <div class="panel-heading">
                    {if $userType == 'parent'}
                        <p>Merci de vérifier vos coordonnées</p>
                    {else}
                        <p style="color:red">Merci de ne pas abuser de ce mode de communication. <strong>Beaucoup de professeurs ont plus de 100 élèves</strong>. Ils pourraient donc ne pas avoir la possibilité de répondre à chacun.</p>
                    {/if}
                </div>
                <div class="panel-body">

                    {if $userType == 'parent'}
                        <p>Vous êtes: </p>
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
                    {else}

                        <p>
                            Tu es <strong>{$user.prenom} {$user.nom} de {$user.groupe}</strong>
                        </p>
                        <p>Ton adresse mail est:</p>
                        <p>
                            <strong><a href="mailto:{$user.mail}">{$user.mail}</a></strong>
                        </p>
                        <p>
                            Une copie de ton message sera envoyée à cette adresse. <br>
                            Le professeur ou l'éducateur répondra à cette adresse.
                        </p>
                        <p>Pour t'y connecter: <a href="http://mail.isnd.be" target="_blank">http://mail.isnd.be</a> (Roundcube) ou <br>
                        <a href="https://isnd.be/mail" target="_blank">https://isnd.be/mail</a> (Rainloop)<br>
                        en indiquant <strong>Ton adresse mail complète</strong> comme indiqué ci-dessus et ton mot de passe.</p>

                    {/if}
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
                            {$data.adresse} {$data.prenom} {$data.nom} -> {if $data.sexe == 'F'}Éducatrice référente{else}Éducateur référent{/if} de {$data.groupe}
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
                <input type="text" name="objet" id="objet" placeholder="Objet" class="form-control"
                    value="{if $userType == 'parent'}À propos de {$user.prenomEl} {$user.nomEl} de {$user.classe}
                        {else}Une question de {$user.prenom} {$user.nom} de {$user.groupe}{/if}">
            </p>
            <textarea name="texte" id="texte" cols="30" rows="10" class="ckeditor form-control" placeholder="Frappez votre texte ici"></textarea>

        </div>
        <!-- col-md-... -->

    </div>
    <!-- row -->
    {if $userType == 'eleve'}
        <input type="hidden" name="userName" value="{$user.user}">
        {else}
        <input type="hidden" name="userName" value="{$user.userName}">
    {/if}
    <input type="hidden" name="action" value="{$action}">
    <input type="hidden" name="mode" value="envoyer">
</form>

<script type="text/javascript">


function sendFile(file, el) {
	var form_data = new FormData();
	form_data.append('file', file);
	$.ajax({
		data: form_data,
		type: "POST",
		url: 'editor-upload.php',
		cache: false,
		contentType: false,
		processData: false,
		success: function(url) {
			$(el).summernote('editor.insertImage', url);
		}
	});
}

function deleteFile(src) {
	console.log(src);
	$.ajax({
		data: { src : src },
		type: "POST",
		url: 'inc/deleteImage.inc.php',
		cache: false,
		success: function(resultat) {
			console.log(resultat);
			}
	} );
	}

    $(document).ready(function() {

        $('#texte').summernote({
    		lang: 'fr-FR', // default: 'en-US'
    		height: null, // set editor height
    		minHeight: 150, // set minimum height of editor
    		focus: true, // set focus to editable area after initializing summernote
    		toolbar: [
    		  ['style', ['style']],
    		  ['font', ['bold', 'underline', 'clear']],
    		  ['font', ['strikethrough', 'superscript', 'subscript']],
    		  ['color', ['color']],
    		  ['para', ['ul', 'ol', 'paragraph']],
    		  ['table', ['table']],
    		  ['insert', ['link', 'picture', 'video']],
    		  ['view', ['fullscreen', 'codeview', 'help']],
    		],
    		maximumImageFileSize: 2097152,
    		dialogsInBody: true,
    		callbacks: {
    			onImageUpload: function(files, editor, welEditable) {
    				for (var i = files.length - 1; i >= 0; i--) {
    					sendFile(files[i], this);
    				}
    			},
    			onMediaDelete : function(target) {
    				deleteFile(target[0].src);
    			}
    		}
    	});

        $("#contact").validate({
            ignore: [],
            rules: {
                acronyme: 'required',
                objet: 'required',
                texte: 'required'
            },
            messages: {
                texte: 'Un texte ci-dessous, svp'
            }
        });

    })
</script>
