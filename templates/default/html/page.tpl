<div id="path">
	<ul itemscope itemtype="https://schema.org/BreadcrumbList">
		<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<a href="/" itemprop="item">
				<span itemprop="name">Главная</span>
				<meta itemprop="position" content="1" />
			</a> →
		</li>

		<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<span itemprop="name">{$page->h1|escape}</span>
			<meta itemprop="position" content="2" />
		</li>
	</ul>
</div>

<!-- Заголовок страницы -->
<h1>{$page->h1|escape}</h1>

{if $user|user_access:pages AND $page->id}
	<div class="admin_edit">
		<a href="/agmin?view=PageAdmin&id={$page->id}" title="Редактировать страницу">Редактировать
			страницу</a>
	</div>
{/if}

<!-- Тело страницы -->
{$page->body}