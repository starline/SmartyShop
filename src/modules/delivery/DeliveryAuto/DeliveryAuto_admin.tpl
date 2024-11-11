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

            <div class="tracking_info">
                <a target='_blank'
                    href='https://www.delivery-auto.com/uk-UA/Receipts/DetailsNew?id={$order->delivery_note}'>delivery-auto.com
                    →</a>
            </div>
        </div>
    {/if}
</div>

<script>
    {literal}
        $(function() {

            // Отправить SMS c накладной
            $("a.send-delivery-sms").click(function() {
                let icon = $(this);
                let line = icon.closest(".icons");
                let id = $('input[name="id"]').val();
                let state = line.hasClass('sms_button') ? 1 : 0;

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