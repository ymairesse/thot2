<a href="javascript:void(0)"
    style="text-decoration: none"
    class="pop"
    title="<span class='emoji'><img src='js/fbLike/emojis/{$likeLevel}.svg' height='24px'> {ucfirst($likeLevel)}</span> <span class='badge badge-danger pull-right'>{$dataLike|@count}</span>"
    data-html="true"
    data-placement="top"
    data-container="body"
    data-emoji="{$likeLevel}"
    data-content="<ul class='list-unstyled fbLike emoji'><li>{$dataLike|implode:'</li><li>'}</li></ul>">

<img src="js/fbLike/emojis/{$likeLevel}.svg" height="24px">

</a>
