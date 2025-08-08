<?php
// file: src/app/pulse/views/edit.php
// MIT License

namespace bravedave\pulse;

use bravedave\dvc\{strings, theme};

use function bravedave\dvc\esc;

// note: $dto and $title into the environment 
?>
<form id="<?= $_form = strings::rand() ?>" autocomplete="off">

  <input type="hidden" name="action" value="pulse-save">
  <input type="hidden" name="id" value="<?= $dto->id ?>">
  <input type="hidden" name="content" value="<?= esc($dto->content) ?>">
  <style>
    /*
     * Configure the z-index of the editor UI, so when inside a Bootstrap
     * modal, it will be rendered over the modal.
     */
    :root {
      --ck-z-default: 100;
      --ck-z-panel: calc(var(--ck-z-default) + 999);
    }

    .ck {
      &.ck-content:not(.ck-style-grid__button__preview):not(.ck-editor__nested-editable) {
        padding: 1em 1.5em;
        /* Make sure all content containers have some min height to make them easier to locate. */
        min-height: 300px;
        /* Set height dynamically */
      }
    }

    .ck.ck-editor__editable_inline>:first-child {
      margin-top: 0
    }

    .ck p {
      margin: 0 0 1rem;
    }
  </style>
  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>"
    aria-labelledby="<?= $_modal ?>Label" aria-modal="true" aria-hidden="true"
    data-bs-focus="false">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">

        <div class="modal-header <?= theme::modalHeader() ?>">
          <h5 class="modal-title" id="<?= $_modal ?>Label"><?= $this->title ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">

          <!-- --[title]-- -->
          <div class="form-floating mb-3">
            <input type="text" class="form-control" name="title" value="<?= $dto->title ?>"
              id="<?= $_uid = strings::rand() ?>" placeholder="title">
            <label for="<?= $_uid ?>">title</label>
          </div>

          <!-- --[content]-- -->
          <div class="border rounded">

            <div id="<?= $_uidContent = strings::rand() ?>"><?= $dto->content ?></div>
          </div>
        </div>

        <div class="modal-footer py-1">

          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    (_ => {
      const form = $('#<?= $_form ?>');
      const modal = $('#<?= $_modal ?>');

      modal.on('shown.bs.modal', () => {

        form.on('submit', function(e) {

          _.fetch.post.form(_.url('<?= $this->route ?>'), this).then(d => {

            if ('ack' == d.response) {

              modal.trigger('success');
              modal.modal('hide');
            } else {

              _.growl(d);
            }
          });

          // console.table( _data);

          return false;
        });

        form.find('input:not([type="hidden"]), select, textarea').first().focus();
      });
    })(_brayworth_);
  </script>
  <script type="module">
    const form = $('#<?= $_form ?>');

    /**
     * CKEditor 5 requires a license key.
     *
     * The "GPL" license key used below only allows you to use the open-source features.
     * To use the premium features, replace it with your commercial license key.
     * If you don't have one, you can get a trial license key from https://portal.ckeditor.com/checkout?plan=free.
     */
    const LICENSE_KEY = 'GPL';

    import {
      ClassicEditor,
      InlineEditor,
      Autoformat,
      Bold,
      Italic,
      BlockQuote,
      Base64UploadAdapter,
      CloudServices,
      Essentials,
      Heading,
      Image,
      ImageCaption,
      ImageStyle,
      ImageToolbar,
      ImageUpload,
      PictureEditing,
      Indent,
      Link,
      List,
      Mention,
      Paragraph,
      PasteFromOffice,
      Table,
      TableToolbar,
      TextTransformation
    } from '/js/ckeditor/ckeditor5.js';

    // Load CKEditor CSS dynamically
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/js/ckeditor/ckeditor5.css';
    document.head.appendChild(link);

    link.addEventListener('load', () => {

      const defaultConfig = {
        plugins: [
          Essentials,
          Autoformat,
          Bold,
          Italic,
          BlockQuote,
          CloudServices,
          Heading,
          Image,
          ImageCaption,
          ImageStyle,
          ImageToolbar,
          Base64UploadAdapter,
          Indent,
          Link,
          List,
          Mention,
          Paragraph,
          PasteFromOffice,
          PictureEditing,
          Table,
          TableToolbar,
          TextTransformation
        ],
        licenseKey: LICENSE_KEY,
        toolbar: [
          'undo',
          'redo',
          '|',
          'heading',
          '|',
          'bold',
          'italic',
          '|',
          'link',
          'insertTable',
          'blockQuote',
          '|',
          'bulletedList',
          'numberedList',
          '|',
          'outdent',
          'indent'
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
          toolbar: [
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side',
            '|',
            'toggleImageCaption',
            'imageTextAlternative'
          ]
        },
        link: {
          addTargetToExternalLinks: true,
          defaultProtocol: 'https://'
        },
        table: {
          contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
        }
      };

      const headerConfig = {
        plugins: [Essentials, Autoformat, Bold, Italic, Heading, Link, Paragraph],
        toolbar: ['heading', '|', 'bold', 'italic', 'link'],
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
        link: {
          addTargetToExternalLinks: true,
          defaultProtocol: 'https://'
        }
      };

      const inlineElementsIds = [
        '<?= $_uidContent ?>',
      ];

      inlineElementsIds.forEach(id => {
        const element = document.getElementById(id);

        if (!element) {
          return;
        }

        // InlineEditor.create(
        ClassicEditor.create(
            element,
            id === 'inline-header' ? headerConfig : defaultConfig
          )
          .then(editor => {
            window.editor = editor;

            // Monitor changes in the editor
            editor.model.document.on('change:data', () => {
              const content = editor.getData();
              form[0].content.value = content; // Save content back to the original div
            });
          })
          .catch(error => {
            console.error(error.stack);
          });
      });
    });
  </script>
</form>