{foreach from=$listeFavoris key=shareId item=data}
    <tr data-shareid="{$shareId}">
        <td>
        {if $data.dirOrFile == 'file'}
            <a href="download.php?type=pId&fileId={$data.fileId}">{$data.fileName}</a>
        {else}
            <button type="button" class="btn btn-primary btn-xs btnFolder" data-fileid="{$data.fileId}" data-commentaire="{$data.commentaire}">
            <i class="fa fa-folder-open"></i> Dossier: {$data.commentaire}
            </button>
        {/if}
        </td>
        <td>{$data.commentaire}</td>
        <td>{$data.nomProf}</td>
        <td><i class="fa fa-star favori actif"></i></td>
    </tr>
{/foreach}
