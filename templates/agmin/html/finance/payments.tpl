{include file='finance/finance_menu_part.tpl'}

{$meta_title='Финансы' scope=global}

<div class="two_columns_list">

   <div id=header>
      <div class="header_top">
         <h1 class="total_amount">
            Баланс:
            {foreach $total_amount as $ta}
               {if $ta->enabled OR $ta->amount > 0}
                  <div class="currency_amount">
                     <span class="sum_total">{$ta->amount|price_format:2:true} <span
                           class="sum_profit_price">{$ta->sign}</span></span>
                  </div>
               {/if}
            {/foreach}

            <div class="currency_amount">
               <span class="sum_total">{$total_dollars|price_format:2:true} <span class="sum_profit_price">Всего
                     $</span></span>
            </div>
         </h1>

         <div class="payments_types">
            {foreach $payments_types as $pt}
               <a class="add {$pt->type}" title="Создать {$pt->name}"
                  href="{url view=FinancePaymentAdmin cur_type=$pt->id}">{$pt->name}</a>
            {/foreach}
         </div>
      </div>

      <div class="grafic">
         <div id='financeByMonth'></div>
      </div>
   </div>


   <div id="right_menu" class="finance_menu">

      {if $payments || $keyword}
         <form class="form_select" method="get" id="search">
            <input type="hidden" name="view" value='FinancePaymentsAdmin' />
            <input class="search" type="text" name="keyword" value="{$keyword|escape}" placeholder="В комментариях" />
            <input class="search_button" type="submit" value="" />
         </form>
      {/if}

      <select class="form_select" name="payments_type">
         <option value="">Все транзакции</option>
         {foreach $payments_types as $pt}
            {if $pt->id != 2}
               <option {if $pt->type == $payments_type}selected{/if} value="{$pt->type}">{$pt->name}</option>
            {/if}
         {/foreach}
      </select>

      <select class="form_select" name="category_id">
         <option value="">Все категории</option>
         {if $categories_income|count > 0}
            <option disabled>─── Приход ───</option>
         {/if}
         {foreach $categories_income as $cat}
            <option {if $cat->id == $category_id} selected {/if} value="{$cat->id}">{$cat->name}</option>
         {/foreach}
         {if $categories_expense|count > 0 AND $categories_income|count > 0}
            <option disabled>─── Расход ───</option>
         {/if}
         {foreach $categories_expense as $cat}
            <option {if $cat->id == $category_id} selected {/if} value="{$cat->id}">{$cat->name}</option>
         {/foreach}
      </select>

      <div class="wallets">
         <div class='all {if !$purse_id}selected{/if}'>
            <a href="{url purse_id=null page=null}">Все кошельки</a>
         </div>
         <ul class="menu_list">
            {foreach $purses as $p}
               <li class='{if $p->id == $purse_id}selected{/if} {if $p->amount<0}minus{/if}'>
                  <a href="{url view=FinancePaymentsAdmin purse_id=$p->id category_id=$category_id clear=true}"
                     title="{$p->comment}">{$p->name}</a>
                  <div>{$p->amount|price_format:2:true} {$p->currency_sign}</div>
               </li>
            {/foreach}
         </ul>
      </div>
   </div>



   <div id="main_list" class="finance">
      {if $payments}

         {include file='parts/pagination.tpl'}

         <form id="form_list" method="post">
            <input type="hidden" name="session_id" value="{$smarty.session.id}" />

            <div class="list">
               {foreach $payments as $p}
                  <div class="row {if !$p->verified}verified_off{else}verified_on{/if}" item_id="{$p->id}">

                     <div class="payment_amount {if $p->type == 0}minus{/if} {if $p->related_payment_id}transfer{/if}">
                        <a href="{url view=FinancePaymentAdmin id=$p->id clear=true}">{if $p->type == 0}-{else}+{/if}{$p->amount|price_format:2:true}
                           {$p->currency_sign}</a>
                        {if $p->currency_rate!=1 AND !$p->related_payment_id}<div class="notice">{$p->currency_amount}
                           {$currency->sign}</div>{/if}
                     </div>

                     <div class="order_date">
                        <div class="date">{$p->date|date}</div>
                        <div class="time">{$p->date|time}</div>
                     </div>

                     <div class="user_name">
                        {if $p->category_name}
                           {$p->category_name}
                        {else}
                           Премещение между кошельками
                        {/if} <div class="notice">{$p->comment}</div>
                     </div>

                     <div class="user_email">
                        {$p->purse_name}

                        {if !$p->contractor->entity->name|empty}
                           <div class="notice">
                              <a
                                 href="{url view=$p->contractor->view_name id=$p->contractor->entity_id clear=true}">{$p->contractor->entity->name}</a>
                           </div>
                        {/if}
                     </div>

                     <div class="payment_status">
                        {if $p->images}
                           <img src="/{$config->templates_subdir}images/clipboard.png" alt="Фотоотчет" title="Фотоотчет">
                        {/if}
                     </div>

                     <div class="icons">
                        <a class="verified edit" title="Cверка с бухгалтерией"></a>
                     </div>

                  </div>
               {/foreach}
            </div>

         </form>

         {include file='parts/pagination.tpl'}

      {/if}
   </div>

