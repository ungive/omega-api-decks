const TEXTAREA_HEIGHT = 'ta_height';
const TEXTAREA_VALUE = 'ta_value';
const NAVIGATION_SELECTED = 'nav_selected';

const elements = {
  deck: {
    image: document.querySelector('#deck-image'),
    link: document.querySelector('#deck-image-link')
  },
  submit: document.querySelector('#submit'),
  output: document.querySelector('#output'),
  textarea: document.querySelector('#deck-list')
};

if (cookie.get(TEXTAREA_HEIGHT))
  elements.textarea.style.height = cookie.get(TEXTAREA_HEIGHT) + 'px';

if (cookie.get(TEXTAREA_VALUE)) {
  elements.textarea.value = atob(cookie.get(TEXTAREA_VALUE));
  if (elements.textarea.value.length > 0)
    window.addEventListener('load', function () {
      computeOutputHeight();
      submit();
    });
}

if (cookie.get(NAVIGATION_SELECTED)) {
  const labelId = cookie.get(NAVIGATION_SELECTED);
  const label = document.getElementById(labelId);
  window.addEventListener('load', function () {
    label.click();
  });
}

document.body.classList.remove('invisible');

// Store the height of the text area
// in a cookie for consecutive page loads.
onClientResize(elements.textarea,
  function (element) {
    cookie.set(TEXTAREA_HEIGHT, element.offsetHeight, { sameSite: 'Strict' });
  }
);

// Focus a textarea immediately when clicking on its label.
for (label of document.querySelectorAll('label')) {
  const selector = 'label ~ textarea#' + label.htmlFor;
  const textarea = label.parentElement.querySelector(selector);

  if (!textarea)
    continue;

  label.addEventListener('mousedown', function (event) {
    setTimeout(function () {
      textarea.focus();
    }, 0);
  });
}

const allTabElements = document.querySelectorAll('#navigation > label');
let lastSelectedTab = document.querySelector('#navigation > label');

function selectTab (element) {
  const input = document.querySelector('#' + element.htmlFor);
  input.checked = true;

  const output = document.querySelector('#output');
  const previousHeight = output.style.height;
  const scrollOffset = document.documentElement.scrollTop;

  // Memorize the height so we don't jump to the top
  output.style.height = output.clientHeight + 'px';

  for (other of allTabElements)
    if (other !== element)
      other.classList.remove('selected');
  element.classList.add('selected');

  cookie.set(NAVIGATION_SELECTED, element.id, { sameSite: 'Strict' });
  setTimeout(function () {
    computeOutputHeight();

    // Reset everything
    output.style.height = previousHeight;
    document.documentElement.scrollTop = scrollOffset;
  }, 0);
}

// Add CSS class for selected navigation element.
for (element of allTabElements)
  element.addEventListener('click', function (event) {
    selectTab(this);
    lastSelectedTab = this;
  });

for (element of document.querySelectorAll('#output-convert textarea'))
  (function () {
    element.addEventListener('click', function (event) {
      this.select();
    });
  })();

