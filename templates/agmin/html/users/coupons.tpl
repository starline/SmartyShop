{include file='users/users_menu_part.tpl'}

{$meta_title='Купоны' scope=global}

<div id=header class=header_top>
	{if $coupons_count}
		<h1>{$coupons_count} {$coupons_count|plural:'купон':'купонов':'купона'}</h1>
	{else}
		<h1>Нет купонов</h1>
	{/if}
	<a class="add" href="{url view=CouponAdmin}">Новый купон</a>
</div>


{if $coupons}
	<div id="main_list">

		{include file='parts/pagination.tpl'}

		<form id="form_list" method="post">
			<input type="hidden" name="session_id" value="{$smarty.session.id}">

			<div class="list">
				{foreach $coupons as $coupon}
					<div class="{if $coupon->valid}highlight{/if} row" item_id="{$coupon->id}">
						<div class="checkbox">
							<input type="checkbox" name="check[]" value="{$coupon->id}" />
						</div>
						<div class="name">
							<a href="{url view=CouponAdmin id=$coupon->id}">{$coupon->code}</a>
						</div>
						<div class="coupon_discount">
							Скидка {$coupon->value*1} {if $coupon->type=='absolute'}{$currency->sign}{else}%{/if}<br>
							{if $coupon->min_order_price>0}
								<div class="detail">
									Для заказов от {$coupon->min_order_price|escape} {$currency->sign}
								</div>
							{/if}
						</div>
						<div class="coupon_details">
							{if $coupon->single}
								<div class="detail">
									Одноразовый
								</div>
							{/if}
							{if $coupon->usages>0}
								<div class="detail">
									Использован {$coupon->usages|escape} {$coupon->usages|plural:'раз':'раз':'раза'}
								</div>
							{/if}
							{if $coupon->expire}
								<div class="detail">
									{if $smarty.now|date_format:'%Y%m%d' <= $coupon->expire|date_format:'%Y%m%d'}
										Действует до {$coupon->expire|date}
									{else}
										Истёк {$coupon->expire|date}
									{/if}
								</div>
							{/if}
						</div>
						<div class="icons">
							<a href='#' class="delete" title="Удалить"></a>
						</div>
					</div>
				{/foreach}
			</div>


			<div id="action">
				<span id="check_all" class="dash_link">Выбрать все</span>

				<span id="select">
					<select name="action">
						<option value="">Выбрать действие</option>
						<option value="delete">Удалить</option>
					</select>
				</span>

				<input id="apply_action" class="button_green" type="submit" value="Применить">

			</div>

		</form>

		<!-- Листалка страниц -->
		{include file='parts/pagination.tpl'}

	</div>
{/if}