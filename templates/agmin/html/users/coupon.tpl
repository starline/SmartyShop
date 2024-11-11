{include file='users/users_menu_part.tpl'}

{if $coupon->code}
	{$meta_title = $coupon->code scope=global}
{else}
	{$meta_title = 'Новый купон' scope=global}
{/if}

{if $message_success}
	<div class="message message_success">
		<span class="text">{if $message_success == 'added'}Купон добавлен
			{elseif $message_success == 'updated'}Купон
			изменен{/if}</span>
	</div>
{/if}

{if $message_error}
	<div class="message message_error">
		<span class="text">{if $message_error == 'code_exists'}Купон с таким кодом уже существует{/if}</span>
		<a class="button" href="">Вернуться</a>
	</div>
{/if}


<!-- Основная форма -->
<form method="post" enctype="multipart/form-data">
	<input type="hidden" name="session_id" value="{$smarty.session.id}">

	<div class="columns">
		<div class="block_flex w100">
			<div class="name_row">
				<input class="name" name="code" type="text" value="{$coupon->code|escape}" />
				<input name="id" class="name" type="hidden" value="{$coupon->id|escape}" />
			</div>
		</div>

		<div class="block_flex">
			<ul class="property_block">
				<li>
					<label class="property_name" for=value>Скидка</label>

					<div class="whith_unit">
						<input name="value" id="value" class="coupon_value small_inp" type="text"
							value="{$coupon->value|escape}" />

						<select class="coupon_type label_unit" name="type">
							<option value="percentage" {if $coupon->type=='percentage'}selected{/if}>%</option>
							<option value="absolute" {if $coupon->type=='absolute'}selected{/if}>{$currency->sign}
							</option>
						</select>
					</div>
				</li>
				<li>
					<label for="min_order_price" class="property_name">Для заказов от</label>
					<div class="whith_unit">
						<input class="coupon_value small_inp" id="min_order_price" type="text" name="min_order_price"
							value="{$coupon->min_order_price|escape}">
						<span class="label_unit"> {$currency->sign}</span>
					</div>
				</li>
			</ul>
			<div class="checkbox_item">
				<input type="checkbox" name="single" id="single" value="1" {if $coupon->single==1}checked{/if}>
				<label for="single">одноразовый</label>
			</div>
		</div>

		<div class="block_flex">
			<ul class="property_block">
				<li>
					<label class="property_name">
						<input type="checkbox" name="expires" value="1" {if $coupon->expire}checked{/if}>Истекает
					</label>
					<input class="small_inp" type="text" name="expire" value='{$coupon->expire|date}'>
				</li>
			</ul>
		</div>

		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="" value="Сохранить" />
		</div>

	</div>
</form>

<script src="/{$config->templates_subdir}js/jquery/datepicker/jquery.ui.datepicker-ru.js"></script>
{include file='parts/tinymce_init.tpl'}

<script>
	{literal}
		$(function() {

			$('input[name="expire"]').datepicker({
				regional: 'ru'
			});
			$('input[name="end"]').datepicker({
				regional: 'ru'
			});

			// On change date
			$('input[name="expire"]').focus(function() {
				$('input[name="expires"]').attr('checked', true);
			});

		});
	{/literal}
</script>