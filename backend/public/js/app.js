const API = '/api/tasks';

let currentFilter = '';
let pendingUpdate = null; // { id, currentStatus, nextStatus, title }

// ── Helpers ──────────────────────────────────────────────────────────────────

function setMsg(el, text, type) {
  el.textContent = text;
  el.className = `msg ${type}`;
}

function badge(value, prefix) {
  return `<span class="badge badge-${prefix}-${value} badge-${value}">${value.replace('_', ' ')}</span>`;
}

const transitions = { pending: 'in_progress', in_progress: 'done' };

// ── Render ────────────────────────────────────────────────────────────────────

function renderTasks(tasks) {
  const list = document.getElementById('taskList');

  if (!tasks.length) {
    list.innerHTML = '<p class="empty">No tasks found.</p>';
    return;
  }

  list.innerHTML = tasks.map(t => {
    const next = transitions[t.status];
    const canDelete = t.status === 'done';

    return `
    <div class="task-card" data-id="${t.id}">
      <div class="task-info">
        <div class="task-title">${escHtml(t.title)}</div>
        <div class="task-meta">Due: ${t.due_date} &nbsp;|&nbsp;
          ${badge(t.priority, 'priority')} &nbsp;
          ${badge(t.status, 'status')}
        </div>
      </div>
      <div class="task-actions">
        ${next ? `<button class="btn-status" onclick="openModal(${t.id},'${t.status}','${next}',\`${escHtml(t.title)}\`)">→ ${next.replace('_',' ')}</button>` : ''}
        <button class="btn-delete" onclick="deleteTask(${t.id})" ${canDelete ? '' : 'disabled'} title="${canDelete ? 'Delete' : 'Only done tasks can be deleted'}">Delete</button>
      </div>
    </div>`;
  }).join('');
}

function escHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/`/g,'&#96;');
}

// ── Load Tasks ────────────────────────────────────────────────────────────────

async function loadTasks(status = '') {
  const listMsg = document.getElementById('listMsg');
  const url = status ? `${API}?status=${status}` : API;

  try {
    const res  = await fetch(url);
    const data = await res.json();

    if (!res.ok) { setMsg(listMsg, data.error || 'Failed to load tasks.', 'error'); return; }

    setMsg(listMsg, '', '');
    renderTasks(Array.isArray(data) ? data : (data.data ?? []));
  } catch {
    setMsg(listMsg, 'Network error.', 'error');
  }
}

// ── Create Task ───────────────────────────────────────────────────────────────

document.getElementById('createForm').addEventListener('submit', async e => {
  e.preventDefault();
  const msg = document.getElementById('createMsg');

  const body = {
    title:    document.getElementById('title').value.trim(),
    due_date: document.getElementById('due_date').value,
    priority: document.getElementById('priority').value,
  };

  try {
    const res  = await fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
    const data = await res.json();

    if (!res.ok) { setMsg(msg, data.error || 'Failed to create task.', 'error'); return; }

    setMsg(msg, `Task "${data.title}" created!`, 'success');
    e.target.reset();
    loadTasks(currentFilter);
  } catch {
    setMsg(msg, 'Network error.', 'error');
  }
});

// ── Update Status (Modal) ─────────────────────────────────────────────────────

function openModal(id, current, next, title) {
  pendingUpdate = { id, next };
  document.getElementById('modalTaskTitle').textContent = `"${title}"`;
  document.getElementById('transitionHint').textContent = `${current.replace('_',' ')} → ${next.replace('_',' ')}`;
  document.getElementById('modal').classList.remove('hidden');
}

document.getElementById('confirmUpdate').addEventListener('click', async () => {
  if (!pendingUpdate) return;
  const { id, next } = pendingUpdate;
  closeModal();

  try {
    const res  = await fetch(`${API}/${id}/status`, { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ status: next }) });
    const data = await res.json();
    const listMsg = document.getElementById('listMsg');

    if (!res.ok) { setMsg(listMsg, data.error || 'Update failed.', 'error'); return; }

    loadTasks(currentFilter);
  } catch {
    setMsg(document.getElementById('listMsg'), 'Network error.', 'error');
  }
});

document.getElementById('cancelUpdate').addEventListener('click', closeModal);
document.getElementById('modal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal(); });

function closeModal() {
  pendingUpdate = null;
  document.getElementById('modal').classList.add('hidden');
}

// ── Delete Task ───────────────────────────────────────────────────────────────

async function deleteTask(id) {
  if (!confirm('Delete this task?')) return;
  const listMsg = document.getElementById('listMsg');

  try {
    const res  = await fetch(`${API}/${id}`, { method: 'DELETE' });
    const data = await res.json();

    if (!res.ok) { setMsg(listMsg, data.error || 'Delete failed.', 'error'); return; }

    setMsg(listMsg, data.message, 'success');
    loadTasks(currentFilter);
  } catch {
    setMsg(listMsg, 'Network error.', 'error');
  }
}

// ── Filter Buttons ────────────────────────────────────────────────────────────

document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentFilter = btn.dataset.status;
    loadTasks(currentFilter);
  });
});

// ── Init ──────────────────────────────────────────────────────────────────────

// Set min date on due_date input to today
document.getElementById('due_date').min = new Date().toISOString().split('T')[0];

loadTasks();
