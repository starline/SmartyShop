{capture name=tabs}
	{if $user|user_access:orders}
		<li class="mini {if $status == '0'}active{/if}">
			<a href="{url view=OrdersAdmin clear=true}">Заказы</a>
			{if $orders_info_count[0]}
				<div class="counter"><span>{$orders_info_count[0]}</span></div>
			{/if}
		</li>
	{/if}

	{if $user|user_access:orders_payment}
		<li class="mini right {if $view=='OrdersPaymentMethodsAdmin' || $view=='OrdersPaymentMethodAdmin'}active{/if}">
			<a href="{url view=OrdersPaymentMethodsAdmin clear=true}">Оплата</a>
		</li>
	{/if}

	{if $user|user_access:orders_delivery}
		<li class="mini right {if $view=='OrdersDeliveriesAdmin' || $view=='OrdersDeliveryAdmin'}active{/if}">
			<a href="{url view=OrdersDeliveriesAdmin clear=true}">Доставка</a>
		</li>
	{/if}

	{if $user|user_access:orders_labels}
		<li class="mini right {if $view=='OrdersLabelsAdmin' || $view=='OrdersLabelAdmin'}active{/if}"><a
				href="{url view=OrdersLabelsAdmin clear=true}">Метки</a></li>
	{/if}
{/capture}