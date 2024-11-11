{include file='content/content_menu_part.tpl'}

{* Title *}
{$meta_title='Обратная связь' scope=global}

{* Поиск *}
{if $feedbacks || $keyword}
	<form method="get">
		<div id="search">
			<input type="hidden" name="module" value='FeedbacksAdmin'>
			<input class="search" type="text" name="keyword" value="{$keyword|escape}" />
			<input class="search_button" type="submit" value="" />
		</div>
	</form>
{/if}

{* Заголовок *}
<div class="header_top">
	{if $feedbacks_count}
		<h1>{$feedbacks_count} {$feedbacks_count|plural:'сообщение':'сообщений':'сообщения'}</h1>
	{else}
		<h1>Нет сообщений</h1>
	{/if}
</div>

<div id="main_list">

	<!-- Листалка страниц -->
	{include file='parts/pagination.tpl'}

	{if $feedbacks}
		<form id="list_form" method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}">

			<div class="list">

				{foreach $feedbacks as $feedback}
					<div class="{if !$feedback->visible}visible_off{/if} row">
						<div class="checkbox">
							<input type="checkbox" name="check[]" value="{$feedback->id}" />
						</div>
						<div class="name">
							<div class='comment_name'>
								<a
									href="mailto:{$feedback->name|escape}<{$feedback->email|escape}>?subject=Вопрос от пользователя {$feedback->name|escape}">{$feedback->name|escape}</a>
							</div>
							<div class='comment_text'>
								{$feedback->message|escape|nl2br}
							</div>
							<div class='comment_info'>
								Сообщение отправлено {$feedback->date|date} в {$feedback->date|time}
							</div>
						</div>
						<div class="icons">
							<a href='#' title='Удалить' class="delete"></a>
						</div>
					</div>
				{/foreach}
			</div>

			<div id="action">
				<span id='check_all' class='dash_link'>Выбрать все</span>
				<span id=select>
					<select name="action">
						<option value="">Выбрать действие</option>
						<option value="delete">Удалить</option>
					</select>
				</span>
				<input id='apply_action' class="button_green" type=submit value="Применить">
			</div>
		</form>

	{else}
		Нет сообщений
	{/if}

	<!-- Листалка страниц -->
	{include file='parts/pagination.tpl'}
</div>


<script>
	let session = '{$smarty.session.id}';

	{literal}
		$(function() {

			// Скрыт/Видим
			$("a.enable").click(function() {
				ajax_icon($(this), 'feedbacks', 'visible', session);
				return false;
			});

		});
	{/literal}
</script>