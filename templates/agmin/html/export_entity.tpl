{$meta_title="Экспорт $entity_name" scope=global}

<style>
	.ui-progressbar-value {
		background-image: url(/{$config->templates_subdir}images/progress.gif);
		background-position: left;
		border-color: #009ae2;
	}
</style>


{if $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">
			{if $message_error == 'no_permission'}
				Установите права на запись в папку {$export_files_dir}
			{else}
				{$message_error}
			{/if}
		</span>
	</div>
{/if}


<div>
	<div class="header_top">
		<h1>Экспортировать {$entity_name} в CSV</h1>
	</div>

	{if $message_error != 'no_permission'}
		<div id='progressbar'></div>
		<input class="button_green" id="start" type="button" name="" value="Экспортировать" />
	{/if}
</div>

<script src="/{$config->templates_subdir}js/piecon/piecon.js"></script>

<script>
	var filter_arr = {$filter_arr};
	var export_file_url =  "{$export_file_url}";
	var entity = "{$entity}";

	{literal}	
		var in_process = false;

		$(function() {

			// On document load
			$('input#start').click(function() {

				Piecon.setOptions({fallback: 'force'});
				Piecon.setProgress(0);
				$("#progressbar").progressbar({ value: 0 });

				$("#start").hide('fast');
				do_export();

			});

			function do_export(page) {
				filter_arr['page'] = typeof(page) != 'undefined' ? page : 1;

				$.ajax({
					url: "/app/agmin/ajax/export/export_" + entity + ".php",
					data: filter_arr,
					dataType: 'json',
					success: function(data) {

						if (data && !data.end) {
							Piecon.setProgress(Math.round(100 * data.page / data.totalpages));
							$("#progressbar").progressbar({ value: 100 * data.page / data.totalpages });
							do_export(data.page * 1 + 1);
						} else {
							Piecon.setProgress(100);
							$("#progressbar").hide('fast');
							window.location.href = export_file_url;

						}
					},
					error: function(xhr, status, errorThrown) {
						alert(errorThrown + '\n' + xhr.responseText);
					}

				});

			}
		});
	{/literal}
</script>