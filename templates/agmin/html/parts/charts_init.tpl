<script src="/{$config->templates_subdir}js/highcharts/js/highcharts.js"></script>

<script>
    /**
     * Выводим график
     */
    async function show_stat_graphic(element, dataParamObj, types_arr, my_options,
        currency_sign, callback) {
        if ($("div").is('#' + element)) {

            let temp_options = {
                chart: {
                    renderTo: element,
                    zoomType: 'x',
                    defaultSeriesType: 'line',
                    type: 'column'
                },
                title: {
                    text: 'Статистика продаж'
                },
                subtitle: {
                    text: 'Выручка по месяцам'
                },
                yAxis: [{
                    title: {
                        text: currency_sign
                    }
                }, {
                    title: {
                        text: 'Шт.'
                    },
                    min: 0,
                    opposite: true
                }],
                series: []
            }

            // Копируем базовые сайства
            Object.assign(temp_options, my_options);
            Object.assign(temp_options, options);

            for (const type of types_arr) {

                dataParamObj.type = type;
                dataParamStr = new URLSearchParams(dataParamObj).toString();

                await $.get('/app/agmin/ajax/stat/stat_orders.php?' + dataParamStr,
                    function(data) {
                        if (data == null || data[0] == null)
                            return false;

                        let series = {
                            data: []
                        }

                        //console.log(data);
                        let minDate = Date.UTC(data[0].month - 1, data[0].day),
                            maxDate = Date.UTC(data[data.length - 1].year, data[data.length - 1]
                                .month - 1),
                            newDates = [],
                            currentDate = minDate,
                            d;

                        while (currentDate <= maxDate) {
                            d = new Date(currentDate);
                            newDates.push((d.getMonth() + 1) + '/' + d.getFullYear());
                            currentDate += (24 * 60 * 60 * 1000); // add one Месяц
                        }

                        //console.log(newDates);  
                        if (dataParamObj.type == 'totalPrice') {
                            if (dataParamObj.manager_id)
                                series.name = 'Сумма дохода, ' + currency_sign;
                            else
                                series.name = 'Сумма заказов, ' + currency_sign;

                        } else if (dataParamObj.type == 'profitPrice') {
                            series.name = 'Сумма прибыли, ' + currency_sign;
                            series.color = 'rgba(248,161,63,1)';

                        } else if (dataParamObj.type == 'amount') {
                            if (dataParamObj.category_id || dataParamObj.product_id)
                                series.name = 'Продано, шт';
                            else
                                series.name = 'Колл-во заказов, шт';

                            series.pointPadding = '0.3';
                            series.pointPlacement = '-0.1';
                            series.color = 'rgba(0,0,0,1)';

                        } else if (dataParamObj.type == 'add') {
                            series.name = 'Поставка, шт';
                            series.type = "scatter";
                            series.color = '#673ab7';
                            {literal}
                                series.tooltip = {headerFormat: '<small>{point.key}</small><br>', pointFormat:'{series.name}: <b>{point.y}</b>', xDateFormat:'%B %Y'};
                            {/literal}
                            series.yAxis = 1;
                        } else if (dataParamObj.type == 'delete') {
                            series.name = 'Списано, шт';
                            series.color = '#f00';
                            series.pointPadding = '0.3';
                            series.pointPlacement = '-0.1';

                        } else if (dataParamObj.type == 'totalPayments') {
                            series.name = 'Сумма платежей, ' + currency_sign;
                            series.color = 'rgba(248,161,63,1)';
                        }

                        // Iterate over the lines and add categories or series
                        $.each(data, function(lineNo, line) {
                            if (dataParamObj.filter == 'byMonth') {
                                series.data.push([Date.UTC(line.year, line.month - 1),
                                    parseInt(line.y)
                                ]);
                            } else if (dataParamObj.filter == 'byDay') {
                                series.data.push([Date.UTC(line.year, line.month - 1, line.day),
                                    parseInt(line.y)
                                ]);
                            }
                        });

                        // Добавляем данные в массив
                        temp_options.series.push(series);
                    }
                );
            }

            // Если есть данные, выводим график
            if (temp_options.series.length > 0) {
                if (callback != null)
                    callback(true);
                else
                    $("#" + element).css("height", "250px");

                // Show chart
                let chart = new Highcharts.Chart(temp_options);
            } else {
                if (callback != null)
                    callback(false);
                $("#" + element).append("<div class='chart_none'>нет данных для отображения графика</div>");
            }
        }
    }

    let options = {
        xAxis: {
            type: 'datetime',
            minRange: 7 * 3600000,
            maxZoom: 7 * 3600000,
            gridLineWidth: 1,
            ordinal: true,
            showEmpty: true
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
                grouping: false,
                shadow: false,
                borderWidth: 0
            }
        }
    }

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
    };

    // Apply the theme
    Highcharts.setOptions(Highcharts.theme);
</script>