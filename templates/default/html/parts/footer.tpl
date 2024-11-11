<!-- Подвал -->
<footer>
    <div class="wrap">

        <!-- Просмотренные товары -->
        {get_browsed_products var=browsed_products limit=4}
        {if $browsed_products}
            <div class="browsed_products_box">
                <div class="h2">Вы просматривали</div>
                <ul class="products gallerywide">
                    {foreach $browsed_products as $product}
                        {include file='parts/product_item.tpl'}
                    {/foreach}
                </ul>
            </div>
        {/if}


        {* Выбираем в переменную $last_posts последние записи *}
        {get_posts var=last_posts limit=5 random=on}

        {if $last_posts}
            <div class="title-wrap">
                <h3>Полезная информация</h3>
                <span> → <a href="/blog">все статьи</a></span>
            </div>
            <div class="posts-content">

                {foreach $last_posts as $post}
                    <div data-post="{$post->id}">
                        <a href="/blog/{$post->url}" class="title">{$post->name|escape}</a>
                        <div>{$post->annotation}</div>
                    </div>
                {/foreach}
            </div>
        {/if}
    </div>
</footer>