// Guard all login-page behaviors so the same script can be safely reused elsewhere.
const email = document.getElementById('email');
const pass  = document.getElementById('password');
if (email && pass) {
  [email, pass].forEach(f => f.addEventListener('focus', () => f.removeAttribute('readonly')));
  function clearFields() { email.value = ''; pass.value = ''; }
  clearFields();
  setTimeout(clearFields, 150);
  window.addEventListener('pageshow', clearFields);
}

const toggle = document.getElementById('togglePass');
if (toggle && pass) {
  const eye    = toggle.querySelector('.eye');
  const eyeOff = toggle.querySelector('.eye-off');
  toggle.addEventListener('click', () => {
    const show = pass.type === 'password';
    pass.type = show ? 'text' : 'password';
    eye.hidden = show;
    eyeOff.hidden = !show;
    toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
  });
}

const form = document.getElementById('loginForm');
if (form) {
  form.addEventListener('submit', () => {
    const btn = form.querySelector('.submit');
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Logging in...';
    }
  });
}

const params = new URLSearchParams(window.location.search);
const errorBox = document.getElementById('error');
if (errorBox) {
  if (params.get('registered')) {
    errorBox.hidden = false;
    errorBox.textContent = 'Account created successfully. Please log in.';
    errorBox.style.borderColor = '#2ec4a0';
    errorBox.style.background = '#eafff5';
    errorBox.style.color = '#0b5d41';
  }
  if (params.get('error')) {
    errorBox.hidden = false;
    errorBox.textContent = 'Invalid email, password, or role.';
    const r = document.getElementById('r-' + params.get('role'));
    if (r) r.checked = true;
  }
}

const titles = { admin: 'Admin Dashboard', faculty: 'Faculty Dashboard', student: 'Student Dashboard' };
const roleInputs = document.querySelectorAll('input[name="role"]');
if (roleInputs.length) {
  function updateTitle() {
    const role = document.querySelector('input[name="role"]:checked')?.value;
    const titleEl = document.getElementById('formTitle');
    if (role && titleEl && titles[role]) {
      titleEl.textContent = titles[role] + ' Login';
    }
  }
  roleInputs.forEach(r => r.addEventListener('change', updateTitle));
  updateTitle();
}
