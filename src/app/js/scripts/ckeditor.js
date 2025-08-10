/**
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 * 
 * MIT License
 *
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

const commonProperties = [
  'color', 'clear', 'font-size', 'font-family', 'font-weight', 'font-style', 'text-align',
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

const config = {
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

const enforceImageWidth = editor => {
  const view = editor.editing.view;
  const domRoot = view.getDomRoot();
  const maxWidth = domRoot.getBoundingClientRect().width;

  // Find all images in the DOM
  domRoot.querySelectorAll('img').forEach(img => {

    // Only process if the image src is a blob and width is set
    if ((img.src.startsWith('blob:') || img.src.startsWith('data:')) && img.width) {

      // Prevent repeated resizing
      if (img.dataset.resized === 'true') return;

      const displayWidth = img.width;
      if (displayWidth > maxWidth) {

        console.warn('Image exceeds editor width, resizing:', displayWidth, '>', maxWidth);

        const tempImg = new window.Image();
        tempImg.onload = function () {

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
      } else {

        console.log('Image within editor width:', displayWidth, '<=', maxWidth);
      }
    }
  });
};

const exportAllComputedStylesInline = (html, options = {}) => {
  const parser = new DOMParser();
  const doc = parser.parseFromString(html, 'text/html');
  const tempDiv = document.createElement('div');

  tempDiv.className = 'ck ck-content';
  tempDiv.style.position = 'absolute';
  tempDiv.style.left = '-99999px';
  tempDiv.style.top = '-99999px';
  // Optionally set width if provided in options
  if (options.width) {
    tempDiv.style.width = typeof options.width === 'number' ? `${options.width}px` : options.width;
  }
  document.body.appendChild(tempDiv);
  tempDiv.innerHTML = doc.body.innerHTML;

  // Recursive function to inline styles
  const inlineAllStyles = element => {
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

  // get the width of the content?
  const contentWidth = tempDiv.scrollWidth;
  const tempDivWidth = tempDiv.getBoundingClientRect().width;
  console.log('Content scrollWidth:', contentWidth, 'tempDiv getBoundingClientRect width:', tempDivWidth);

  const outDoc = parser.parseFromString(tempDiv.innerHTML, 'text/html');
  document.body.removeChild(tempDiv);
  return outDoc.body.innerHTML;
};

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

const getCKEditor = () => new Promise((resolve, reject) => {

  if (typeof ClassicEditor !== 'undefined') {

    // Ensure the CSS is loaded before resolving
    loadCKEditorCSS()
      .then(() => {
        resolve({
          ClassicEditor,
          config
        });
      })
  } else {

    reject(new Error('CKEditor is not loaded'));
  }
});

export { getCKEditor, enforceImageWidth, exportAllComputedStylesInline };