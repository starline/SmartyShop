{include file='content/content_menu_part.tpl'}

{$meta_title='Комментарии' scope=global}

<div class="two_columns_list">

	<!-- Заголовок -->
	<div class="header_top">
		{if $keyword && $comments_count}
			<h1>{$comments_count|plural:'Нашелся':'Нашлось':'Нашлись'} {$comments_count}
				{$comments_count|plural:'комментарий':'комментариев':'комментария'}</h1>
		{elseif !$type}
			<h1>{$comments_count} {$comments_count|plural:'комментарий':'комментариев':'комментария'}</h1>
		{elseif $type=='product'}
			<h1>{$comments_count} {$comments_count|plural:'комментарий':'комментариев':'комментария'} к товарам</h1>
		{elseif $type=='blog'}
			<h1>{$comments_count} {$comments_count|plural:'комментарий':'комментариев':'комментария'} к записям в блоге
			</h1>
		{/if}

		{* Поиск *}
		{if $comments || $keyword}
			<div id="search">
				<form method="get">

					<input type="hidden" name="view" value='CommentsAdmin'>
					<input class="search" type="text" name="keyword" value="{$keyword|escape}" />
					<input class="search_button" type="submit" value="" />

				</form>
			</div>
		{/if}
	</div>

	<!-- Меню -->
	<div id="right_menu">
		<ul class="menu_list">
			<li {if !$type}class="selected" {/if}><a href="{url type=null}">Все комментарии</a></li>
			<li {if $type == 'product'}class="selected" {/if}><a href='{url keyword=null type=product}'>К товарам</a>
			</li>
			<li {if $type == 'blog'}class="selected" {/if}><a href='{url keyword=null type=blog}'>К блогу</a></li>
		</ul>
	</div>


	<div id="main_list">
		{if $comments}
			{include file='parts/pagination.tpl'}
			<form id="list_form" method="post">
				<input type="hidden" name="session_id" value="{$smarty.session.id}">

				<div id="comments" class="list sortable">
					{foreach $comments as $comment}
						<div class="{if !$comment->approved}approved_off{/if} row">
							<div class="checkbox">
								<input type="checkbox" name="check[]" value="{$comment->id}" />
							</div>
							<div class="name">
								<div class="comment_name">
									<a href="{url view=CommentAdmin id=$comment->id}">{$comment->name|escape}</a>
									<span class="round_box order_ip">IP: {$comment->ip}</span>
									{if !$comment->approved}<a class="approve" href="#">Одобрить</a>{/if}
								</div>
								<div class="comment_text">
									{$comment->text|escape|nl2br}
								</div>
								<div class="comment_info">
									Комментарий оставлен {$comment->date|date} в {$comment->date|time}

									{if $comment->type == 'product'}
										к товару <a target="_blank"
											href="{$config->root_url}/product/{$comment->product->id}#comment_{$comment->id}">{$comment->product->name}</a>
									{elseif $comment->type == 'blog'}
										к статье <a target="_blank"
											href="{$config->root_url}/blog/{$comment->post->url}#comment_{$comment->id}">{$comment->post->name}</a>
									{/if}
								</div>
							</div>
							<div class="icons">
								<a class="delete" title="Удалить" href="#"></a>
							</div>
						</div>
					{/foreach}
				</div>

				<div id="action">
					Выбрать
					<span id="check_all" class="dash_link">все</span> или <span id="check_unapproved"
						class="dash_link">ожидающие</span>

					<span id="select">
						<select name="action">
							<option value="">Выбрать действие</option>
							<option value="approve">Одобрить</option>
							<option value="delete">Удалить</option>
						</select>
					</span>

					<input id="apply_action" class="button_green" type="submit" value="Применить">

				</div>
			</form>
			{include file='parts/pagination.tpl'}
		{else}
			Нет комментариев
		{/if}
	</div>
</div>


<script>
	let session = '{$smarty.session.id}';

	{literal}
		$(function() {

			// Выделить ожидающие
			$("#check_unapproved").click(function() {
				$('.list input[type="checkbox"][name*="check"]').prop('checked', false);
				$('.list .unapproved input[type="checkbox"][name*="check"]').prop('checked', true);
			});

			// Одобрить
			$("a.approve").click(function() {
				ajax_icon($(this), 'comment', 'approved', session);
				return false;
			});

		});
	{/literal}
</script>