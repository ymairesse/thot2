<link href="css/filetree.css" type="text/css" rel="stylesheet">

<style media="screen">

i.fav.actif, i.favori.actif {
    color: orange;
}

i.fav, i.favori {
    color: #ccc;
    cursor: pointer;
}

</style>

<ul class="nav nav-pills">
    <li class="active">
        <a data-toggle="tab" href="#eleves">{$identite.prenom} {$identite.nom|substr:0:1}.
        <span class="badge">{$listeDocs.eleves|@count|default:0}</span></a>
    </li>

    <li>
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

    <li class="pull-right bg-danger">
        <a data-toggle="tab" href="#favoris">Favoris
        <span class="badge favori">{$listeFavoris|@count|default:0}</span></a>
    </li>

</ul>

<div class="tab-content">

    <div id="eleves" class="tab-pane fade in active" style="min-height:30em; overflow:auto">
        <h3>Les documents pour {$identite.prenom}</h3>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Commentaire</th>
                    <th>Professeur</th>
                    <th>Fav.</th>
                </tr>
            </thead>
            {if isset($listeDocs.eleves)}
                {foreach from=$listeDocs.eleves key=fileId item=data}
                    {assign var=shareId value=$data.shareId}
                    <tr data-shareid="{$data.shareId}">
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
                        <td><i class="fa fa-star fav{if isset($listeFavoris.$shareId)} actif{/if}"></i></td>
                    </tr>
                {/foreach}
            {/if}
        </table>
    </div>

    <div id="classe" class="tab-pane fade" style="min-height:30em; overflow:auto;">
        <h3>Les documents pour ma classe</h3>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Classe</th>
                    <th>Document</th>
                    <th>Commentaire</th>
                    <th>Professeur</th>
                    <th>Fav.</th>
                </tr>
            </thead>
            {if isset($listeDocs.classes)}
                {foreach from=$listeDocs.classes key=fileId item=data}
                {assign var=shareId value=$data.shareId}
                <tr data-shareid="{$data.shareId}">
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
                    <td><i class="fa fa-star fav{if isset($listeFavoris.$shareId)} actif{/if}"></i></td>
                </tr>
                {/foreach}
            {/if}
        </table>
    </div>



    <div id="cours" class="tab-pane fade" style="min-height:30em; overflow:auto;">
        <h3>Les documents pour mes cours</h3>

        {if isset($listeDocs.coursGrp)}

        <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
            {foreach from=$listeDocs.coursGrp key=libelle item=dataBranche name=boucle}
            <li {if $smarty.foreach.boucle.iteration == 1 }class="active"{/if}>
	        <a href="#{$libelle|regex_replace:'/[^a-zA-Z]/':''}" data-toggle="tab">
                    {$libelle} <span class="badge">{$listeDocs.coursGrp.$libelle|count}</span>
                </a>
	        </li>
            {/foreach}
        </ul>

        <div class="tab-content">

            {foreach from=$listeDocs.coursGrp key=libelle item=dataBranche name=boucle}

                <div class="tab-pane{if $smarty.foreach.boucle.iteration == 1} active{/if}"
	             id="{$libelle|regex_replace:'/[^a-zA-Z]/':''}">

                    <table class="table table-condensed">
                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Commentaire</th>
                                <th>Professeur</th>
                                <th>Fav.</th>
                            </tr>
                        </thead>

                        {foreach from=$dataBranche key=fileId item=dataDoc}
                        {assign var=shareId value=$data.shareId}
                        <tr data-shareid="{$dataDoc.shareId}">
                            <td>
                                {if $dataDoc.dirOrFile == 'file'}
                                <a href="download.php?type=pId&amp;fileId={$fileId}">{$dataDoc.fileName}</a>
				 {else}
                                <button type="button" class="btn btn-primary btn-xs btnFolder" data-fileid="{$fileId}" data-commentaire="{$dataDoc.commentaire}">
                                    <i class="fa fa-folder-open"></i> Dossier: {$dataDoc.commentaire|truncate:40}
                                </button>
                                {/if}
                            </td>
                            <td>{$dataDoc.commentaire}</td>
                            <td>{if $dataDoc.sexe == 'F'}Mme{else}M.{/if} {$dataDoc.prenom|substr:0:1}. {$dataDoc.nom}</td>
                            <td><i class="fa fa-star fav{if isset($listeFavoris.$shareId)} actif{/if}"></i>{$dataDoc.fav}</td>
                        </tr>
                        {/foreach}
                    </table>

                </div>
            {/foreach}

        </div>

        {/if}  {* isset($listeDocs.coursGrp) *}

    </div>

    <div id="niveau" class="tab-pane fade" style="min-height:30em; overflow:auto;">
        <h3>Les documents pour mon niveau d'études</h3>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Commentaire</th>
                    <th>Professeur</th>
                    <th>Fav.</th>
                </tr>
            </thead>
            {if isset($listeDocs.niveau)}
                {foreach from=$listeDocs.niveau key=fileId item=data}
                {assign var=shareId value=$data.shareId}
                <tr data-shareid="{$data.shareId}">
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
                    <td><i class="fa fa-star fav{if isset($listeFavoris.$shareId)} actif{/if}"></i></td>
                </tr>
                {/foreach}
            {/if}
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
                    <th>Fav.</th>
                </tr>
            </thead>
            {if isset($listeDocs.ecole)}
                {foreach from=$listeDocs.ecole key=fileId item=data}
                {assign var=shareId value=$data.shareId}
                <tr data-shareid="{$data.shareId}">
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
                    <td><i class="fa fa-star fav{if isset($listeFavoris.$shareId)} actif{/if}"></i></td>
                </tr>
                {/foreach}
            {/if}
        </table>
    </div>

    <div id="favoris" class="tab-pane fade" style="min-height:30em; overflow: auto;">
        <h3>Mes favoris</h3>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Commentaire</th>
                    <th>Professeur</th>
                    <th style="width:2em">Fav</th>
                </tr>
            </thead>
            <tbody id="favTable">
                {include file='files/favoris.tpl'}
            </tbody>

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

        $('.fav').click(function(){
            var star = $(this);
            var shareId = $(this).closest('tr').data('shareid');
            $.post('inc/files/favUnfav.inc.php', {
                shareId: shareId
            }, function(resultat){
                star.toggleClass('actif');
                $.post('inc/files/getListeFavs.inc.php',{
                }, function(resultat){
                    $('#favTable').html(resultat);
                    var nb = $('#favTable tr').length;
                    $('.badge.favori').text(nb);
                })
            })
        })

        // suppression d'un favori dans la liste
        $('#favoris').on('click', '.favori', function(){
            var ceci = $(this);
            var shareId = $(this).closest('tr').data('shareid');
            $.post('inc/files/favUnfav.inc.php', {
                shareId: shareId
            }, function(resultat){
                ceci.parent().closest('tr').remove();
                // élimination du favori dans l'onglet original
                $('table [data-shareid="' + shareId + '"]').find('.fav').removeClass('actif');
                var nb = $('#favTable tr').length;
                $('.badge.favori').text(nb);
            })
        })

        $(document).on('click', ".btnFolder", function() {
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

        $(document).on('click', 'tr', function(){
            $(this).toggleClass('active');
        })

        $("#treeview").on('click', '.dirLink', function(event) {
            $(this).next('.filetree').toggle('slow');
            $(this).closest('li').toggleClass('expanded');
        })
    })
</script>
