function fixedEncodeURIComponent (str) {
  return encodeURIComponent(str).replace(/[!'()*]/g, function (c) {
    return '%' + c.charCodeAt(0).toString(16);
  });
}

function createObjectURL (blob) {
  return new Promise(function (resolve, reject) {
    const reader = new FileReader();
    reader.readAsDataURL(blob);
    reader.onloadend = function () {
      resolve(reader.result);
    };
    reader.onerror = reject;
  });
}

function flattenQuery (query) {
  const items = [];

  for (const key in query) {
    if (!query.hasOwnProperty(key))
      continue;
    const name = fixedEncodeURIComponent(key);
    const value = fixedEncodeURIComponent(query[key]);
    items.push([name, value].join('='));
  }

  return items.join('&');
}

function onTextAreaResize (element, callback) {
  let width = element.clientWidth;
  let height = element.clientHeight;

  window.addEventListener('mouseup', function () {
    if (element.clientWidth != width || element.clientHeight != height)
      return callback(element);

    width = element.clientWidth;
    height = element.clientHeight;
  });
}

function onClientResize (element, callback) {
  if (element.tagName === 'TEXTAREA')
    return onTextAreaResize(element, callback);
  throw 'Not implemented';
}

function getRequestToken() {
  return document.getElementById('data-request-token').getAttribute('data-request-token');
}

function Endpoint (name, query) {
  this.name = fixedEncodeURIComponent(name);
  this.query = query;
  query['token'] = getRequestToken();
}

Endpoint.prototype.url = function () {
  const name = fixedEncodeURIComponent(this.name);
  const queryString = flattenQuery(this.query);
  return [name, queryString].join('?');
};

Endpoint.prototype.fetch = function () {
  const url = this.url();
  return new Promise(function (resolve, reject) {
    fetch(url)
      .then(function (res) {
        if (res.status !== 200)
          return res.json().then(reject);
        resolve(res);
      })
      .catch(reject);
  });
};
