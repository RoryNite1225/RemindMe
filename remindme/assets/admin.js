// 1. Animate the category bars
document.querySelectorAll('.bar i').forEach(bar => {
  requestAnimationFrame(() => { bar.style.width = bar.dataset.width + '%'; });
});

// 2. Search + status filter for the recent posts table
const search  = document.getElementById('search');
const buttons = document.querySelectorAll('#statusFilter button');
const rows    = document.querySelectorAll('#postsTable tr[data-status]');
const noMatch = document.getElementById('noMatch');
let status = 'all';

function applyFilter() {
  const q = search.value.trim().toLowerCase();
  let shown = 0;
  rows.forEach(row => {
    const ok = (status === 'all' || row.dataset.status === status) &&
               row.textContent.toLowerCase().includes(q);
    row.hidden = !ok;
    if (ok) shown++;
  });
  noMatch.hidden = shown > 0 || rows.length === 0;
}

search.addEventListener('input', applyFilter);
buttons.forEach(btn => btn.addEventListener('click', () => {
  buttons.forEach(b => b.classList.remove('on'));
  btn.classList.add('on');
  status = btn.dataset.status;
  applyFilter();
}));
