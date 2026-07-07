// assets/js/tinymce-config.js

function initTinyMCE(selector, options = {}) {
    const defaultOptions = {
        height: 500,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount',
            'emoticons', 'template', 'codesample', 'pagebreak'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic underline strikethrough | ' +
            'fontselect fontsizeselect | ' +
            'alignleft aligncenter alignright alignjustify | ' +
            'bullist numlist outdent indent | ' +
            'link image media | ' +
            'forecolor backcolor removeformat | ' +
            'code fullscreen preview | ' +
            'emoticons table | help',
        toolbar_mode: 'sliding',
        content_style: `
            body { 
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                font-size: 16px;
                line-height: 1.8;
                color: #333;
                padding: 20px;
                max-width: 100%;
            }
            h1 { font-size: 28px; font-weight: 700; color: #2c3e50; margin: 20px 0 10px 0; }
            h2 { font-size: 24px; font-weight: 600; color: #2c3e50; margin: 18px 0 10px 0; }
            h3 { font-size: 20px; font-weight: 600; color: #2c3e50; margin: 16px 0 10px 0; }
            h4 { font-size: 18px; font-weight: 600; color: #2c3e50; margin: 14px 0 10px 0; }
            p { margin-bottom: 15px; line-height: 1.8; }
            ul, ol { margin: 10px 0 15px 25px; }
            li { margin-bottom: 5px; }
            a { color: #3498db; text-decoration: none; }
            a:hover { text-decoration: underline; }
            blockquote { 
                border-left: 4px solid #3498db; 
                padding: 10px 20px; 
                margin: 15px 0; 
                background: #f8f9fa;
                font-style: italic;
            }
            code { 
                background: #f8f9fa; 
                padding: 2px 6px; 
                border-radius: 4px; 
                font-family: 'Courier New', monospace;
                font-size: 14px;
                color: #e74c3c;
            }
            pre {
                background: #2c3e50;
                color: #fff;
                padding: 15px;
                border-radius: 8px;
                overflow-x: auto;
                margin: 15px 0;
            }
            pre code {
                background: transparent;
                color: #fff;
                padding: 0;
                font-size: 14px;
            }
            img { 
                max-width: 100%; 
                height: auto; 
                border-radius: 8px; 
                margin: 10px 0;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 15px 0;
            }
            table th, table td { 
                border: 1px solid #ddd; 
                padding: 10px 15px; 
                text-align: left;
            }
            table th { 
                background: #f8f9fa; 
                font-weight: 600;
            }
            table tr:nth-child(even) {
                background: #f8f9fa;
            }
            .mce-content-body {
                padding: 20px !important;
            }
        `,
        font_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; Georgia=georgia,times new roman,serif; Inter=inter,arial,helvetica,sans-serif; Verdana=verdana,geneva,sans-serif; Tahoma=tahoma,arial,helvetica,sans-serif; Times New Roman=times new roman,times,serif',
        fontsize_formats: '8pt 10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt 72pt',
        image_advtab: true,
        image_title: true,
        automatic_uploads: true,
        file_picker_types: 'image',
        paste_data_images: true,
        paste_as_text: false,
        paste_auto_cleanup_on_paste: true,
        paste_remove_styles: false,
        paste_remove_styles_if_webkit: false,
        paste_strip_class_attributes: 'all',
        paste_retain_style_properties: 'all',
        paste_merge_formats: true,
        paste_word_valid_elements: 'b,strong,i,em,h1,h2,h3,h4,h5,h6,p,ol,ul,li,table,tr,td,th,div,span,a,img,pre,code,blockquote',
        browser_spellcheck: true,
        contextmenu: 'link image table',
        quickbars_selection_toolbar: 'bold italic underline | formatselect | bullist numlist | quicklink',
        quickbars_insert_toolbar: 'quickimage quicktable',
        quickbars_image_toolbar: 'alignleft aligncenter alignright | rotateleft rotateright | imageoptions',
        advlist_bullet_styles: 'square circle disc',
        advlist_number_styles: 'lower-alpha lower-roman upper-alpha upper-roman',
        lists_indent_on_tab: true,
        table_default_attributes: {
            border: '1'
        },
        table_default_styles: {
            borderCollapse: 'collapse',
            width: '100%'
        },
        table_advtab: true,
        table_cell_advtab: true,
        table_row_advtab: true,
        setup: function(editor) {
            editor.on('init', function() {
                console.log('TinyMCE initialized successfully');
            });
            
            // Auto-save content
            editor.on('change', function() {
                const content = editor.getContent();
                // Get lesson ID from URL or use 0 for new
                const urlParams = new URLSearchParams(window.location.search);
                const lessonId = urlParams.get('id') || '0';
                localStorage.setItem('lesson_content_' + lessonId, content);
            });
            
            // Handle image paste
            editor.on('paste', function(e) {
                const items = (e.clipboardData || e.originalEvent.clipboardData).items;
                for (let i = 0; i < items.length; i++) {
                    if (items[i].type.indexOf('image') !== -1) {
                        const file = items[i].getAsFile();
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            editor.insertContent('<img src="' + ev.target.result + '" alt="Pasted image" style="max-width:100%;height:auto;" />');
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });
        }
    };
    
    const mergedOptions = { ...defaultOptions, ...options };
    mergedOptions.selector = selector;
    
    if (typeof tinymce !== 'undefined') {
        tinymce.init(mergedOptions);
    } else {
        console.error('TinyMCE not loaded. Check your API key.');
    }
}

// Auto-initialize if selector is provided in data attribute
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('[data-tinymce]');
    textareas.forEach(function(textarea) {
        const options = textarea.dataset.tinymceOptions ? JSON.parse(textarea.dataset.tinymceOptions) : {};
        initTinyMCE('#' + textarea.id, options);
    });
});