<div class="delivery_note">
    <span>Номер накладной</span>
    <input type="text" name="delivery_note" value="{$order->delivery_note}" {if !$can_edit}disabled{/if} />

    {if $order->delivery_note}
        <span class="icons {if $order->settings->delivery_sms}sms_button{/if}">
            <a class="send_sms send-delivery-sms" title="Отправить смс c накладной" href="#"></a>
        </span>
    {/if}

    {if $order->delivery_note}
        <div class='tracking_status'>
            <input name='delivery[module]' value='{$delivery->module}' type='hidden'>

            <div class='icons'>
                <a class='delivery_info_update' title='Проверить статус доставки' href='#'>Проверить статус
                    доставки</a>
            </div>

            <div class='tracking_status_content'>
                {$order->delivery_info}
            </div>

            <div class='tracking_info'>
                <a target='_blank'
                    href='http://novaposhta.ua/tracking/index/cargo_number/{$order->delivery_note}'>NovaPoshta.ua
                    →</a>
            </div>
        </div>
    {/if}
</div>

<script>
    {literal}
        $(function() {

            // Проверить статус доставки
            $("a.delivery_info_update").click(function() {
                var icon = $(this);
                var id = $('input[name="id"]').val();
                var module = $('input[name="delivery[module]"]').val();

                icon.addClass('loading_icon');

                $.ajax({
                    type: 'POST',
                    url: '/app/agmin/ajax/get_delivery.php',
                    data: {'id': id, 'module': module, 'request_type': 'checkTracking', 'session_id': session_id},
                    success: function(data) {
                        icon.removeClass('loading_icon');
                        $(".tracking_status_content").html(data);
                    },
                    dataType: 'json'
                });
                return false;
            });

            // Отправить SMS c накладной
            $("a.send-delivery-sms").click(function() {
                var icon = $(this);
                var line = icon.closest(".icons");
                var id = $('input[name="id"]').val();
                var state = line.hasClass('sms_button') ? 1 : 0;

                icon.addClass('loading_icon');

                $.ajax({
                    type: 'POST',
                    url: '/app/agmin/ajax/send_sms.php',
                    data: {'id': id, 'session_id': session_id, 'type': 'delivery'},
                    success: function(data) {
                        icon.removeClass('loading_icon');
                        if (!state)
                            line.addClass('sms_button');
                    },
                    dataType: 'json'
                });
                return false;
            });

        });
    {/literal}
</script>