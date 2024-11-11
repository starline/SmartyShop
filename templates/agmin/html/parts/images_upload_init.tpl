<script>
    let php_templ_subdir = '/{$config->templates_subdir}';

    {literal}
        $(function() {

            // Сортировка изображений
            $(".images ul").sortable({
                tolerance: "pointer",
                opacity: 0.90
            });

            // Удаление изображений
            $(".images").on('click', 'span.delete', function() {
                $(this).closest(".images").find("input[name='delete_image']").val('1'); // for alone photo
                $(this).closest("li").fadeOut(200, function() {
                    $(this).remove();
                });
                return false;
            });

            // Загрузить изображение с компьютера
            $('.upload_image').click(function() {
                let name = $(this).closest('.images').attr('id');
                $("<input class='upload_image' name=" + name +
                        "[] type=file multiple  accept='image/jpeg,image/png,image/gif,image/webp' />")
                    .appendTo('#' + name + ' .add_image').focus().click();
            });

            // Или с URL
            $('.add_image_url').click(function() {
                let name = $(this).closest('.images').attr('id');
                $("<input class='remote_image' name=" + name + "_urls[] type=text value='http://'>")
                    .appendTo('#' + name + ' .add_image').focus().select();
            });

            // Или перетаскиванием
            if (window.File && window.FileReader && window.FileList) {
                $(document).on('dragenter', function(e) {
                    $(".dropZone").css('border', '1px dotted var(--lightin-color)');
                });

                $(".dropZone").on('dragover', function(e) {
                    $(this).css('border', '1px dotted var(--lightin-color)').css('background-color',
                        'var(--lightin-dim-color)');
                });

                $(".dropZone").on('dragleave', function(e) {
                    $(".dropZone").css('border', '1px solid var(--border-color)').css('background-color',
                        'var(--background-inside-color)'
                    );
                });

                $('.images').on("change", '.dropInput', handleFileSelect);
            }
        });


        function handleFileSelect(evt) {

            $(".dropZone").css('border', '').css('background-color', '');

            let files = evt.target.files; // FileList object
            let dropInput = $(this).first();
            let name = $(this).closest('.images').attr('id');

            // Loop through the FileList and render image files as thumbnails.
            for (var i = 0, file; file = files[i]; i++) {

                // Only process image files.
                if (!file.type.match('image.*')) {
                    continue;
                }
                let reader = new FileReader();

                // Closure to capture the file information.
                reader.onload = (function(theFile) {
                    return function(e) {

                        // Render thumbnail.
                        $("<li class=wizard>" +
                            "<span class='delete'>" +
                            "<img src='" + php_templ_subdir +
                            "images/cross-circle-frame.png'></span>" +
                            "<a href='" + e.target.result +
                            "' class='zoom' data-fancybox='images_content'>" +
                            "<img onerror='$(this).closest(\"li\").remove();' src='" +
                            e.target.result + "' />" +
                            "<input name=" + name + "_urls[] type='hidden' value='" +
                            theFile.name + "'/></a></li>").appendTo('#' + name + ' ul');

                        let temp_input = dropInput.clone().show();

                        $('#' + name + ' .dropInput').hide();
                        $('#' + name + ' .dropZone').prepend(temp_input);

                        initFancybox();
                    }
                })(file);

                // Read in the image file as a data URL.
                reader.readAsDataURL(file);
            }
        }

    {/literal}
</script>