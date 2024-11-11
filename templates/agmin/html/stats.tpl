{capture name=tabs}
   <li class="mini {if $view == 'StatsAdmin'}active{/if}">
      <a href="{url view=StatsAdmin}">График продаж</a>
   </li>
{/capture}

{$meta_title='График продаж' scope=global}

<div class="header_top">
   <h1 class="total_amount">
      На складе {$total->sum_stock|price_format:0} товара
      <span class="sum_total" title="Выручка в розницу">на сумму <span class="amount">{$total->sum_price|price_format:0}
            {$currency->sign}</span><span class="sum_profit_price"
            title="Розничная прибыль">+{($total->sum_price - $total->sum_wholesale_price)|price_format:0}
            {$currency->sign}</span></span>


      <span class="sum_total">в ассортименте <span class="amount">{$total->products_count|price_format:0}
            единиц</span><span class="sum_profit_price"></span></span>
      <span class="sum_total">себестоимостью <span class="amount">{$total->sum_wholesale_price|price_format:0}
            {$currency->sign}</span><span class="sum_profit_price"></span></span>
   </h1>
</div>


<div class="columns">
   <div class="block_flex w100">
      <div id='stats_byDay'></div>
   </div>

   <div class="block_flex w100 layer">
      <h2>Статистика заказов по месяцам</h2>
      <select name="payment_method" id="payment_method">
         <option value="">Все способы оплаты</option>
         {foreach $payment_methods as $payment_method}
            <option class="{if !$payment_method->enabled}disabled{/if}" value="{$payment_method->id}">
               {$payment_method->name}
            </option>
         {/foreach}
      </select>

      <div id='stats_byMonth'></div>
   </div>
</div>

{include file='parts/charts_init.tpl'}


<script>

   let php_currency_name = '{$currency->name}';
   let php_currency_sign = '{$currency->sign}';

   {literal}
      $(function() {

         var date = new Date();
         date.setMonth(date.getMonth() - 2); // 2 месяца

         // 30.08.2020
         var date_format = date.getDate() + '.' + date.getMonth() + '.' + date.getFullYear()

         // Выводим график
         let my_options = {
            title: {
               text: 'Статистика заказов'
            },
            subtitle: {
               text: 'Выручка по дням'
            },
            xAxis: {
               type: 'datetime',
               minRange: 7 * 24 * 3600000,
               maxZoom: 7 * 24 * 3600000,
               gridLineWidth: 1,
               ordinal: true,
               showEmpty: true
            },
            yAxis: {
               title: {
                  text: php_currency_name
               }
            }
         }

         // Выводим график по дням
         show_stat_graphic(
            'stats_byDay',
            {fromDate: date_format, filter: 'byDay'},
            ['totalPrice', 'profitPrice', 'amount'],
            my_options,
            php_currency_sign,
            function(response) {
               return response;
            }
         );


         // Выводим график по месяцам
         show_stat_graphic(
            'stats_byMonth',
            {filter: 'byMonth'},
            ['totalPrice', 'profitPrice', 'amount'],
            my_options,
            php_currency_sign,
            function(response) {
               return response;
            }
         );

         $('select[name="payment_method"]').change(function() {
            let paymentMethod = $('select[name="payment_method"]').val();

            show_stat_graphic(
               'stats_byMonth',
               {filter: 'byMonth', paymentMethod: paymentMethod},
               ['totalPrice', 'profitPrice', 'amount'],
               my_options,
               php_currency_sign,
               function(response) {
                  return response;
               }
            );
         });

      });

   {/literal}
</script>