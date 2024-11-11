<!DOCTYPE html>
<html>

<head>

	<meta charset="utf-8">
	<meta HTTP-EQUIV="Pragma" CONTENT="no-cache">
	<meta HTTP-EQUIV="Expires" CONTENT="-1">
	<meta name="viewport" content="width=device-width">

	<title>{$meta_title}</title>

	<link rel="icon" href="/templates/{$settings->theme|escape}/images/favicon.ico" type="image/x-icon">

	{$css_files = array(
		"/`$config->templates_subdir`css/style.css",
		"/`$config->templates_subdir`js/jquery/jquery-ui.css",
		"/`$config->templates_subdir`js/fancybox/jquery.fancybox.min.css"
	)}
	{combine input=$css_files output="/`$config->templates_subdir`css/combine.css" use_true_path=false age='180' debug=$config->smarty_combine}

	{$js_files = array(
		"/`$config->templates_subdir`js/jquery/jquery.js",
		"/`$config->templates_subdir`js/jquery/jquery-ui.js",
		"/`$config->templates_subdir`js/jquery/jquery.form.js",
		"/`$config->templates_subdir`js/fancybox/jquery.fancybox.min.js",
		"/`$config->templates_subdir`js/common.js"
	)}
	{combine input=$js_files output="/`$config->templates_subdir`js/combine.js" use_true_path=false age='180' debug=$config->smarty_combine}

</head>

<body>

	<a class="admin_bookmark" href="{$config->root_url}" title="Перейти в админку">
		<svg viewBox="0 0 24 24" focusable="false" class="dyAbMb">
			<path d="M0 0h24v24H0z" fill="none"></path>
			<path d="M8.59,16.59L13.17,12L8.59,7.41L10,6l6,6l-6,6L8.59,16.59z"></path>
		</svg>
	</a>

	<!-- Вся страница -->
	<div id="main">
		<ul id="main_menu">

			{if $user|user_access:products_view}
				<li>
					<a href="{url view=ProductsAdmin clear=true}">
						<img src="/{$config->templates_subdir}images/menu/catalog.png" />
						<b>Товары</b>
					</a>
				</li>
			{/if}


			{if $user|user_access:orders}
				<li>
					<a href="{url view=OrdersAdmin clear=true}">
						<img src="/{$config->templates_subdir}images/menu/orders.png" />
						<b>Заказы</b>
					</a>
					{if $orders_info_count[0]}
						<div class="counter">
							<span>{$orders_info_count[0]}</span>
						</div>
					{/if}
				</li>
			{/if}


			{if $user|user_access:users}
				<li>
					<a href="{url view=UsersAdmin clear=true}">
						<img src="/{$config->templates_subdir}images/menu/users.png">
						<b>Покупатели</b>
					</a>
				</li>
			{/if}


			{if $user|user_access:comments}
				<li>
					<a href="{url view=CommentsAdmin clear=true}">
						<img src="/{$config->templates_subdir}images/menu/pages.png">
						<b>Контент</b>
					</a>
					{if $new_comments_counter}
						<div class="counter">
							<span>{$new_comments_counter}</span>
						</div>
					{/if}
				</li>
			{elseif $user|user_access:blog}
				<li>
					<a href="{url view=BlogAdmin clear=true}">
						<img src="/{$config->templates_subdir}images/menu/pages.png">
						<b>Контент</b>
					</a>
				</li>
			{elseif $user|user_access:pages}
				<li>
					<a href="{url view=PagesAdmin clear=true}">
						<img src="/{$config->templates_subdir}images/menu/pages.png"><b>Контент</b>
					</a>
				</li>
			{elseif $user|user_access:feedbacks}
				<li>
					<a href="{url view=FeedbacksAdmin clear=true}">
						<img src="/{$config->templates_subdir}images/menu/pages.png"><b>Контент</b>
					</a>
				</li>
			{/if}


			{if $user|user_access:stats}
				<li>
					<a href="{url view=StatsAdmin clear=true}">
						<img src="/{$config->templates_subdir}images/menu/statistics.png"><b>Статистика</b>
					</a>
				</li>
			{/if}


			{if $user|user_access:finance}
				<li>
					<a href="{url view=FinancePaymentsAdmin clear=true}">
						<img src="/{$config->templates_subdir}images/menu/finance.png"><b>Финансы</b>
					</a>
				</li>
			{/if}


			{if $user|user_access:settings}
				<li>
					<a href="{url view=SettingsAdmin clear=true}">
						<img src="/{$config->templates_subdir}images/menu/settings.png"><b>Настройки</b>
					</a>
				</li>
			{/if}
		</ul>

		<ul id="tab_menu">
			{$smarty.capture.tabs}
		</ul>

		<div id="middle">
			{$smarty.capture.subtabs}
			{$content}
		</div>

		<div id="footer">
			<span class="grey">Вы вошли как</span>
			<a class="user_name" href="{url view=UserAdmin id=$user->id clear=true}">{$user->name}</a>
			<a href="/user/logout" id="logout">Выход</a>
		</div>

	</div>

</body>

</html>