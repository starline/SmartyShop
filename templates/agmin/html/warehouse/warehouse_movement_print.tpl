{*Печать поставки*}
{$wrapper='' scope=global}

<!DOCTYPE html>
<html>

<head>
	<title>Поставка №{$movement->id}</title>

	{* Метатеги *}
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="description" content="{$meta_description|escape}" />

	<link rel="icon" href="/templates/{$settings->theme|escape}/images/favicon.ico" type="image/x-icon" />

	<style>
		{literal}
			@page {
				size: A4 portrait;
			}

			body {
				margin: 0;
				padding: 0;
				font: 0.9em "Open Sans", sans-serif;
				background-color: #fff;
			}

			div.wrapper {
				width: 900px;
				margin-left: auto;
				margin-right: auto;
				font-family: "Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;
				font-size: 10pt;
				color: black;
				background-color: white;
			}

			h1 {
				margin: 0;
				font-weight: normal;
				font-size: 40px;
			}

			h2 {
				margin: 0;
				font-weight: normal;
				font-size: 24px;
			}

			p {
				font-style: italic;
				margin: 0;
			}

			div#header {
				margin-top: 50px;
				height: 75px;
				float: left;
			}

			div#company {
				margin-top: 50px;
				width: 550px;
				float: right;
				text-align: right;
			}

			div#purchases {
				margin-bottom: 30px;
			}

			div#purchases table {
				width: 100%;
				border-collapse: collapse
			}

			div#purchases table th {
				font-weight: 600;
				text-align: left;
				font-size: 15px;
			}

			div#purchases td,
			div#purchases th {
				font-size: 14px;
				padding-top: 5px;
				padding-bottom: 5px;
				margin: 0;
			}

			div#purchases td {
				border-top: 1px solid lightgrey;
			}

			div#purchases td.align_right, div#purchases th.align_right{text-align: right;}

			div.image img {
				width: 35px;
				padding: 2px;
				border: 1px solid #d3d3d3;
			}

			div.note {
				margin-bottom: 60px;
			}

			div.note p {
				margin: 20px 0;
			}

		{/literal}
	</style>
</head>

<body>
	<div class="wrapper">
		<div id="header">
			<h1>Поставка №{$movement->id}</h1>
			<p>от {$movement->awaiting_date|date}</p>
		</div>

		<div id="company">
			<h2>{$settings->company_name}</h2>
			<span>{$settings->company_description}</span>
		</div>

		<div id="purchases">
			<table>
				<tr>
					<th>Фото</th>
					<th>Товар</th>
					<th class="align_right">Артикул</th>
					<th class="align_right">Количество</th>
				</tr>

				{foreach $purchases as $purchase}
					<tr>
						<td>
							<div class="image">
								{$image = $purchase->product->images|first}
								{if $image}
									<a href="{$image->filename|resize:1080:1080:w}" class="zoom"
										data-fancybox="images-{$purchase->sku}"
										data-caption="{$purchase->product_name|escape} - Фото: 1">
										<img class="product_icon" src="{$image->filename|resize:50:50}">
									</a>
									{foreach $purchase->product->images|cut as $i=>$image}
										<a href="{$image->filename|resize:1080:1080:w}" class="zoom"
											data-fancybox="images-{$purchase->sku}"
											data-caption="{$purchase->product_name|escape} - Фото: {$i+1}">
										</a>
									{{/foreach}}
								{/if}
							</div>
						<td>
							<span class="view_purchase">
								{$purchase->product_name}{if $purchase->variant_name} - {$purchase->variant_name}{/if}
							</span>
						</td>
						<td class="align_right">
							<span class="view_purchase">{$purchase->sku}</span>
						</td>
						<td class="align_right">
							<span class="view_purchase">
								{$purchase->amount} {$settings->units}
							</span>
						</td>
					</tr>
				{/foreach}
			</table>
		</div>

		{if $movement->note}
			<div class="note">
				<h2>Примечания для приемщика</h2>
				<p>{$movement->note|escape|nl2br}</p>
			</div>
		{/if}
	</div>
</body>

</html>