<div id="path">
	<ul itemscope itemtype="https://schema.org/BreadcrumbList">
		<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<a href="/" itemprop="item">
				<span itemprop="name">Главная</span>
				<meta itemprop="position" content="1">
			</a> →
		</li>
		{$item_position = 2}
		{foreach $category->path as $path}
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="/{$path->url}" itemprop="item">
					<span itemprop="name">{$path->name}</span>
					<meta itemprop="position" content="{$item_position++}">
				</a> →
			</li>
		{/foreach}

		<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<span itemprop="name">{$product->name|escape}</span>
			<meta itemprop="position" content="{$item_position}">
		</li>
	</ul>
</div>

{$time_priceValidUntil = $smarty.now + 60*60*24*360}

<!-- https://schema.org/ -->
<script type="application/ld+json">
	[{
		"@context": "https://schema.org/",
		"@type": "Product",
		"name": "{$product->name|escape}",
		"image": [
			{foreach $product->images as $image}
				"{$image->filename|resize:1080:1080:w}"{if !$image@last},{/if}
			{/foreach}
		],
		"description": "Цена: {$product->variant->price|convert} {$currency->sign|escape}. {$product->annotation|strip_tags}",
		"sku": "{$product->variant->sku}",
		"brand": {
			"@type": "Brand",
			"name":"{$settings->site_name|escape}"
		},
		"aggregateRating": {
			"@type": "AggregateRating",
			"ratingValue": 5,
			"reviewCount": {if $comments|count>0}{$comments|count}{else}1{/if}
		},
		"offers": {
			"@type": "Offer",
			"url": "{url}",
			"priceCurrency": "{$currency->code}",
			"price": "{$product->variant->price}",
			"priceValidUntil": "{$time_priceValidUntil|date_format:'%Y-%m-%d'}",
			"itemCondition": "https://schema.org/UsedCondition",
			"availability": "https://schema.org/InStock",
			"seller": {
				"@type": "Organization",
				"name": "{$settings->company_name|escape}"
			}
		}
	}, {
		"@context": "https://schema.org",
		"@type": "WebSite",
		"url": "{$config->root_url}",
		"potentialAction": {
			"@type": "SearchAction",
			"target": "{$config->root_url}/search/{literal}{search_term_string}{/literal}",
			"query-input": "required name=search_term_string"
		}
	}];
</script>


<script>
	window.dataLayer = window.dataLayer || [];
	window.dataLayer.push({
		'event': 'DynamicRemarketing',
		'dynamicParams': {
			'ecomm_pagetype': 'product',
			'ecomm_prodid': '{$product->variant->sku|escape}',
			'ecomm_totalvalue': '{$product->variant->price}',
			'ecomm_category': '{$path->name|escape}'
		}
	});

	window.dataLayer.push({
		'ecommerce': null
	});

	window.dataLayer.push({
		'event': 'view_item',
		'ecommerce': {
			'detail': {
				'actionField': {
					'list': 'Product Page'
				},
				'products': [{
					'name': '{$product->name|escape}{if $product->variant->name} - {$product->variant->name|escape}{/if}',
					'id': '{$product->variant->sku|escape}',
					'price': '{$product->variant->price}',
					'category': '{$category->name|escape}'
					{if $product->variant->name}
						,'variant': '{$product->variant->name|escape}'
					{/if}
				}]
			}
		}
	});

	let currency_code = '{$currency->code}';
</script>


<h1 data-product="{$product->id}">{$product->name|escape}</h1>

{if $user|user_access:products_content AND $product->id}
	<div class="admin_edit">
		<a href="/agmin?view=ProductAdmin&id={$product->id}" title=" Редактировать товар">Редактировать
			товар</a>
	</div>
{/if}

