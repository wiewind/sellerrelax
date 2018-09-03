/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
    config.skin = 'office2013';

    // The toolbar groups arrangement, optimized for two toolbar rows.
    config.toolbar = [
        [
            'Source', '-',
            'Undo', 'Redo', '-',
            'Link', 'Unlink', 'Anchor', 'Image', 'Flash', 'Table', '-',
            'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', '-', 'CodeSnippet', '-',
            'Find', 'Replace', '-',
            'Templates', 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-',
            'Print', 'SpellChecker', 'Scayt'
        ],
        '/',
        [
            'SelectAll', 'RemoveFormat',
            'Styles', 'Font', 'FontSize', 'Bold', 'Italic', 'Underline', 'Strike', 'TextColor', 'BGColor', '-',
            'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-',
            'NumberedList', 'BulletedList', '-',
            'Subscript', 'Superscript'
        ]
    ];

    config.enterMode = CKEDITOR.ENTER_BR;
    config.shiftEnterMode = CKEDITOR.ENTER_P;

    var dir = "/apps/lib/ckeditor";
    config.filebrowserBrowseUrl = dir + '/ckfinder/ckfinder.html';
    config.filebrowserImageBrowseUrl = dir + '/ckfinder/ckfinder.html?Type=Images';
    config.filebrowserFlashBrowseUrl = dir + '/ckfinder/ckfinder.html?Type=Flash';
    config.filebrowserUploadUrl = dir + '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
    config.filebrowserImageUploadUrl = dir + '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
    config.filebrowserFlashUploadUrl = dir + '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';


//    config.codeSnippet_theme = 'vs';
//    config.removePlugins = 'elementspath';
};