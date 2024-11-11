<head>
	<meta charset="utf-8" />
	<meta name="description" content="{$meta_description|escape}" />
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, initial-scale=1.0" />
	<meta name="theme-color" content="#6b514c" />

	<title>{$meta_title|escape}</title>

	<link href="/{$config->templates_subdir}{$settings->theme}/images/favicon.ico" rel="icon" type="image/x-icon">
	<link href="/{$config->templates_subdir}{$settings->theme}/images/favicon.ico" rel="shortcut icon"
		type="image/x-icon">

	{if isset($canonical)}
		<link rel="canonical" href="{$config->root_url}{$canonical}">
	{/if}

	{if isset($noindex) and $noindex == true}
		<meta name="robots" content="noindex" />
	{/if}

	{foreach $openGraph as $og}
		<meta property="{$og['property']}" content="{$og['content']}" />
	{/foreach}

	{$css_files = array(
		"/`$config->templates_subdir``$settings->theme`/css/common.css",
		"/`$config->templates_subdir``$settings->theme`/js/fancybox/jquery.fancybox.min.css"
	)}
	{combine input=$css_files output="/`$config->templates_subdir``$settings->theme`/css/combine.css" use_true_path=false age='180' debug=$config->smarty_combine}

	{$js_files = array(
		"/`$config->templates_subdir``$settings->theme`/js/jquery/jquery.js",
		"/`$config->templates_subdir``$settings->theme`/js/fancybox/jquery.fancybox.min.js",
		"/`$config->templates_subdir``$settings->theme`/js/autocomplete/jquery.autocomplete-min.js",
		"/`$config->templates_subdir``$settings->theme`/js/jquery/jquery.form.js",
		"/`$config->templates_subdir``$settings->theme`/js/common.js"
	)}
	{combine input=$js_files output="/`$config->templates_subdir``$settings->theme`/js/combine.js" use_true_path=false age='180' debug=$config->smarty_combine}

	{literal}
		<!-- Google Tag Manager -->
		<script>
		</script>
		<!-- End Google Tag Manager -->
	{/literal}

</head>