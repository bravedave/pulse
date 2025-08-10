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
    /**
     * https://ckeditor.com/docs/ckeditor5/latest/getting-started/installation/css.html
     *
     * Configure the z-index of the editor UI, so when inside a Bootstrap
     * modal, it will be rendered over the modal.
     *
     * note also, the data attribute `data-bs-focus="false"` is used to prevent Bootstrap
     * from automatically focusing the first focusable element in the modal when it is opened.
     */
    :root {
      --ck-z-default: 100;
      --ck-z-panel: calc(var(--ck-z-default) + 999);
    }

    .ck {
      &.ck-content:not(.ck-style-grid__button__preview):not(.ck-editor__nested-editable) {
        padding: 1em 1.5em;
        /* Make sure all content containers have 
           a min height to make them easier to locate. */
        min-height: 300px;
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

            <div class="js-loading d-flex py-5">
              <div class="spinner-border text-primary m-auto my-5"></div>
            </div>
            <div class="d-none" id="<?= $_uidContent = strings::rand() ?>"><?= $dto->content ?></div>
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
      const content = modal.find('#<?= $_uidContent ?>');

      let editor = null;
      let exportInline = null;

      modal.on('shown.bs.modal', () => {

        form.on('submit', async function(e) {

          e.preventDefault();

          if (!editor) {
            _.growlError('editor not ready');
            return;
          }

          const rawContent = editor.getData();
          let processedContent = exportInline(rawContent, {
            width: <?= config::pulse_width ?>
          });
          // console.log('raw content:', rawContent);
          // console.log('Processed content:', processedContent);

          this.content.value = processedContent;
          _.fetch.post.form(_.url('<?= $this->route ?>'), this)
            .then(d => {

              if ('ack' == d.response) {

                modal.trigger('success');
                modal.modal('hide');
              } else {

                _.growl(d);
              }
            });

          return false;
        });

        form.find('input:not([type="hidden"]), select, textarea').first().focus();
      });

      import('/js/ckeditor')
        .then(({
          getCKEditor,
          enforceImageWidth,
          exportAllComputedStylesInline
        }) => {

          getCKEditor().then(({
            ClassicEditor,
            config
          }) => {

            modal.find('.js-loading').remove();
            content.removeClass('d-none');

            ClassicEditor.create(content[0], config)
              .then(editorInstance => {

                editor = editorInstance;
                exportInline = exportAllComputedStylesInline
                editor.model.document.on('change:data', () => enforceImageWidth(editor));
              })
              .catch(error => console.error(error.stack));
          });
        });
    })(_brayworth_);
  </script>
</form>