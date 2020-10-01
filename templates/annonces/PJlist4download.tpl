{if $listePJ != Null}
<ul class="list-unstyled">
{foreach $listePJ as $fileId => $fileName}
    <li><i class="fa fa-paperclip"></i> <a href="download.php?type=pId&fileId={$fileId}" target="_blank">{$fileName}</a></li>
{/foreach}
</ul>
<p class="discret">Ces documents sont aussi accessibles dans les ISND/docs</p>
{/if}
