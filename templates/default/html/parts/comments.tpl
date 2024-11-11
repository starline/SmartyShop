<!-- Комментарии -->
<div id="comments">

	<h2>Отзывы и Комментарии</h2>

	{if $comments}
		<ul class="comment_list">
			{foreach $comments as $comment}
				<li id="comment_{$comment->id}">
					<div class="comment_header">
						{$comment->name|escape} <i>{$comment->date|date} в {$comment->date|time}</i>
						{if !$comment->approved}<span class="await_approval">ожидает модерации</span>{/if}
					</div>

					{$comment->text|escape|nl2br}

					<br>
					<a class="add_answer" data-id="{$comment->id}" style="cursor: pointer">Ответить</a>
				</li>
				{if isset($comment->answer)}
					{foreach $comment->answer as $answer}
						<li class="answer" id="comment_{$answer->id}">
							<div class="comment_header">
								{$answer->name|escape} <i>{$answer->date|date} в {$answer->date|time}</i>
								{if !$answer->approved}<span class="await_approval">ожидает модерации</span>{/if}
							</div>

							{$answer->text|escape|nl2br}
						</li>
					{/foreach}
				{/if}
			{/foreach}
		</ul>
	{else}
		<p>
			Ваш комментарий будет первым!
		</p>
	{/if}

	<!--Форма отправления комментария-->
	<form id="comment_form" class="comment_form" method="post">
		<h4>Написать комментарий</h4>

		{if $error}
			<div class="message_error">
				{if $error=='captcha'}
					Подтвердите что вы не робот
				{elseif $error=='empty_name'}
					Введите имя
				{elseif $error=='empty_comment'}
					Введите комментарий
				{/if}
			</div>
		{/if}

		<label for="comment_name">Имя</label>
		<input class="input_name" type="text" id="comment_name" name="comment_name" value="{$comment_name}"
			data-format=".+" data-notice="Введите имя" autocomplete="name" />
		<textarea class="comment_textarea" id="comment_text" name="comment_text" data-format=".+"
			data-notice="Введите комментарий">{$comment_text}</textarea>

		<input type="hidden" id="comment_related_id" name="comment_related_id" value="" />

		<div>
			<div class="g-recaptcha" data-sitekey="{$config->rc_public_key}"></div>
			<input class="button btn_green" type="submit" name="comment" value="Отправить" />
		</div>

		<div>
			<input class="comment_email" type="text" id="comment_email" name="comment_email" value=""
				data-format=".+@.+" data-notice="Введите email">
		</div>
	</form>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>


<script>
	{literal}

		$('.add_answer').click(function() {
			let id = $(this).data('id');

			$('form.comment_form').find('#comment_related_id').val(id);
			$('#comment_' + id).append($('form.comment_form'));
		});

	{/literal}
</script>