{$wrapper = 'index.tpl' scope=global}

<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "WebSite",
		"url": "{$config->root_url}",
		"potentialAction": {
			"@type": "SearchAction",
			"target": "{$config->root_url}/search/{literal}{search_term_string}{/literal}",
			"query-input": "required name=search_term_string"
		}
	}

	window.dataLayer = window.dataLayer || [];
	dataLayer.push({
		'event': 'DynamicRemarketing',
		'dynamicParams': {
			'ecomm_pagetype': 'home',
			'ecomm_prodid': '',
			'ecomm_totalvalue': ''
		}
	});
</script>


{if $categories_products}
	{foreach $categories_products as $cat_products}
		<div class="accessories_products">
			<div class="title-main">
				<h2>{$cat_products->category->name}</h2>
				<span> → <a href="/{$cat_products->category->url}" title="{$cat_products->category->name}">все
						{$cat_products->category->name}</a></span>
			</div>
			<ul class="products gallerywide">
				{foreach $cat_products->products as $product}
					{include file='parts/product_item.tpl'}
				{/foreach}
			</ul>
		</div>
	{/foreach}
{/if}