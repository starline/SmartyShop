{include file='products/products_settings_menu_part.tpl'}

{if $feature->id}
	{$meta_title = $feature->name scope=global}
{else}
	{$meta_title = 'Новое свойство' scope=global}
{/if}

{if $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success=='added'}Свойство добавлено
			{elseif $message_success=='updated'}Свойство
			обновлено{else}{$message_success}
			{/if}</span>
	</div>
{/if}

{if $message_error}
	<div class="message message_error">
		<span class="text">{$message_error}</span>
		<a class="button" href="">Вернуться</a>
	</div>
{/if}

<!-- Основная форма -->
<form method="post" class="form_css">
	<input type="hidden" name="session_id" value="{$smarty.session.id}">
	<div class="columns">

		<div class="block_flex w100">
			<div class="name_row">
				<input class="name" name=name type="text" value="{$feature->name|escape}" />
				<input name=id type="hidden" value="{$feature->id|escape}" />
			</div>
		</div>

		<div class="block_flex layer">
			<h2>Использовать в категориях</h2>
			<select class="multiple_categories" multiple name="feature_categories[]">
				{function name=category_select selected_id=$product_category level=0}
					{foreach $categories as $category}
						<option value="{$category->id}" {if in_array($category->id, $feature_categories)}selected{/if}
							category_name="{$category->single_name}">
							{section name=sp loop=$level}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$category->name}</option>
						{category_select categories=$category->subcategories selected_id=$selected_id  level=$level+1}
					{/foreach}
				{/function}
				{category_select categories=$categories}
			</select>
		</div>

		<div class="block_flex layer">
			<h2>Настройки свойства</h2>
			<div>
				<div>
					<input type="checkbox" name="in_filter" id="in_filter" {if $feature->in_filter}checked{/if}
						value="1">
					<label for="in_filter">Использовать в фильтре</label>
				</div>
			</div>

			<h3 class="mt_15">Варианты свойства</h3>
			<ul class="features_variants sessions" id="sort">

				<li class="one" style="display:none;">
					<div class="move">
						<div class="move_zone"></div>
					</div>
					<input name="feature_variants[]" type="text" value="" />
					<span class="delete"><i class="dash_link">Удалить</i></span>
				</li>

				{foreach name=variants from=$feature_variants item=variant}
					<li class="one">
						<div class="move">
							<div class="move_zone"></div>
						</div>
						<input name="feature_variants[]" type="text" value="{$variant}" />
						<span class="delete"><i class="dash_link">Удалить</i></span>
					</li>
				{/foreach}

			</ul>
			<span class="add"><i class="dash_link">Добавить вариант</i></span>


			<h2 class="mt_15 layer">Используемые свойства</h2>
			<div>
				{foreach $options as $option}
					<div>
						{$ido = $option->id}
						<a
							href="{url params=[view=>ProductsAdmin, $option->feature_id=>$option->value] clear=true}">{$option->value}</a>
					</div>
				{/foreach}
			</div>
		</div>

		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="" value="Сохранить" />
		</div>
	</div>

</form>


<script>
	{literal}
		$(function() {

			// Добавление варианта
			$('span.add').click(function() {
				var blok = $('.features_variants');
				$(blok).find(".one:first").clone(false).appendTo(blok).show('slow');
				return false;
			});

			// Удаление варианта
			$(".features_variants").on('click', '.delete', function() {
				$(this).closest(".one").fadeOut(200, function() {$(this).remove();});
				return false;
			});

			$("#sort").sortable({
				items: ".one:not(.sort_disabled)",
				cancel: ".sort_disabled",
				handle: ".move_zone",
				axis: 'y',
				tolerance: "pointer",
				opacity: 0.90,
			});
		});
	{/literal}
</script>