// Handle auto-submission when:
//
// 1. Pasting input
// 2. Pressing the Enter key (with or without CTRL-modifier)
// 3. Navigating away from a line after changing it
//
(function () {
  let isTextChanged;
  let lastKeyCode;
  let keyCode;

  const _submit = function () {
    isTextChanged = false;
    submit();
  };

  const _submitChanged = function () {
    if (isTextChanged)
      return _submit();
  };

  elements.textarea.addEventListener('mousedown', _submitChanged);
  elements.textarea.addEventListener('focusout', _submitChanged);

  elements.textarea.addEventListener('keydown',
    function (event) {
      keyCode = event.keyCode;

      if (event.key === 'Enter' && event.ctrlKey)
        return _submit();

      if (isTextChanged)
        if (event.key === 'ArrowUp' || event.key === 'ArrowDown')
          return _submit();
    }
  );

  elements.textarea.addEventListener('keyup', function (event) {
    if (event.keyCode === 86 /* V */ && event.ctrlKey)
      return _submit();
  });

  elements.textarea.addEventListener('input',
    (function () {
      const handle = function (callback) {
        return function (event) {
          callback.call(this, event);
          lastKeyCode = keyCode;
        };
      };

      return handle(function (event) {
        if (this.value.trim().length === 0)
          cookie.remove(TEXTAREA_VALUE);

        if (lastKeyCode === 13 /* Enter */) return;

        if (keyCode === 13 /* Enter */)
          return _submit();

        if (event.inputType === 'insertFromPaste') {
          console.log(elements.textarea.value
            .replace(/\\n/g, '\n').replace(/\\"/g, '\"')
            .replace(/\\\\/g, '\\').replace(/\\\//g, '/'));
          elements.textarea.value = elements.textarea.value
            .replace(/\\n/g, '\n').replace(/\\"/g, '\"')
            .replace(/\\\\/g, '\\').replace(/\\\//g, '/');
          return _submit();
        }

        isTextChanged = true;
      });
    })()
  );

  elements.submit.addEventListener('mouseup', function () { this.blur(); });
  elements.submit.addEventListener('mousedown', _submitChanged);
})();

function computeOutputHeight () {
  document
    .querySelectorAll('#output textarea')
    .forEach(function (element) {
      const style = window.getComputedStyle(element);
      const pad = parseFloat(style.paddingBottom);

      element.style.height = 0;
      element.style.height = (element.scrollHeight + pad) + 'px';
    });
}

window.addEventListener('resize', computeOutputHeight);

// Factory for creating a new endpoint
const endpoint = function (endpoint, list, query) {
  query = query || {};
  query.list = list;
  return new Endpoint(endpoint, query);
};

// Shortcuts for specific endpoints
endpoint.convert = endpoint.bind(endpoint, 'convert');
endpoint.imageify = endpoint.bind(endpoint, 'imageify');

function submit () {
  console.debug('submit');

  let list = decodeURIComponent(elements.textarea.value);

  const endsLineFeed = list.endsWith('\n');
  list = list.trim() + (endsLineFeed ? '\n' : '');

  cookie.set(TEXTAREA_VALUE, btoa(list), { sameSite: 'Strict' });

  list = list.trim();

  const promises = [
    endpoint
      .imageify(list, { quality: 10 })
      .fetch()
      .then(function (res) {
        res.blob().then(handleImage);
        elements.deck.link.href = endpoint.imageify(list).url();
      }),
    endpoint
      .convert(list)
      .fetch()
      .then(function (res) {
        res.json().then(function (json) {
          handleConversion(json);
        });
      })
  ];

  return Promise
    .all(promises)
    .then(function (data) {
      elements.output.classList.remove('hidden');
      computeOutputHeight();
    })
    .catch(function (error) {
      elements.output.classList.remove('hidden');
      handleError(error);
    });
}

function hideError () {
  const element = document.querySelector('#navigation > #label-error');
  const error = document.querySelector('#output-error');
  const separator = error.parentElement.querySelector('span.separator');
  const content = error.parentElement.querySelector('span.content');

  error.value = '';
  content.innerText = '';
  separator.classList.add('hidden');
  element.classList.add('hidden');
  selectTab(lastSelectedTab);
  console.log(lastSelectedTab);
}

function showError () {
  const element = document.querySelector('#navigation > #label-error');
  const separator = document.querySelector('#output span.separator');

  separator.classList.remove('hidden');
  element.classList.remove('hidden');
  selectTab(element);
}

function handleError (data) {
  const error = document.querySelector('#output-error');
  const content = error.parentElement.querySelector('span.content');

  error.value = JSON.stringify(data, undefined, 2);

  const parts = data.meta.error.split(':');
  const message = parts[parts.length - 1].trim();
  content.innerText = message;

  computeOutputHeight();
  showError();
}

function handleImage (blob) {
  createObjectURL(blob)
    .then(function (url) {
      elements.deck.image.src = url;
    });
}

function handleConversion ({ meta, data }) {
  hideError();

  const omega = document.querySelector('#output-omega');
  const ydke = document.querySelector('#output-ydke');
  const ydk = document.querySelector('#output-ydk');
  const names = document.querySelector('#output-names');
  const json = document.querySelector('#output-json');

  omega.value = data.formats.omega;
  ydke.value = data.formats.ydke;
  ydk.value = data.formats.ydk;
  names.value = data.formats.names;
  json.value = JSON.stringify(JSON.parse(data.formats.json), undefined, 2);

  computeOutputHeight();
}
