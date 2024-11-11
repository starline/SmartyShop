<div id="path">
	<ul itemscope itemtype="https://schema.org/BreadcrumbList">
		<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<a href="/" itemprop="item">
				<span itemprop="name">Главная</span>
				<meta itemprop="position" content="1" />
			</a> →
		</li>

		<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<span itemprop="name">Все статьи</span>
			<meta itemprop="position" content="2" />
		</li>
	</ul>
</div>

<h1>{$seo->h1}</h1>

<ul id="blog">
	{foreach $posts as $post}
		<li>
			<h3><a data-post="{$post->id}" href="blog/{$post->url}">{$post->name|escape}</a></h3>
			<div class="date">{$post->date|date}</div>
			<p>{$post->annotation|strip_tags}</p>
		</li>
	{/foreach}
</ul>

{include file='parts/pagination.tpl'}