<script src="/{$config->templates_subdir}js/tinymce/tinymce.min.js"></script>

<script>
	let php_theme = "{$settings->theme}";

	{literal}
		tinymce.init({
			selector: 'textarea.html_editor',
			language: 'ru',
			plugins: [
				'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'searchreplace',
				'visualblocks', 'code', 'fullscreen', 'media', 'table', 'wordcount',
				'visualchars', 'fullscreen', 'emoticons'
			],
			menubar: ' edit insert view format table tools',
			toolbar: 'undo redo | H1 H2 H3 | bold italic forecolor backcolor | alignleft aligncenter ' +
				'alignright alignjustify | bullist numlist outdent indent | link image | emoticons | ' +
				' code removeformat ',
			content_css: '/templates/' + php_theme + '/css/common.css',
			content_style: 'body { margin: 5px;}',
			promotion: false,
			branding: false,
			visualchars_default_state: true,
			image_title: true
		});
	{/literal}
</script>