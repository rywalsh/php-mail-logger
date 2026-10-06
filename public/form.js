/*!
 * Contact form snippet. Include on any page:
 *   <div id="contact-form"></div>   (optional; otherwise appended where the script tag is)
 *   <script src="https://yoursite.com/form.js" async></script>
 */
(function () {
  var script = document.currentScript;
  var endpoint = new URL('submit.php', script.src).href;

  var host = document.getElementById('contact-form');
  if (!host) {
    host = document.createElement('div');
    script.parentNode.insertBefore(host, script);
  }
  var root = host.attachShadow({ mode: 'open' });

  function render(colors) {
  var c = colors || {};
  var vars = ':host{' +
    '--primary:' + (c.color_primary || '#2563eb') + ';' +
    '--btn-text:' + (c.color_button_text || '#ffffff') + ';' +
    '--text:' + (c.color_text || '#222222') + ';' +
    '--bg:' + (c.color_background || '#ffffff') + ';' +
    '--border:' + (c.color_border || '#bbbbbb') + '}';
  root.innerHTML =
    '<style>' + vars +
    ':host{display:block}' +
    '*{box-sizing:border-box}' +
    '.wrap{max-width:600px;width:100%;margin:0 auto;padding:16px;font:16px/1.4 system-ui,sans-serif;color:var(--text);background:var(--bg)}' +
    'label{display:block;margin:12px 0 4px;font-weight:600}' +
    'input,textarea{width:100%;padding:10px;border:1px solid var(--border);border-radius:6px;font:inherit;color:var(--text);background:var(--bg)}' +
    'input:focus,textarea:focus{outline:2px solid var(--primary);border-color:var(--primary)}' +
    'textarea{min-height:140px;resize:vertical}' +
    '.row{display:flex;gap:12px}.row>div{flex:1;min-width:0}' +
    '@media(max-width:480px){.row{flex-direction:column;gap:0}}' +
    'button{margin-top:16px;padding:12px 24px;border:0;border-radius:6px;background:var(--primary);color:var(--btn-text);font:inherit;font-weight:600;cursor:pointer;width:100%}' +
    'button:disabled{opacity:.6;cursor:default}' +
    '@media(min-width:481px){button{width:auto}}' +
    '.err{color:#b91c1c;font-size:14px;min-height:0}' +
    '.ok{padding:16px;background:#ecfdf5;border:1px solid #10b981;border-radius:6px}' +
    '.hp{position:absolute;left:-9999px;height:0;overflow:hidden}' +
    '</style>' +
    '<div class="wrap"><form novalidate>' +
    '<div class="row"><div><label for="name">Name</label><input id="name" name="name" autocomplete="name" required><div class="err" data-for="name"></div></div>' +
    '<div><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="email" required><div class="err" data-for="email"></div></div></div>' +
    '<label for="subject">Subject</label><input id="subject" name="subject"><div class="err" data-for="subject"></div>' +
    '<label for="message">Message</label><textarea id="message" name="message" required></textarea><div class="err" data-for="message"></div>' +
    '<div class="hp" aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></div>' +
    '<div class="err" data-for="form" role="alert"></div>' +
    '<button type="submit">Send message</button></form></div>';

  bind();
  }

  function bind() {
  var form = root.querySelector('form');
  var btn = root.querySelector('button');

  function showErrors(errs) {
    root.querySelectorAll('.err').forEach(function (el) { el.textContent = errs[el.dataset.for] || ''; });
  }

  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    var data = {};
    new FormData(form).forEach(function (v, k) { data[k] = v; });
    data.page_url = location.href;
    showErrors({});
    btn.disabled = true;
    btn.textContent = 'Sending…';

    fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
      .then(function (r) { return r.json().catch(function () { return {}; }); })
      .then(function (res) {
        if (res.ok) {
          var box = document.createElement('div');
          box.className = 'ok';
          box.setAttribute('role', 'status');
          box.textContent = res.message;
          form.replaceWith(box);
          return;
        }
        showErrors(res.errors || { form: res.error || 'Something went wrong.' });
        btn.disabled = false;
        btn.textContent = 'Send message';
      })
      .catch(function () {
        showErrors({ form: 'Network error. Please try again.' });
        btn.disabled = false;
        btn.textContent = 'Send message';
      });
  });
  }

  fetch(endpoint)
    .then(function (r) { return r.json(); })
    .then(function (res) { render(res.colors); })
    .catch(function () { render(); });
})();
