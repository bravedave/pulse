<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 * 
 * MIT License
 *
*/

namespace cms\docs;

use bravedave\dvc\strings;

?>
<style>
  .ck {
    &.ck-content:not(.ck-style-grid__button__preview):not(.ck-editor__nested-editable) {
      padding: 1em 1.5em;
      /* Make sure all content containers have some min height to make them easier to locate. */
      min-height: 300px;
      height: calc(100vh - 150px);
      /* Set height dynamically */
    }
  }

  .ck.ck-powered-by {
    display: none;
  }
</style>
<div id="<?= $_editor = strings::rand()  ?>"></div>
<script type="module">
  const editorDiv = document.getElementById('<?= $_editor ?>');
  // ckeditor can be imported from /js/ckeditor/ckeditor5.js
  import {
    ClassicEditor,
    Autoformat,
    Bold,
    Italic,
    Underline,
    BlockQuote,
    Base64UploadAdapter,
    CloudServices,
    Essentials,
    Heading,
    Image,
    ImageCaption,
    ImageResize,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    PictureEditing,
    Indent,
    IndentBlock,
    Link,
    List,
    MediaEmbed,
    Mention,
    Paragraph,
    PasteFromOffice,
    Table,
    TableColumnResize,
    TableToolbar,
    TextTransformation
  } from '/js/ckeditor/ckeditor5.js';

  document.addEventListener('DOMContentLoaded', () => {
    // Load CKEditor CSS dynamically
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/js/ckeditor/ckeditor5.css';
    document.head.appendChild(link);

    link.addEventListener('load', () => {

      editorDiv.innerHTML = `
        <h1>Welcome to CKEditor 5</h1>
        <p>This is a rich text editor where you can create and format your content.</p>
        <ul>
          <li><strong>Bold</strong>, <em>Italic</em>, and <u>Underline</u> your text.</li>
          <li>Add <a href="https://ckeditor.com" target="_blank">links</a> to your content.</li>
          <li>Insert images, tables, and media embeds.</li>
          <li>Create lists, block quotes, and headings.</li>
        </ul>
        <p>Start editing and see the changes live!</p>
      `;

      ClassicEditor
        .create(document.querySelector('#<?= $_editor ?>'), {
          plugins: [
            Autoformat,
            BlockQuote,
            Bold,
            CloudServices,
            Essentials,
            Heading,
            Image,
            ImageCaption,
            ImageResize,
            ImageStyle,
            ImageToolbar,
            ImageUpload,
            Base64UploadAdapter,
            Indent,
            IndentBlock,
            Italic,
            Link,
            List,
            MediaEmbed,
            Mention,
            Paragraph,
            PasteFromOffice,
            PictureEditing,
            Table,
            TableColumnResize,
            TableToolbar,
            TextTransformation,
            Underline
          ],
          licenseKey: 'GPL',
          toolbar: [
            'undo',
            'redo',
            '|',
            'heading',
            '|',
            'bold',
            'italic',
            'underline',
            '|',
            'link',
            'uploadImage',
            'insertTable',
            'blockQuote',
            'mediaEmbed',
            '|',
            'bulletedList',
            'numberedList',
            '|',
            'outdent',
            'indent',
            '|'
          ],
          heading: {
            options: [{
                model: 'paragraph',
                title: 'Paragraph',
                class: 'ck-heading_paragraph'
              },
              {
                model: 'heading1',
                view: 'h1',
                title: 'Heading 1',
                class: 'ck-heading_heading1'
              },
              {
                model: 'heading2',
                view: 'h2',
                title: 'Heading 2',
                class: 'ck-heading_heading2'
              },
              {
                model: 'heading3',
                view: 'h3',
                title: 'Heading 3',
                class: 'ck-heading_heading3'
              },
              {
                model: 'heading4',
                view: 'h4',
                title: 'Heading 4',
                class: 'ck-heading_heading4'
              }
            ]
          },
          image: {
            resizeOptions: [{
                name: 'resizeImage:original',
                label: 'Default image width',
                value: null
              },
              {
                name: 'resizeImage:50',
                label: '50% page width',
                value: '50'
              },
              {
                name: 'resizeImage:75',
                label: '75% page width',
                value: '75'
              }
            ],
            toolbar: [
              'imageTextAlternative',
              'toggleImageCaption',
              '|',
              'imageStyle:inline',
              'imageStyle:wrapText',
              'imageStyle:breakText',
              '|',
              'resizeImage'
            ]
          },
          link: {
            addTargetToExternalLinks: true,
            defaultProtocol: 'https://'
          },
          table: {
            contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
          }
        })
        .then(editor => {
          window.editor = editor;

          // Set focus on the editor after initialization
          editor.editing.view.focus();

          // Monitor changes in the editor
          editor.model.document.on('change:data', () => {
            const content = editor.getData();
            editorDiv.innerHTML = content; // Save content back to the original div
            console.log('Content updated:', content);
          });
        })
        .catch(error => {
          console.error(error);
        });
    });
  });
</script>