{*Печать поставки*}
{$wrapper='' scope=global}

<!DOCTYPE html>
<html>

<head>

	<title>{$variant->product_name}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="icon" href="/templates/{$settings->theme|escape}/images/favicon.ico" type="image/x-icon" />

	{literal}
		<style>
			@page {
				size: 30mm 20mm;
			}

			body {
				margin: 0;
				padding: 0;
				font: 0.5rem "Open Sans", sans-serif;
				color: black;
				background-color: white;
			}

			section {
				page-break-after: always;
				break-after: page;
				-webkit-print-color-adjust: exact;
			}

			section {
				width: 30mm;
				height: 20mm;
				margin-left: auto;
				margin-right: auto;
				display: grid;
				justify-items: center;
				margin-block-end: 1.5rem;
			}

			.name {
				font-weight: 600;
				padding: 0.2rem;
				background-color: hsl(0, 0%, 0%);
				color: #fff;
			}

			.sku {
				align-content: center;
				font-size: 1.5rem;
				padding: 0;
			}
		</style>
	{/literal}
</head>

<body>
	<div class='wrapper'>
		{for $i=1 to $count}
			<section>
				<div class="name">{$variant->product_name}{if $variant->name} - {$variant->name}{/if}</div>
				<div class="sku">{$variant->sku}</div>
			</section>
		{/for}
	</div>
</body>

</html>