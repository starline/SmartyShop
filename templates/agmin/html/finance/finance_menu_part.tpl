{capture name=tabs}
	<li class="mini {if $view == 'FinancePaymentsAdmin' || $view == 'FinancePaymentAdmin'}active{/if}">
		<a href="{url view=FinancePaymentsAdmin clear=true}">Платежи</a>
	</li>

	<li class="mini right {if $view=='CurrencyAdmin'}active{/if}">
		<a href="{url view=CurrencyAdmin clear=true}">Валюты</a>
	</li>

	<li class="mini right {if $view == 'PursesAdmin' || $view == 'PurseAdmin'}active{/if}">
		<a href="{url view=PursesAdmin clear=true}">Кошелки</a>
	</li>

	<li class="mini right {if $view == 'FinanceCategoryAdmin' || $view == 'FinanceCategoriesAdmin'}active{/if}">
		<a href="{url view=FinanceCategoriesAdmin clear=true}">Категории платежей</a>
	</li>
{/capture}