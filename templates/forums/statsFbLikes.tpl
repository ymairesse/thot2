{if isset($FBstats.$postId)}

   {assign var=stats value=$FBstats.$postId}

    {include file="forums/singleLikeType.tpl"}

{/if}
