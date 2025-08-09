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

  <script type="module">
    const _ = _brayworth_;
    const form = $('#<?= $_form ?>');
    const modal = $('#<?= $_modal ?>');
    const commonProperties = [
      'color', 'font-size', 'font-family', 'font-weight', 'font-style', 'text-align',
      'text-decoration', 'line-height', 'letter-spacing', 'word-spacing', 'vertical-align', 'white-space',
      'direction', 'text-transform', 'text-indent', 'text-shadow', 'margin', 'margin-top', 'margin-right',
      'margin-bottom', 'margin-left', 'padding', 'padding-top', 'padding-right', 'padding-bottom',
      'padding-left', 'border', 'border-top', 'border-right', 'border-bottom', 'border-left',
      'border-color', 'border-style', 'border-width', 'border-radius', 'box-shadow', 'background',
      'background-image', 'background-position', 'background-repeat', 'background-size', 'width',
      'height', 'min-width', 'min-height', 'max-width', 'max-height', 'overflow', 'display', 'position',
      'top', 'right', 'bottom', 'left', 'float', 'clear', 'opacity', 'z-index', 'cursor', 'visibility'
    ];
    const textProperties = [
      'color', 'background-color', 'font-size', 'font-family', 'font-weight', 'font-style', 'text-align',
      'text-decoration', 'line-height', 'letter-spacing', 'word-spacing', 'vertical-align', 'white-space',
      'direction', 'text-transform', 'text-indent', 'text-shadow'
    ];

    let editor = null;

    modal.on('shown.bs.modal', () => {

      form.on('submit', async function(e) {

        e.preventDefault();

        if (!editor) {
          _.growlError('editor not ready');
          return;
        }

        // Async function to resize inline images to their displayed size
        const resizeInlineImagesToDisplaySize = async function(html) {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const figures = doc.querySelectorAll('figure.image');

          // Helper to process a single image
          function processImage(figure, img) {
            return new Promise(resolve => {
              if (!img || !img.src.startsWith('data:image')) return resolve();

              // Get the displayed width from the figure's style or the img's width attribute
              let displayWidth = null;
              if (figure.style.width) {
                if (figure.style.width.endsWith('px')) {
                  displayWidth = parseInt(figure.style.width, 10);
                }
              }

              if (!displayWidth && img.width) displayWidth = img.width;
              if (!displayWidth) displayWidth = img.naturalWidth || 0;

              // max it at 640px
              // if (displayWidth > 640) displayWidth = 640;


              console.log('Processing image: Display width:', displayWidth);
              if (!displayWidth) return resolve();

              const tempImg = new window.Image();
              tempImg.onload = function() {
                if (tempImg.width > displayWidth) {
                  const scale = displayWidth / tempImg.width;
                  const canvas = document.createElement('canvas');
                  canvas.width = displayWidth;
                  canvas.height = tempImg.height * scale;
                  const ctx = canvas.getContext('2d');
                  ctx.drawImage(tempImg, 0, 0, canvas.width, canvas.height);
                  canvas.toBlob(blob => {
                    const reader = new FileReader();
                    reader.onloadend = () => {
                      img.src = reader.result;
                      img.width = displayWidth;
                      img.height = canvas.height;
                      resolve();
                    };
                    reader.readAsDataURL(blob);
                  }, img.type || 'image/jpeg', 0.92);
                } else {
                  resolve();
                }
              };
              tempImg.onerror = () => resolve();
              tempImg.src = img.src;
            });
          }

          // Collect all image processing promises
          const promises = [];
          figures.forEach(figure => {
            const img = figure.querySelector('img');
            promises.push(processImage(figure, img));
          });
          await Promise.all(promises);
          return doc.body.innerHTML;
        }

        const exportInlineStylesToAttributes = (html) => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          // Create a temporary container to attach to DOM for computedStyle
          const tempDiv = document.createElement('div');
          tempDiv.className = 'ck ck-content'; // Add any other relevant classes here
          tempDiv.style.position = 'absolute';
          tempDiv.style.left = '-99999px';
          tempDiv.style.top = '-99999px';
          document.body.appendChild(tempDiv);
          tempDiv.innerHTML = doc.body.innerHTML;

          tempDiv.querySelectorAll('*').forEach(el => {
            const computed = window.getComputedStyle(el);
            if (computed.width && computed.width.endsWith('px')) {
              el.setAttribute('width', parseInt(computed.width, 10));
            }
            if (computed.height && computed.height.endsWith('px')) {
              el.setAttribute('height', parseInt(computed.height, 10));
            }
            el.removeAttribute('style');
          });

          // Copy back to a new doc for serialization
          const outDoc = parser.parseFromString(tempDiv.innerHTML, 'text/html');
          document.body.removeChild(tempDiv);
          return outDoc.body.innerHTML;
        };

        function exportAllComputedStylesInline(html) {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const tempDiv = document.createElement('div');
          tempDiv.className = 'ck ck-content';
          tempDiv.style.position = 'absolute';
          tempDiv.style.left = '-99999px';
          tempDiv.style.top = '-99999px';
          document.body.appendChild(tempDiv);
          tempDiv.innerHTML = doc.body.innerHTML;

          // Recursive function to inline styles
          function inlineAllStyles(element) {
            if (element.nodeType !== Node.ELEMENT_NODE) return;
            const computedStyle = window.getComputedStyle(element);
            // List of 50 common CSS properties to inline
            // Text-related properties to skip for images
            // Default values for the 50 properties (CSS initial values)
            const defaultValues = {
              'color': '',
              'font-size': '16px',
              'font-family': '',
              'font-weight': '400',
              'font-style': 'normal',
              'text-align': 'start',
              'text-decoration': 'none solid rgb(33, 37, 41)',
              'line-height': 'normal',
              'letter-spacing': 'normal',
              'word-spacing': '0px',
              'vertical-align': 'baseline',
              'white-space': 'normal',
              'direction': 'ltr',
              'text-transform': 'none',
              'text-indent': '0px',
              'text-shadow': 'none',
              'margin': '0px',
              'margin-top': '0px',
              'margin-right': '0px',
              'margin-bottom': '0px',
              'margin-left': '0px',
              'padding': '0px',
              'padding-top': '0px',
              'padding-right': '0px',
              'padding-bottom': '0px',
              'padding-left': '0px',
              'border': '0px none rgb(33, 37, 41)',
              'border-top': '0px none rgb(33, 37, 41)',
              'border-right': '0px none rgb(33, 37, 41)',
              'border-bottom': '0px none rgb(33, 37, 41)',
              'border-left': '0px none rgb(33, 37, 41)',
              'border-color': 'rgb(33, 37, 41)',
              'border-style': 'none',
              'border-width': '0px',
              'border-radius': '0px',
              'box-shadow': 'none',
              'background': 'rgba(0, 0, 0, 0)',
              'background-image': 'none',
              'background-position': '0% 0%',
              'background-repeat': 'repeat',
              'background-size': 'auto',
              'width': 'auto',
              'height': 'auto',
              'min-width': '0px',
              'min-height': '0px',
              'max-width': 'none',
              'max-height': 'none',
              'overflow': 'visible',
              'display': 'inline',
              'position': 'static',
              'top': 'auto',
              'right': 'auto',
              'bottom': 'auto',
              'left': 'auto',
              'float': 'none',
              'clear': 'none',
              'opacity': '1',
              'z-index': 'auto',
              'cursor': 'auto',
              'visibility': 'visible'
            };
            let styleString = '';
            for (let property of commonProperties) {
              // Skip height and text properties for images
              if (/img|figure/i.test(element.tagName) && (property === 'height' || textProperties.includes(property))) continue;
              const value = computedStyle.getPropertyValue(property);
              // Only set if value is not the default (or if no default is known, always set)
              if (value && (defaultValues[property] === undefined || value !== defaultValues[property])) {
                styleString += `${property}:${value};`;
              }
            }
            if (styleString) {
              element.setAttribute('style', styleString);
            } else {
              element.removeAttribute('style');
            }
            for (let child of element.children) {
              inlineAllStyles(child);
            }
          }

          for (let child of tempDiv.children) {
            inlineAllStyles(child);
          }

          const outDoc = parser.parseFromString(tempDiv.innerHTML, 'text/html');
          document.body.removeChild(tempDiv);
          return outDoc.body.innerHTML;
        }

        // Async resize inline images to their displayed size before saving
        const rawContent = editor.getData();

        let processedContent = rawContent;

        // processedContent = exportInlineStylesToAttributes(processedContent);
        processedContent = exportAllComputedStylesInline(processedContent);
        // processedContent = await resizeInlineImagesToDisplaySize(processedContent);
        this.content.value = processedContent;
        // Convert computed styles to HTML attributes for images and tables

        // Note: Ensure /js/ckeditor/ckeditor5.css is included wherever this content is rendered for correct styling.


        _.fetch.post.form(_.url('<?= $this->route ?>'), this).then(d => {

          if ('ack' == d.response) {

            modal.trigger('success');
            modal.modal('hide');
          } else {

            _.growl(d);
          }
        });
      });

      form.find('input:not([type="hidden"]), select, textarea').first().focus();
    });

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
      Autoformat,
      Bold,
      Italic,
      BlockQuote,
      Base64UploadAdapter,
      // CloudServices,
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
      Link,
      List,
      Mention,
      Paragraph,
      PasteFromOffice,
      Table,
      TableToolbar,
      TextTransformation
    } from '/js/ckeditor/ckeditor5.js';

    // Ensure CKEditor CSS is loaded only once using a Promise
    const loadCKEditorCSS = (() => {
      let promise;
      return () => {
        if (promise) return promise;
        promise = new Promise((resolve, reject) => {
          // Check if the stylesheet is already present
          if ([...document.styleSheets].some(s => s.href && s.href.endsWith('/js/ckeditor/ckeditor5.css'))) {
            resolve();
            return;
          }
          const link = document.createElement('link');
          link.rel = 'stylesheet';
          link.href = '/js/ckeditor/ckeditor5.css';
          link.onload = resolve;
          link.onerror = reject;
          document.head.appendChild(link);
        });
        return promise;
      };
    })();

    loadCKEditorCSS().then(() => {

      const defaultConfig = {
        plugins: [
          Essentials,
          Autoformat,
          Bold,
          Italic,
          BlockQuote,
          // CloudServices,
          Heading,
          Image,
          ImageCaption,
          ImageResize,
          ImageStyle,
          ImageToolbar,
          ImageUpload,
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
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side',
            '|',
            'toggleImageCaption',
            'imageTextAlternative',
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

      [
        '<?= $_uidContent ?>',
      ].forEach(id => {
        const element = document.getElementById(id);

        if (!element) return;

        // InlineEditor.create(
        ClassicEditor.create(
            element,
            id === 'inline-header' ? headerConfig : defaultConfig
          )
          .then(editorInstance => {
            editor = editorInstance;

            // return;

            editor.model.document.on('change:data', () => {
              const view = editor.editing.view;
              const domRoot = view.getDomRoot();

              // Find all images in the DOM
              domRoot.querySelectorAll('img').forEach(img => {

                // Only process if the image src is a blob and width is set
                if ((img.src.startsWith('blob:') || img.src.startsWith('data:')) && img.width) {
                  // Prevent repeated resizing
                  if (img.dataset.resized === 'true') return;

                  const displayWidth = img.width;
                  const tempImg = new window.Image();
                  tempImg.onload = function() {

                    if (tempImg.width > displayWidth) {
                      const scale = displayWidth / tempImg.width;
                      const canvas = document.createElement('canvas');
                      canvas.width = displayWidth;
                      canvas.height = tempImg.height * scale;
                      const ctx = canvas.getContext('2d');
                      ctx.drawImage(tempImg, 0, 0, canvas.width, canvas.height);
                      canvas.toBlob(blob => {
                        const newUrl = URL.createObjectURL(blob);
                        img.src = newUrl;
                        img.width = canvas.width;
                        img.height = canvas.height;
                        img.setAttribute('width', canvas.width);
                        img.setAttribute('height', canvas.height);
                        // If parent is a <figure>, update its width/height attributes and style
                        const parent = img.parentElement;
                        if (parent && parent.tagName === 'FIGURE') {
                          parent.setAttribute('width', canvas.width);
                          parent.setAttribute('height', canvas.height);
                          parent.style.width = canvas.width + 'px';
                          parent.style.height = canvas.height + 'px';
                        }
                        img.dataset.resized = 'true'; // Mark as resized

                        console.log('Resized image to', canvas.width, 'x', canvas.height);
                      }, img.type || 'image/jpeg', 0.92);
                    }
                  };
                  tempImg.src = img.src;
                }
              });
            });
          })
          .catch(error => {
            console.error(error.stack);
          });
      });
    });
  </script>
</form>