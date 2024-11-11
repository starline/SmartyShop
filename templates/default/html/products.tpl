<!-- Хлебные крошки -->
<div id="path">
	<ul itemscope itemtype="https://schema.org/BreadcrumbList">

		<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<a href="/" itemprop="item">
				<span itemprop="name">Главная</span>
				<meta itemprop="position" content="1">
			</a>
		</li>

		{$item_position = 2}
		{if $category}
			{foreach $category->path as $cat}
				<li>→</li>
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="/{$cat->url}" itemprop="item">
						<span itemprop="name">{$cat->name|escape}</span>
						<meta itemprop="position" content="{$item_position++}">
					</a>
				</li>
			{/foreach}
		{/if}

		{if $brand}
			<li>→</li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="/{$cat->url}/{$brand->url}" itemprop="item">
					<span itemprop="name">{$brand->name|escape}</span>
					<meta itemprop="position" content="{$item_position++}">
				</a>
			</li>
		{elseif $keyword}
			<li>→</li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<span itemprop="name">Поиск</span>
				<meta itemprop="position" content="{$item_position++}">
			</li>
		{/if}
	</ul>
</div>


<script>
	window.dataLayer = window.dataLayer || [];

	{if $variants_sku}
		dataLayer.push({
			'event': 'DynamicRemarketing',
			'dynamicParams': {
				'ecomm_prodid': ['{$variants_sku|join:"','"}'],
				'ecomm_pagetype': 'category',
				'ecomm_category': '{$category->name|escape}'
			}
		});

		window.dataLayer.push({
			'ecommerce': null
		});

		window.dataLayer.push({
			'event': 'view_item_list',
			'ecommerce': {
				'currencyCode': '{$currency->code}',  
				'impressions': [
					{foreach $products as $p}
						{
							'id': '{$p->variant->sku|escape}',
							'name': '{$p->name|escape}',
							'variant': '{$p->variant->name|escape}',
							'price': '{$p->variant->price}',
							'position': {$p@index},
							'category': '{$p->category_name|escape}',
							'list': '{$category->path[0]->name|escape}'
						},
					{/foreach}
				]
			}
		});
	{/if}
</script>



{if $category}
	<div id="catalog_menu">
		<ul>
			{foreach $category->path[0]->subcategories as $c}
				{if $c->visible}
					<li class="category_main">
						<a {if $category->id == $c->id}class="selected" {/if} href="{$c->url}"
							data-category="{$c->id}">{$c->name|escape}</a>
						{if $c->subcategories}
							<ul>
								{foreach $c->subcategories as $sc}
									<li>
										<a {if $category->id == $sc->id}class="selected" {/if} href="{$sc->url}"
											data-category="{$sc->id}">{$sc->name|escape}</a>
									</li>
								{/foreach}
							</ul>
						{/if}
					</li>
				{/if}
			{/foreach}
		</ul>
	</div>
{/if}


<div class="wrap_products {if !$category}wide{/if}">

	{* Заголовок страницы *}
	{if $seo->h1}
		<h1>{$seo->h1|escape}</h1>
	{elseif $keyword}
		<h1>Поиск {$keyword|escape}</h1>
	{else}
		<h1>{if $category->h1}{$category->h1|escape}{else}{$category->name|escape}{/if}</h1>
	{/if}


	{if $user|user_access:products_categories AND $category->id}
		<div class="admin_edit">
			<a href="/agmin?view=CategoryAdmin&id={$category->id}" title="Редактировать категорию">Редактировать
				категорию</a>
		</div>
	{/if}


	{if $current_page_num == 1 AND $category->annotation}
		<div class="category_annotation">
			{$category->annotation}
		</div>
	{/if}

	{* Описание бренда *}
	{if $current_page_num == 1}
		{$brand->description}
	{/if}

	{* Фильтр по свойствам *}
	{if $features}
		<table id="features">
			{foreach $features as $key=>$f}
				<tr>
					<td class="feature_name" data-feature="{$f->id}">
						{$f->name}:
					</td>
					<td class="feature_values">
						<a href="{url params=[$f->id=>null, page=>null]}" {if !$smarty.get.$key}class="selected" {/if}>Все</a>
						{foreach $f->options as $o}
							<a href="{url params=[$f->id=>$o->value, page=>null]}"
								{if $smarty.get.$key == $o->value}class="selected" {/if}>{$o->value|escape}</a>
						{/foreach}
					</td>
				</tr>
			{/foreach}
		</table>
	{/if}


	<!-- Каталог товаров -->
	{if $products}

		{* Сортировка *}
		{if $products|count > 0}
			<div class="sort">
				Сортировать по
				<a {if $sort=='position'} class="selected" {/if} href="{url sort=position page=null}">умолчанию</a>
				<a {if $sort=='price'} class="selected" {/if} href="{url sort=price page=null}">цене</a>
			</div>
		{/if}

		<ul class="products gallerywide catalog">
			{foreach $products as $product}
				{include file='parts/product_item.tpl'}
			{/foreach}
		</ul>

		{include file="parts/pagination.tpl"}

	{else}
		Товары не найдены
	{/if}

	{if $current_page_num == 1 AND $category->description}
		<div class="category_description">
			{$category->description}
		</div>
	{/if}

</div>