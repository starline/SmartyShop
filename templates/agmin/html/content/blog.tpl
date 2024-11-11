{include file='content/content_menu_part.tpl'}

{* Title *}
{$meta_title='Блог' scope=global}

<!-- Заголовок -->

<div class="header_top">
	{if $keyword && $posts_count}
		<h1>{$posts_count|plural:'Нашлась':'Нашлись':'Нашлись'} {$posts_count}
			{$posts_count|plural:'запись':'записей':'записи'}</h1>
	{elseif $posts_count}
		<h1>{$posts_count} {$posts_count|plural:'запись':'записей':'записи'} в блоге</h1>
	{else}
		<h1>Нет записей</h1>
	{/if}

	<a class="add" href="{url view=PostAdmin}">Добавить запись</a>


	{if $posts || $keyword}
		<!-- Поиск -->
		<div id="search">
			<form method="get">
				<input type="hidden" name="view" value='BlogAdmin' />
				<input class="search" type="text" name="keyword" value="{$keyword|escape}" />
				<input class="search_button" type="submit" value="" />
			</form>
		</div>
	{/if}
</div>


<!-- Статьи -->
<div id="main_list">
	{if $posts}

		{include file='parts/pagination.tpl'}
		<form id="form_list" method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}" />

			<div class="list">
				{foreach $posts as $post}
					<div class="{if !$post->visible}visible_off{/if} row">
						<input type="hidden" name="positions[{$post->id}]" value="{$post->position}" />
						<div class="checkbox">
							<input type="checkbox" name="check[]" value="{$post->id}" />
						</div>

						<div class="name">

							<a href="{url view=PostAdmin id=$post->id}">{$post->name|escape}</a>
							<div class="icons">
								<a class="external_link" title="Предпросмотр в новом окне" href="../blog/{$post->url}"
									target="_blank"></a>
							</div>

							<div class=comment_info>{$post->date|date}</div>
						</div>

						<div class="icons">
							<a class="enable" title="Активна" href="#"></a>
							<a class="delete" title="Удалить" href="#"></a>
						</div>
					</div>
				{/foreach}
			</div>


			<div id="action">
				<span id="check_all" class="dash_link">Выбрать все</span>

				<span id="select">
					<select name="action">
						<option value="">Выбрать действие</option>
						<option value="enable">Сделать видимыми</option>
						<option value="disable">Сделать невидимыми</option>
						<option value="delete">Удалить</option>
					</select>
				</span>

				<input id="apply_action" class="button_green" type="submit" value="Применить" />

			</div>

		</form>
		{include file='parts/pagination.tpl'}

	{/if}
</div>

<script>
	let session = '{$smarty.session.id}';

	{literal}
		$(function() {

			// Скрыт/Видим
			$("a.enable").click(function() {
				ajax_icon($(this), 'blog', 'visible', session);
				return false;
			});

		});
	{/literal}
</script>