{assign var=postId value={$post.postId}}
<div class="postForum"
    id="post_{$postId}"
    data-postid="{$postId}"
    data-date="{$post.ladate}">
    {if $post.post == ''}
    <span class="supprime">Cette contribution a été supprimée</span>
    {else}
    {$post.post}
    {/if}
</div>
<div class="repondre" data-postid="{$post.postId}">
    <button type="button"
        style="color:#11036f"
        class="btn btn-success btn-xs btn-repondre btn-forum"
        data-postid="{$postId}"
        data-idcategorie="{$post.idCategorie}"
        data-idsujet="{$post.idSujet}"
        {if $post.post == ''}disabled{/if}>
        <i class="fa fa-arrow-up"></i> Répondre à {$post.user}
        </button>
        {if $matricule == $post.auteur}
        <button type="button"
            style="color:black"
            class="btn btn-warning btn-xs btn-edit btn-forum"
            data-postid="{$postId}"
            data-idcategorie="{$post.idCategorie}"
            data-idsujet="{$post.idSujet}"
            {if $post.post == ''}disabled{/if}>
            Modifier <i class="fa fa-arrow-up"></i>
        </button>
        <button type="button"
            class="btn btn-danger btn-xs btn-delPost btn-forum"
            data-postid="{$postId}"
            data-idcategorie="{$post.idCategorie}"
            data-idsujet="{$post.idSujet}"
            {if $post.post == ''}disabled{/if}>
        <i class="fa fa-times"></i>
        </button>
        {/if}

        {if $infoSujet.fbLike == 1}
        {* Boutons Like *}
        <span class="fbReactions" data-postid="{$postId}">
            <a class="FB_reactions"
                data-reactions-type="horizontal"
                data-postid="{$postId}"
                data-idcategorie="{$post.idCategorie}"
                data-idsujet="{$post.idSujet}"
                data-unique-id="{$post.idCategorie}:{$post.idSujet}:{$postId}"
                data-emoji-class="{$likes4user.$postId|default:''}">
            	<span>{$likes4user.$postId|default:'J\'aime'}</span>
            </a>
        </span>
        {* Statistiques Like *}
        <span class="listeFBlikes"
            data-postid="{$post.postId}"
            data-toggle="tooltip"
            data-unique-id="{$post.idCategorie}:{$post.idSujet}:{$post.postId}">

               {include file="forums/statsFbLikes.tpl"}

        </span>
        {/if}

     <span class="pull-right">{$post.ladate} - {$post.heure} {if $post.modifie == 1}<i class="discret">Modifié le {$post.dateModif} à {$post.heureModif}{/if}</i></span>
</div>
