<!DOCTYPE html>
<html lang="ru">

{include file='parts/head.tpl'}

<body>

	{include file='parts/header.tpl'}

	<!-- Основной контент -->
	<div id="main">
		<div class="wrap">

			{$content}

			{if $seo->body}
				<div class="page_description">
					{$seo->body}
				</div>
			{/if}

		</div>
	</div>

	{include file='parts/footer.tpl'}

</body>

</html>