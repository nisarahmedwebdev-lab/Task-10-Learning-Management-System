<?php
// config/tinymce.php

define('TINYMCE_API_KEY', 'cxlbvbj8wr546pct09uhrhcwv913ej87xh6hnyzz5zv6vz0e');

// TinyMCE Configuration
$tinymce_config = [
    'height' => 500,
    'menubar' => true,
    'plugins' => [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount',
        'emoticons', 'template', 'codesample', 'pagebreak'
    ],
    'toolbar' => 'undo redo | blocks | ' .
        'bold italic underline strikethrough | ' .
        'fontselect fontsizeselect | ' .
        'alignleft aligncenter alignright alignjustify | ' .
        'bullist numlist outdent indent | ' .
        'link image media | ' .
        'forecolor backcolor removeformat | ' .
        'code fullscreen preview | ' .
        'emoticons table | help',
    'toolbar_mode' => 'sliding',
    'font_formats' => 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; Georgia=georgia,times new roman,serif; Inter=inter,arial,helvetica,sans-serif; Verdana=verdana,geneva,sans-serif; Tahoma=tahoma,arial,helvetica,sans-serif; Times New Roman=times new roman,times,serif',
    'fontsize_formats' => '8pt 10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt 72pt',
    'image_advtab' => true,
    'image_title' => true,
    'automatic_uploads' => true,
    'file_picker_types' => 'image',
    'paste_data_images' => true,
    'browser_spellcheck' => true,
    'contextmenu' => 'link image table',
    'table_default_attributes' => ['border' => '1'],
    'table_default_styles' => ['borderCollapse' => 'collapse', 'width' => '100%']
];
?>

<script>
// Make config available to JavaScript
window.tinymceConfig = <?php echo json_encode($tinymce_config); ?>;
</script>