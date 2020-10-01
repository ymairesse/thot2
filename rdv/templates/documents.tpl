<link href="css/filetree.css" type="text/css" rel="stylesheet">

<ul class="nav nav-pills">
    <li class="active">
        <a data-toggle="tab" href="#classe">Ma classe
        <span class="badge">{$listeDocs.classes|@count|default:0}</span></a>
    </li>
    <li>
        <a data-toggle="tab" href="#cours">Mes cours
        <span class="badge">{$listeDocs.coursGrp|@count|default:0}</span></a>
    </li>
    <li>
        <a data-toggle="tab" href="#niveau">Mon niveau d'études
        <span class="badge">{$listeDocs.niveau|@count|default:0}</span></a>
    </li>
    <li>
        <a data-toggle="tab" href="#ecole">École
        <span class="badge">{$listeDocs.ecole|@count|default:0}</span></a>
    </li>

</ul>

<div class="tab-content">
    <div id="classe" class="tab-pane fade in active" style="min-height:30em; overflow:auto;">
        <h3>Les documents pour ma classe</h3>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Classe</th>
                    <th>Document</th>
                    <th>Commentaire</th>
                    <th>Professeur</th>
                </tr>
            </thead>
            {if isset($listeDocs.classes)}
                {foreach from=$listeDocs.classes key=fileId item=data}
                <tr>
                    <td>{$data.groupe}</td>
                    <td>
                        {if $data.dirOrFile == 'file'}
                        <a href="download.php?type=pId&amp;fileId={$fileId}">{$data.fileName}</a>
                        {else}
                        <button type="button" class="btn btn-primary btn-xs btnFolder" data-fileid="{$fileId}" data-commentaire="{$data.commentaire}">
                            <i class="fa fa-folder-open"></i> Dossier: {$data.commentaire|truncate:40}
                        </button>
                        {/if}
                    </td>
                    <td>{$data.commentaire}</td>
                    <td>{if $data.sexe == 'F'}Mme{else}M.{/if} {$data.prenom|substr:0:1}. {$data.nom}</td>
                </tr>
                {/foreach}
            {/if}
        </table>
    </div>


    <div id="cours" class="tab-pane fade" style="min-height:30em; overflow:auto;">
        <h3>Les documents pour mes cours</h3>

        {if isset($listeDocs.coursGrp)}

        <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
            {foreach from=$listeDocs.coursGrp key=libelle item=data name=boucle}
            <li>
                <a href="#{$libelle|replace:' ':'_'}" data-toggle="tab" {if $smarty.foreach.boucle.iteration==1 }class="active" {/if}>
                    {$libelle} <span class="badge">{$listeDocs.coursGrp.$libelle|count}</span>
                </a>
            </li>
            {/foreach}
        </ul>


        <div id="my-tab-content" class="tab-content">

            {foreach from=$listeDocs.coursGrp key=libelle item=dataBranche name=boucle}

            <div class="tab-pane{if $smarty.foreach.boucle.iteration == 1} active{/if}" id="{$libelle|replace:' ':'_'}">

                <table class="table table-condensed">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Commentaire</th>
                            <th>Professeur</th>
                        </tr>
                    </thead>

                    {foreach from=$dataBranche key=fileId item=dataDoc}
                    <tr>
                        <td>
                            {if $dataDoc.dirOrFile == 'file'}
                            <a href="download.php?type=pId&amp;fileId={$fileId}">{$dataDoc.fileName}</a> {else}
                            <button type="button" class="btn btn-primary btn-xs btnFolder" data-fileid="{$fileId}" data-commentaire="{$dataDoc.commentaire}">
                                <i class="fa fa-folder-open"></i> Dossier: {$dataDoc.commentaire|truncate:40}
                            </button>
                            {/if}
                        </td>
                        <td>{$dataDoc.commentaire}</td>
                        <td>{if $dataDoc.sexe == 'F'}Mme{else}M.{/if} {$dataDoc.prenom|substr:0:1}. {$dataDoc.nom}</td>
                    </tr>
                    {/foreach}
                </table>

            </div>

            {/foreach}

        </div>

        {/if}
        <!-- isset($listeDocs.coursGrp) -->

    </div>

    <div id="niveau" class="tab-pane fade" style="min-height:30em; overflow:auto;">
        <h3>Les documents pour mon niveau d'études</h3>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Commentaire</th>
                    <th>Professeur</th>
                </tr>
            </thead>
            {if isset($listeDocs.niveau)} {foreach from=$listeDocs.niveau key=fileId item=data}
            <tr>
                <td>
                    {if $data.dirOrFile == 'file'}
                    <a href="download.php?type=pId&amp;fileId={$fileId}">{$data.fileName}</a> {else}
                    <button type="button" class="btn btn-primary btn-xs btnFolder" data-fileid="{$fileId}" data-commentaire="{$data.commentaire}">
                        <i class="fa fa-folder-open"></i> Dossier: {$data.commentaire|truncate:40}
                    </button>
                    {/if}
                </td>
                <td>{$data.commentaire}</td>
                <td>{if $data.sexe == 'F'}Mme{else}M.{/if} {$data.prenom|substr:0:1}. {$data.nom}</td>
            </tr>
            {/foreach} {/if}
        </table>
    </div>

    <div id="ecole" class="tab-pane fade" style="min-height:30em; overflow:auto;">
        <h3>Les documents pour tous les élèves de l'école</h3>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Commentaire</th>
                    <th>Professeur</th>
                </tr>
            </thead>
            {if isset($listeDocs.ecole)} {foreach from=$listeDocs.ecole key=fileId item=data}
            <tr>
                <td>
                    {if $data.dirOrFile == 'file'}
                    <a href="download.php?type=pId&amp;fileId={$fileId}">{$data.fileName}</a>
                    {else}
                    <button type="button" class="btn btn-primary btn-xs btnFolder" data-fileid="{$fileId}" data-commentaire="{$data.commentaire}">
                        <i class="fa fa-folder-open"></i> Dossier: {$data.commentaire|truncate:40}
                    </button>
                    {/if}
                </td>
                <td>{$data.commentaire}</td>
                <td>{if $data.sexe == 'F'}Mme{else}M.{/if} {$data.prenom|substr:0:1}. {$data.nom}</td>
            </tr>
            {/foreach} {/if}
        </table>
    </div>

    <div id="e-docs" class="tab-pane fade" style="min-height:30em; overflow:auto;">
        <h3>Mes documents électroniques</h3>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Document</th>
                </tr>
            </thead>
            {if isset($listeEdocs.$matricule)} {foreach $listeEdocs.$matricule key=wtf item=data}
            <tr>
                <td>{$data.date}</td>
                <td>
                    {if $data.doc == 'pia'}
                    <a id="pia" href="javascript:void(0)">Plan individuel d'accompagnement (PIA)</a> {elseif $data.doc == 'competences'}
                    <a id="competences" href="javascript:void(0)">Rapport de compétences acquises</a> {/if}
                </td>
            </tr>
            {/foreach} {/if}

        </table>
    </div>

