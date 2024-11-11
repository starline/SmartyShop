{capture name="tabs"}

	{if $user|user_access:orders}
		<li class="mini status_new {if $status == '0'}active{/if}">
			<a href="{url view=OrdersAdmin status=0 clear=true}">Новые</a>
			{if $orders_info_count[0]}
				<div class="counter"><span>{$orders_info_count[0]}</span></div>
			{/if}
		</li>

		<li class="mini status_work {if $status == 1}active{/if}">
			<a href="{url view=OrdersAdmin status=1 clear=true}">Приняты</a>
			{if $orders_info_count[1]}
				<div class="counter"><span>{$orders_info_count[1]}</span></div>
			{/if}
		</li>

		<li class="mini status_shipped {if $status == 4}active{/if}">
			<a href="{url view=OrdersAdmin status=4 clear=true}">Отгружены</a>
			{if $orders_info_count[4]}
				<div class="counter gray"><span>{$orders_info_count[4]}</span></div>
			{/if}
		</li>

		<li class="mini status_done {if $status == 2}active{/if}">
			<a href="{url view=OrdersAdmin status=2 clear=true}">Выполнены</a>
		</li>

		<li class="mini status_delete {if $status == 3}active{/if}">
			<a href="{url view=OrdersAdmin status=3 clear=true}">Отмена</a>
		</li>

		{if isset($keyword)}
			<li class="mini active">
				<a href="{url view=OrdersAdmin id=null label=null}">Поиск</a>
			</li>
		{/if}
	{/if}

	{if $user|user_access:orders_labels}
		<li class="mini right {if $view == 'OrdersLabelsAdmin' || $view == 'OrdersLabelAdmin'}active{/if}">
			<a href="{url view=OrdersLabelsAdmin clear=true}">Настройки</a>
		</li>
	{/if}

{/capture}