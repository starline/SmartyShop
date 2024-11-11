{include file='settings/theme_menu_part.tpl'}

{$meta_title = "Изображения" scope=global}


{if $message_error}
	<div class="message message_error">
		<span class="text">{if $message_error == 'permissions'}Установите права на запись для папки {$images_dir}
			{elseif $message_error == 'name_exists'}Файл с таким именем уже существует
			{elseif $message_error == 'theme_locked'}Текущая тема защищена от изменений. Создайте копию темы.
			{else}{$message_error}
			{/if}</span>
	</div>
{/if}

<div class="header_top">
	<h1>Изображения темы {$theme}</h1>
</div>

<form class="form_css" method="post" enctype="multipart/form-data">
	<input type="hidden" name="session_id" value="{$smarty.session.id}">
	<input type="hidden" name="delete_image" value="">

	<div class="columns">
		<div class="block_flex w100 layer">
			<ul class="theme_images">
				{foreach item=image from=$images}
					<li name='{$image->name|escape}'>
						<a href="#" class="delete" title="Удалить">
							<img src='/{$config->templates_subdir}images/delete.png'>
						</a>
						<a href="#" class="edit" title="Переименовать">
							<img src='/{$config->templates_subdir}images/pencil.png'>
						</a>
						<p class="name">{$image->name|escape|truncate:16:'...'}</p>
						<div class="theme_image">
							<a class="preview" href="{$images_url}{$image->name|escape}">
								<img src="{$images_url}{$image->name|escape}">
							</a>
						</div>
						<p class="size">
							{if $image->size>1024*1024}
								{($image->size/1024/1024)|round:2} МБ
							{elseif $image->size>1024}
								{($image->size/1024)|round:2} КБ
							{else}
								{$image->size} Байт
							{/if},
							{$image->width}&times;{$image->height} px</p>
					</li>
				{/foreach}
			</ul>
		</div>

		<div class="block_flex w100 upload_image">
			<span id="upload_image"><i class="dash_link">Добавить изображение</i></span>
		</div>

		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="save" value="Сохранить" />
		</div>
	</div>
</form>



<script>
	{literal}
		$(function() {

			// Редактировать
			$("a.edit").click(function() {
				name = $(this).closest('li').attr('name');
				inp1 = $('<input type=hidden name="old_name[]">').val(name);
				inp2 = $('<input type=text name="new_name[]">').val(name);
				$(this).closest('li').find("p.name").html('').append(inp1).append(inp2);
				inp2.focus().select();
				return false;
			});


			// Удалить 
			$("a.delete").click(function() {
				name = $(this).closest('li').attr('name');
				$('input[name=delete_image]').val(name);
				$(this).closest("form").submit();
			});

			// Загрузить
			$("#upload_image").click(function() {
				$(this).closest('div').append($('<input type=file name=upload_images[]>'));
			});

			$("form").submit(function() {
				if ($('input[name="delete_image"]').val() != '' && !confirm('Подтвердите удаление'))
					return false;
			});

		});
	{/literal}
</script>