<!DOCTYPE html>
<html>

{include file='parts/head.tpl'}

<body>

	<div id="header" class="header-order">
		<div class="wrap">
			<div class="content">
				<div class="logo">
					<a href="/" title="{$settings->company_name|escape} - {$settings->company_description|escape}"
						style="background-image: url(/{$config->templates_subdir}{$settings->theme}/images/logo.png);"></a>
				</div>

				<div class="contact">
					{include file='parts/phones.tpl'}
				</div>

				{include file='parts/work_time.tpl'}
			</div>
		</div>
	</div>

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

	<footer>
		<div class="wrap">

		</div>
	</footer>

</body>

</html>