<div class="product_one">
	<div class="header">

		<div class="images-box">

			<div class="promo_block">
				{if $product->sale}
					<div class="sale" title="Акция и скидка">Супер цена!</div>
				{/if}
			</div>

			{if $product->image}
				<div class="image">
					<a href="{$product->image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="images"
						data-caption="{$product->name|escape} - Фото: 1">
						<img src="{$product->image->filename|resize:720:720:w}" alt="{$product->name|escape} | Фото: 1"
							title="{$product->name|escape} - Фото: 1">
					</a>
				</div>
			{/if}

			{if $product->images|count>1}
				<div class="images">

					{* cut удаляет первую фотографию, если нужно начать 2-й - пишем cut:2 и тд *}
					{foreach $product->images|cut as $i=>$image}
						<div>
							<a href="{$image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="images"
								data-caption="{$product->name|escape} - Фото: {$i+1}">
								<img src="{$image->filename|resize:220:220}" alt="{$product->name|escape} - Фото: {$i+1}"
									title="{$product->name|escape} - Фото: {$i+1}">
							</a>
						</div>
					{/foreach}
				</div>
			{/if}
		</div>


		<div class="middle">
			{if $product->variants|count > 0}
				<form class="variants" action="/cart">
					{$show_buy_btn = false}
					{foreach $product->variants as $v}
						<div class="variant">

							<input id="product_{$v->id}" name="variant" value="{$v->id}" type="radio"
								class="variant_radiobutton" {if $product->variant->id == $v->id}checked{/if}
								{if !$v->stock AND !$v->custom}disabled{/if}
								{if $product->variants|count<2}style="display:none;" {/if} />

							{if $v->name}
								<label class="variant_name" for="product_{$v->id}">{$v->name}</label>
							{/if}

							<div class="status_stock">
								{if $product->disable}
									<span class="notinstock">Товар больше не поставляется</span>
								{elseif $v->stock>0}
									{$show_buy_btn = true}
									<span class="instock">В наличии</span>
									{if $v->stock|instock:4:'заканчивается'}
										<span class="instock_count">{$v->stock|instock:4:'заканчивается'}</span>
									{/if}
								{elseif $v->custom}
									{$show_buy_btn = true}
									<span class="awaiting">Под заказ</span>
								{elseif $v->awaiting}
									<span class="awaiting">Ожидается
										поставка {if strtotime(date("d.m.Y")) < strtotime($v->awaiting_date)}
										<span>{$v->awaiting_date|date}</span>{/if}</span>
								{else}
									<span class="notinstock">Нет в наличии</span>
								{/if}
							</div>

							<span class="price">
								{if $v->old_price > $v->price AND !$product->disable}
									<span class="old-price">{$v->old_price|convert} {$currency->sign}</span>
								{/if}
								<span class="price_name">Цена:</span>{$v->price|convert}<span
									class="price_sign">{$currency->sign|escape}</span>
							</span>

						</div>
					{/foreach}


					{if $show_buy_btn}
						<button type="submit" class="button btn_green btn_buy" value="в корзину" data-result-text="добавлено">
							<svg class="cart-icon" viewBox="0 0 1024 1024">
								<path fill="#fff"
									d="M97.718857 109.714286a109.714286 109.714286 0 0 1 107.349333 87.064381L220.16 268.190476h0.24381l49.005714 234.666667L306.541714 682.666667h459.678476l70.460953-341.333334H285.500952l-15.286857-73.142857h566.491429a73.142857 73.142857 0 0 1 71.631238 87.942095l-70.460952 341.333334A73.142857 73.142857 0 0 1 766.22019 755.809524H306.541714a73.142857 73.142857 0 0 1-71.631238-58.343619l-69.241905-335.335619-0.463238 0.097524-31.695238-150.357334A36.571429 36.571429 0 0 0 97.718857 182.857143H35.157333v-73.142857zM304.761905 926.47619a60.952381 60.952381 0 1 0 0-121.904761 60.952381 60.952381 0 0 0 0 121.904761z m438.857143 0a60.952381 60.952381 0 1 0 0-121.904761 60.952381 60.952381 0 0 0 0 121.904761z">
								</path>
							</svg>
							<span>Купить</span>
						</button>
					{/if}
				</form>
			{/if}


			{if $product->features}
				<h2 class="mt_45">Характеристики</h2>
				<ul class="features">
					{foreach $product->features as $f}
						<li>
							<div class="label">
								<span>{$f->name}</span>
							</div>
							<div class="value">
								<span>{$f->value}</span>
							</div>
						</li>
					{/foreach}
				</ul>
			{/if}
		</div>

		<div class="info-box">
			{get_info_block var=delivery_info id=9}
			{$delivery_info->body}
		</div>

	</div>


	<!-- Описание товара -->
	{if $product->body}
		<h2 class="mt_45">Описание</h2>
		<div class="description">
			{$product->body}
		</div>
	{/if}
</div>


{if $related_products}
	<div class="related_products_box">
		<h2>С этим товаром покупают</h2>
		<ul class="products gallerywide">
			{foreach $related_products as $product}
				{include file='parts/product_item.tpl'}
			{/foreach}
		</ul>
	</div>
{/if}


{include file='parts/comments.tpl'}