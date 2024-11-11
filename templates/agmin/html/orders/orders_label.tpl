{include file='orders/orders_settings_menu_part.tpl'}

{if $label->id}
	{$meta_title = $label->name scope=global}
{else}
	{$meta_title = 'Новая метка' scope=global}
{/if}

{if $message_success}
	<!-- Системное сообщение -->
	<div class="message message_success">
		<span class="text">{if $message_success == 'added'}Метка добавлена
			{elseif $message_success == 'updated'}Метка
			обновлена{/if}</span>
	</div>
{/if}

<!-- Основная форма -->
<form method="post" class="form_css" enctype="multipart/form-data">
	<input type="hidden" name="session_id" value="{$smarty.session.id}">

	<div class="columns">
		<div class="block_flex w100">
			<div class="over_name">
				<div class="checkbox_line">
					<div class="checkbox_item">
						<input name="enabled" value="1" type="checkbox" id="active_checkbox"
							{if $label->enabled}checked{/if} />
						<label for="active_checkbox">Активен</label>
					</div>
					<div class="checkbox_item">
						<input name="in_filter" value="1" type=checkbox id="in_filter_checkbox"
							{if $label->in_filter}checked{/if} />
						<label for="in_filter_checkbox">Использовать в фильтре заказов</label>
					</div>
				</div>
			</div>

			<div class="name_row">

				<span class="color_piker">
					<span id="color_icon" style="background-color:#{$label->color};" class="order_label_big"></span>
					<input id="color_input" name="color" type="hidden" value="{$label->color|escape}" />
				</span>

				<input class="name" name="name" type="text" value="{$label->name|escape}" />
				<input name="id" type="hidden" value="{$label->id|escape}" />
			</div>
		</div>

		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="" value="Сохранить" />
		</div>
	</div>
</form>


<link rel="stylesheet" media="screen" type="text/css"
	href="/{$config->templates_subdir}js/colorpicker/css/colorpicker.css" />
<script type="text/javascript" src="/{$config->templates_subdir}js/colorpicker/js/colorpicker.js"></script>



<script>
	{literal}
		// On document load
		$(function() {
			$('#color_icon, #color_link').ColorPicker({
				color: $('#color_input').val(),
				onShow: function(colpkr) {
					$(colpkr).fadeIn(500);
					return false;
				},
				onHide: function(colpkr) {
					$(colpkr).fadeOut(500);
					return false;
				},
				onChange: function(hsb, hex, rgb) {
					$('#color_icon').css('backgroundColor', '#' + hex);
					$('#color_input').val(hex);
					$('#color_input').ColorPickerHide();
				}
			});
		});
	{/literal}
</script>