</div>
</table>
</div>

{include file="files/modaleDoc.tpl"}
{include file="files/modalTreeView.tpl"}

<iframe id="iframe" src="" style="display:none; width:0; height:0"></iframe>

<script type="text/javascript">

    $(document).ready(function() {

        $("#pia").click(function() {
            $.post('e-docs/inc/printDoc.inc.php', {
                    typeDoc: 'pia'
                },
                function(resultat) {
                    $("#edocReady").html(resultat);
                    $("#modaleDoc").modal('show');
                })
        })

        $("#competences").click(function() {
            $.post('e-docs/inc/printDoc.inc.php', {
                    typeDoc: 'competences'
                },
                function(resultat) {
                    $("#edocReady").html(resultat);
                    $("#modaleDoc").modal('show');
                })
        })

        $("#edocReady").click(function() {
            $("#modaleDoc").modal('hide');
        })

        $(".btnFolder").click(function() {
            var fileId = $(this).data('fileid');
            var titre = $(this).data('commentaire');
            $.post('inc/getTree.inc.php', {
                    fileId: fileId
                },
                function(resultat) {
                    $("#titleTreeview").text(titre);
                    $("#treeview").html(resultat);
                    $("#modalTreeView").modal('show');
                })
        })

        $("#treeview").on('click', '.dirLink', function(event) {
            $(this).next('.filetree').toggle('slow');
            $(this).closest('li').toggleClass('expanded');
        })
    })
</script>