</div>

<script src="/{$config->templates_subdir}js/highcharts/js/highcharts.js" type="text/javascript"></script>

<script>
   let session = '{$smarty.session.id}';
   let php_currency_name = '{$currency->name}';
   let php_currency_sign = '{$currency->sign}';


   $(function() {

      // Сделать проверенным
      $("a.verified.edit").click(function() {
         ajax_icon($(this), 'payment', 'verified', session);
         return false;
      });

      // Select gategory
      $('select[name="category_id"]').change(function() {
         var id = $(this).val();
         var category_id = '';

         if (id != '')
            category_id = '&category_id=' + id;

         var link = '{url category_id=null page=null}';
         window.location.href = link + category_id;
      });

      // Select payments_type
      $('select[name="payments_type"]').change(function() {
         var type = $(this).val();
         var payments_type = '';

         if (type != '')
            payments_type = '&payments_type=' + type;

         var link = '{url payments_type=null page=null category_id=null}';
         window.location.href = link + payments_type;
      });


      var options = {
         chart: {
            zoomType: 'x',
            renderTo: 'financeByMonth',
            defaultSeriesType: 'line',
            type: 'column'
         },
         title: {
            text: 'Финансовый график, по месяцам'
         },
         xAxis: {
            type: 'datetime',
            minRange: 30 * 24 * 3600000,
            maxZoom: 30 * 24 * 3600000,
            gridLineWidth: 1,
            ordinal: true,
            showEmpty: true
         },
         yAxis: {
            title: {
               text: php_currency_name
            }
         },
         plotOptions: {
            line: {
               dataLabels: {
                  enabled: true
               },
               enableMouseTracking: true,
               connectNulls: false
            },
            area: {
               marker: {
                  enabled: false
               },
            },
            series: {
               borderWidth: 0,
               dataLabels: {
                  enabled: true,
               }
            },
            column: {
               grouping: true,
               shadow: false,
               borderWidth: 0
            }
         },
         series: []
      }

      var chart;
      let purse_id = '{$purse_id}';
      let category_id = '{$category_id}';

      $.get('/app/agmin/ajax/stat/stat_finance.php?filter=byMonth&type=plus&purse_id=' +
         purse_id +
         '&category_id=' +
         category_id,
         function(data) {
            if (data[0] != null) {

               // Устанавливаем высоту графика
               $("#financeByMonth").css("height", "250px");

               var series = {
                  data: []
               }

               //console.log(data);

               var minDate = Date.UTC(data[0].month - 1, data[0].day),
                  maxDate = Date.UTC(data[data.length - 1].year, data[data.length - 1].month - 1);

               var newDates = [],
                  currentDate = minDate,
                  d;

               while (currentDate <= maxDate) {
                  d = new Date(currentDate);
                  newDates.push((d.getMonth() + 1) + '/' + d.getFullYear());
                  currentDate += (24 * 60 * 60 * 1000); // add one Месяц
               }

               //console.log(newDates);

               series.name = 'Сумма приходов, ' + php_currency_sign;

               // Iterate over the lines and add categories or series
               $.each(data, function(lineNo, line) {
                  series.data.push([Date.UTC(line.year, line.month - 1), parseInt(line.y)]);
               });

               // Добавляем данные в массив
               options.series.push(series);
            }

            $.get('/app/agmin/ajax/stat/stat_finance.php?filter=byMonth&type=minus&purse_id=' + purse_id +
               '&category_id=' + category_id,
               function(data) {
                  if (data[0] == null) {
                     chart = new Highcharts.Chart(options);
                  } else {
                     var series = {
                        data: []
                     }

                     //console.log(data);

                     var minDate = Date.UTC(data[0].month - 1, data[0].day),
                        maxDate = Date.UTC(data[data.length - 1].year, data[data.length - 1].month - 1);

                     var newDates = [],
                        currentDate = minDate,
                        d;

                     while (currentDate <= maxDate) {
                        d = new Date(currentDate);
                        newDates.push((d.getMonth() + 1) + '/' + d.getFullYear());
                        currentDate += (24 * 60 * 60 * 1000); // add one Месяц
                     }

                     //console.log(newDates);

                     series.name = 'Сумма расходов, ' + php_currency_sign;
                     series.color = 'rgba(248,161,63,1)';

                     // Iterate over the lines and add categories or series
                     $.each(data, function(lineNo, line) {
                        series.data.push([Date.UTC(line.year, line.month - 1), parseInt(line.y)]);
                     });

                     // Добавляем данные в график
                     options.series.push(series);

                     // Create the chart
                     chart = new Highcharts.Chart(options);
                  }
               });
         });
   });


   Highcharts.theme = {
      colors: ["#55BF3B", "#f45b5b", "#8085e9", "#8d4654", "#7798BF", "#aaeeee", "#ff0066", "#eeaaee", "#DF5353",
         "#7798BF", "#aaeeee"
      ],
      chart: {
         backgroundColor: null,
         style: {
            fontFamily: "Signika, serif"
         }
      },
      title: {
         style: {
            color: 'black',
            fontSize: '16px',
            fontWeight: 'bold'
         }
      },
      subtitle: {
         style: {
            color: 'black'
         }
      },
      tooltip: {
         borderWidth: 0
      },
      legend: {
         itemStyle: {
            fontWeight: 'bold',
            fontSize: '13px'
         }
      },
      xAxis: {
         labels: {
            style: {
               color: '#6e6e70'
            }
         }
      },
      yAxis: {
         labels: {
            style: {
               color: '#6e6e70'
            }
         }
      },
      plotOptions: {
         series: {
            shadow: false
         },
         candlestick: {
            lineColor: '#404048'
         },
         map: {
            shadow: false
         }
      },
      toolbar: {
         itemStyle: {
            color: '#CCC'
         }
      },

      // Highstock specific
      navigator: {
         xAxis: {
            gridLineColor: '#D0D0D8'
         }
      },
      rangeSelector: {
         buttonTheme: {
            fill: 'white',
            stroke: '#C0C0C8',
            'stroke-width': 1,
            states: {
               select: {
                  fill: '#D0D0D8'
               }
            }
         }
      },
      scrollbar: {
         trackBorderColor: '#C0C0C8'
      },

      // General
      background2: '#E0E0E8'
   }

   // Apply the theme
   var highchartsOptions = Highcharts.setOptions(Highcharts.theme);
</script>