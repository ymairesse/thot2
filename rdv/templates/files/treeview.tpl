{function name=repertoire level=0}
<ul class="filetree level{$level}" {if ($level > 0)}style="display:none"{/if}>
    {foreach $data as $file}
        {if $file.type == 'folder'}
            <li class="folder expanded">
                <a href="javascript:void(0)"
                    class="dirLink"
                    data-dir="{if $level > 0}/{/if}{$file.path|escape:'htmlall'}/{$file.name|escape:'htmlall'}/"
                    data-nbfiles='{$file.items|@count}'>
                    {$file.name}
                </a>

                {repertoire data=$file.items level=$level+1}

            </li>
        {else}
            <li data-filename="{$file.name|escape:'htmlall'}"
                data-extension='{$file.ext}'
                data-path="/{$file.path|escape:'htmlall'}"
                data-size='{$file.size}'
                data-date='{$file.date}'
                class='file ext_{$file.ext} level{$level}'>
                <a href="download.php?type=pfN&amp;fileName={$file.path|escape:'htmlall'}/{$file.name|escape:'htmlall'}&amp;fileId={$fileId}">
                    {$file.name|escape:'htmlall'}
                </a>

            </li>
        {/if}
    {/foreach}
</ul>
{/function}


{repertoire data=$tree}
