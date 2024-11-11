{if $user->manager}
	<a class="admin_bookmark" href="/agmin" title="Перейти в админку">
		<svg viewBox="0 0 24 24" focusable="false">
			<path d="M0 0h24v24H0z" fill="none"></path>
			<path d="M8.59,16.59L13.17,12L8.59,7.41L10,6l6,6l-6,6L8.59,16.59z"></path>
		</svg>
	</a>
{/if}

<div class="back-to-top"></div>

<!-- Верхняя строка -->
<div id="header_top">
	<div class="wrap">

		<div class="menu_button">
			<i></i>
			<i></i>
			<i></i>
		</div>

		<!-- Меню -->
		<ul id="menu">
			{get_pages visible=1 menu_id=1 var='pages'}
			{foreach $pages as $p}
				<li class=" {if ($page && $page->id == $p->id) OR ($p->id == 4 and $posts)}selected{/if}">
					<a data-page="{$p->id}" href="/info/{$p->url}">{$p->name|escape}</a>
				</li>
			{/foreach}
		</ul>

		<!-- Корзина -->
		<div id="cart-informer">
			{* Обновляемая аяксом корзина должна быть в отдельном файле *}
			{include file='parts/cart_informer.tpl'}
		</div>

		<!-- Вход пользователя -->
		<div id="account">
			{if $user->name}
				<span id="username">
					<a href="/user"
						title="{if $user->group->discount>0}ваша скидка: {$user->group->discount}%{/if}">{$user->name}</a>
				</span>
				<a class="logout" href="/user/logout">выйти</a>
			{else}
				<a class="login" href="/user/login">Вход</a>
			{/if}
		</div>

	</div>
</div>


<!-- Шапка -->
<div id="header">
	<div class="wrap">
		<div class="content">
			<div class="logo">
				<a href="/" title="{$settings->company_name|escape} - {$settings->company_description|escape}"
					style="background-image: url(/{$config->templates_subdir}{$settings->theme|escape}/images/logo.png);"></a>
			</div>

			<div class="search">
				<div class="search-wrap">
					<form action="/product">
						<input class="input_search" type="text" name="keyword" value="{$keyword|escape}"
							placeholder="Поиск, например: фрезы">
						<span class="search_button">
							<svg id="search_icon" viewBox="0 0 4.15758 4.15745">
								<path
									d="M4.04343 3.49209l-0.954009 -0.953705c-0.142071,0.220816 -0.330047,0.408944 -0.551116,0.55104l0.953882 0.953806c0.1523,0.152275 0.399171,0.1523 0.551217,0 0.152199,-0.151946 0.152199,-0.398841 2.532e-005,-0.551141z">
								</path>
								<path
									d="M1.55908 2.72829c-0.644724,0 -1.16928,-0.524581 -1.16928,-1.16915 5.06401e-005,-0.644826 0.524505,-1.16941 1.16925,-1.16941 0.644826,-2.532e-005 1.16933,0.524581 1.16933,1.16941 5.06401e-005,0.644572 -0.524505,1.16915 -1.16931,1.16915zm1.55913 -1.16915c0,-0.86116 -0.698175,-1.55913 -1.55916,-1.55913 -0.860907,0 -1.55906,0.697972 
								-1.55906,1.55913 0,0.860882 0.69815,1.55898 1.55906,1.55898 0.861033,-2.532e-005 1.55913,-0.698074 1.55916,-1.55898z">
								</path>
								<path
									d="M0.649662 1.55913l0.25986 0c0,-0.358177 0.291434,-0.649738 0.649535,-0.649738l2.532e-005 -0.259758c-0.501388,0 -0.909445,0.407855 -0.90942,0.909496z">
								</path>
							</svg>
						</span>
					</form>
				</div>
			</div>

			<div class="contact">
				{include file='parts/phones.tpl'}
			</div>

			{include file='parts/work_time.tpl'}
		</div>

		<div class="soc-info">
			{get_info_block var=delivery_info id=15}
			{$delivery_info->body}
		</div>

		<div class="products-catalog-menu">
			<table id="action-zone">
				<tbody>
					<tr>
						{foreach $categories as $cat}
							<td class="{if $cat@last}last{/if}{if $cat@first}first{/if}">
								<a href="/{$cat->url}" class="p-c-title">
									<span class="p-c-title-text">{$cat->name}</span>
								</a>
							</td>
						{/foreach}
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>