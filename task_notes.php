<?php
require_once 'auth_check.php';
checkLogin();

$user_id = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'staff';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Task | TMS</title>
    <link rel="icon" type="image/png" href="favicon.png?v=3">
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0" />

    <style>
        .notes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card-note {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card-note:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05);
        }

        .stat-icon-note {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            align-items: center;
            background: #ffffff;
            padding: 0.75rem 1rem;
            border-radius: 14px;
            border: 1px solid var(--border);
        }

        .filter-btn {
            background: #f8fafc;
            border: 1px solid var(--border);
            color: var(--text-sub);
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .filter-btn:hover {
            background: #f1f5f9;
            color: var(--text);
        }

        .filter-btn.active {
            background: #064e3b;
            color: #ffffff;
            border-color: #064e3b;
        }

        .note-item {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transition: all 0.2s;
            position: relative;
        }

        .note-item:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .note-item.done {
            background: #f8fafc;
            opacity: 0.75;
        }

        .note-item.done .note-title {
            text-decoration: line-through;
            color: var(--text-muted);
        }

        .note-checkbox {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            cursor: pointer;
            accent-color: #10b981;
            margin-top: 2px;
        }

        .priority-badge {
            font-size: 0.7rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .priority-high {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .priority-medium {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }

        .priority-low {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            padding: 1.5rem;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .modal-overlay.show {
            display: flex !important;
            opacity: 1;
        }

        .modal-card {
            background: #ffffff;
            border-radius: 20px;
            width: 100%;
            max-width: 480px;
            padding: 1.75rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            animation: modalFadeIn 0.25s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.96);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
            <div class="page-title" style="display: flex; align-items: center; gap: 1rem;">
                <div class="page-title-icon" style="background:#dcfce7; color:#059669; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: 28px;">checklist</span>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                        <h1 style="font-size: 1.5rem; font-weight: 800; margin: 0; color: var(--text);">Catatan Task Pribadi</h1>
                        <span style="background: rgba(16, 185, 129, 0.12); color: #065f46; border: 1px solid rgba(16, 185, 129, 0.3); padding: 3px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                            <span class="material-symbols-outlined" style="font-size: 15px; color: #10b981;">lock</span>
                            Privat: Hanya Akun Anda (<?php echo htmlspecialchars($user_name); ?>)
                        </span>
                    </div>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 3px 0 0 0;">Agenda kerja &amp; catatan personal — bersifat rahasia dan tidak dapat dilihat atau diakses oleh user lain.</p>
                </div>
            </div>
            <div class="page-actions">
                <button class="btn btn-primary" onclick="openAddModal()" style="background: #064e3b; color: white; border: none; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; box-shadow: 0 4px 12px rgba(6, 78, 59, 0.2);">
                    <span class="material-symbols-outlined">add_task</span>
                    Tambah Task Baru
                </button>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="notes-grid">
            <div class="stat-card-note">
                <div class="stat-icon-note" style="background: #ecfdf5; color: #059669;">
                    <span class="material-symbols-outlined">event_note</span>
                </div>
                <div>
                    <h3 id="statToday" style="font-size: 1.5rem; font-weight: 800; margin: 0; color: var(--text);">0</h3>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Tugas Hari Ini</span>
                </div>
            </div>

            <div class="stat-card-note">
                <div class="stat-icon-note" style="background: #eff6ff; color: #3b82f6;">
                    <span class="material-symbols-outlined">pending_actions</span>
                </div>
                <div>
                    <h3 id="statPending" style="font-size: 1.5rem; font-weight: 800; margin: 0; color: var(--text);">0</h3>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Belum Selesai</span>
                </div>
            </div>

            <div class="stat-card-note">
                <div class="stat-icon-note" style="background: #fef2f2; color: #ef4444;">
                    <span class="material-symbols-outlined">priority_high</span>
                </div>
                <div>
                    <h3 id="statHigh" style="font-size: 1.5rem; font-weight: 800; margin: 0; color: var(--text);">0</h3>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Prioritas Tinggi</span>
                </div>
            </div>

            <div class="stat-card-note">
                <div class="stat-icon-note" style="background: #f0fdf4; color: #16a34a;">
                    <span class="material-symbols-outlined">task_alt</span>
                </div>
                <div>
                    <h3 id="statCompleted" style="font-size: 1.5rem; font-weight: 800; margin: 0; color: var(--text);">0</h3>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Selesai</span>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-tabs">
            <button class="filter-btn active" onclick="setFilter('today')" id="filterToday">
                <span class="material-symbols-outlined" style="font-size: 16px;">today</span>
                Hari Ini
            </button>
            <button class="filter-btn" onclick="setFilter('pending')" id="filterPending">
                <span class="material-symbols-outlined" style="font-size: 16px;">hourglass_empty</span>
                Belum Selesai
            </button>
            <button class="filter-btn" onclick="setFilter('all')" id="filterAll">
                <span class="material-symbols-outlined" style="font-size: 16px;">list_alt</span>
                Semua Catatan
            </button>
            <div style="margin-left: auto; display: flex; align-items: center; gap: 0.5rem;">
                <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Tanggal:</label>
                <input type="date" id="customDateInput" onchange="filterByDate(this.value)" style="padding: 0.4rem 0.75rem; border-radius: 8px; border: 1px solid var(--border); font-size: 0.85rem; outline: none;">
            </div>
        </div>

        <!-- Task List Container -->
        <div id="notesContainer">
            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                <span class="material-symbols-outlined" style="font-size: 36px; animation: spin 1s infinite linear;">autorenew</span>
                <p>Memuat catatan tugas...</p>
            </div>
        </div>
    </div>

    <!-- Modal Form Catatan Task -->
    <div id="noteModal" class="modal-overlay" style="display: none;" onclick="if(event.target===this)closeNoteModal()">
        <div class="modal-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 id="modalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--text);">Tambah Catatan Task</h3>
                <button type="button" onclick="closeNoteModal()" style="background: none; border: none; cursor: pointer; color: var(--text-muted);">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="noteForm" onsubmit="saveNote(event)">
                <input type="hidden" id="noteId" name="id">

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--text);">Judul Task *</label>
                    <input type="text" id="noteTitle" name="title" required placeholder="Contoh: Follow up koli transit gudang A"
                           style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--border); font-size: 0.9rem; outline: none;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--text);">Tanggal Pelaksanaan *</label>
                        <input type="date" id="noteDate" name="note_date" required
                               style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--border); font-size: 0.9rem; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--text);">Jam / Pengingat</label>
                        <input type="time" id="noteTime" name="note_time"
                               style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--border); font-size: 0.9rem; outline: none;">
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--text);">Tingkat Prioritas</label>
                    <select id="notePriority" name="priority" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--border); font-size: 0.9rem; outline: none; background: white;">
                        <option value="low">Rendah (Low)</option>
                        <option value="medium" selected>Sedang (Medium)</option>
                        <option value="high">Tinggi (High) 🔥</option>
                    </select>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--text);">Catatan Rinci / Keterangan</label>
                    <textarea id="noteDescription" name="description" rows="3" placeholder="Tambahkan catatan atau checklist langkah kerja..."
                              style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--border); font-size: 0.9rem; outline: none; resize: vertical;"></textarea>
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" onclick="closeNoteModal()" class="btn btn-ghost" style="padding: 0.6rem 1.25rem; border-radius: 10px; border: 1px solid var(--border); background: #f8fafc; cursor: pointer; font-weight: 600;">Batal</button>
                    <button type="submit" id="btnSubmitNote" class="btn btn-primary" style="background: #064e3b; color: white; border: none; padding: 0.6rem 1.5rem; border-radius: 10px; font-weight: 700; cursor: pointer;">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toastContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 99999;"></div>

    <script>
        let currentFilter = 'today';
        let allNotes = [];

        function showToast(msg, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.style.cssText = `background: ${type === 'success' ? '#065f46' : '#dc2626'}; color: white; padding: 0.75rem 1.25rem; border-radius: 10px; font-size: 0.85rem; font-weight: 600; margin-top: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 8px; animation: slideIn 0.3s ease;`;
            toast.innerHTML = `<span class="material-symbols-outlined" style="font-size: 18px;">${type === 'success' ? 'check_circle' : 'error'}</span> <span>${msg}</span>`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        async function loadNotes() {
            try {
                let url = 'api.php?action=get_task_notes';
                if (currentFilter === 'today') {
                    const todayStr = new Date().toISOString().split('T')[0];
                    url += `&date=${todayStr}`;
                }

                const res = await fetch(url);
                allNotes = await res.json();
                renderNotes();
                updateStats();
            } catch (err) {
                console.error('Error loading notes:', err);
                document.getElementById('notesContainer').innerHTML = `<p style="text-align:center; color:#ef4444;">Gagal memuat catatan.</p>`;
            }
        }

        function updateStats() {
            const todayStr = new Date().toISOString().split('T')[0];
            const todayTasks = allNotes.filter(n => n.note_date === todayStr);
            const pendingTasks = allNotes.filter(n => parseInt(n.is_done) === 0);
            const highTasks = allNotes.filter(n => n.priority === 'high' && parseInt(n.is_done) === 0);
            const doneTasks = allNotes.filter(n => parseInt(n.is_done) === 1);

            document.getElementById('statToday').textContent = todayTasks.length;
            document.getElementById('statPending').textContent = pendingTasks.length;
            document.getElementById('statHigh').textContent = highTasks.length;
            document.getElementById('statCompleted').textContent = doneTasks.length;
        }

        function renderNotes() {
            const container = document.getElementById('notesContainer');
            let filtered = allNotes;

            if (currentFilter === 'pending') {
                filtered = allNotes.filter(n => parseInt(n.is_done) === 0);
            }

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 3rem 1.5rem; background: white; border-radius: 16px; border: 1px dashed var(--border);">
                        <span class="material-symbols-outlined" style="font-size: 48px; color: var(--text-muted); opacity: 0.5;">task</span>
                        <h4 style="font-size: 1.1rem; color: var(--text); margin: 0.5rem 0 0.25rem 0;">Tidak ada catatan tugas</h4>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Klik tombol "+ Tambah Catatan Baru" di atas untuk mencatat agenda kerja Anda.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = filtered.map(n => {
                const isDone = parseInt(n.is_done) === 1;
                const priorityClass = `priority-${n.priority}`;
                const priorityLabel = n.priority === 'high' ? 'Tinggi 🔥' : (n.priority === 'medium' ? 'Sedang' : 'Rendah');
                const timeText = n.note_time ? `<span style="display:inline-flex; align-items:center; gap:3px; font-size:0.75rem; color:var(--text-muted);"><span class="material-symbols-outlined" style="font-size:14px;">alarm</span> ${n.note_time.substring(0, 5)}</span>` : '';

                return `
                    <div class="note-item ${isDone ? 'done' : ''}" id="note-${n.id}">
                        <input type="checkbox" class="note-checkbox" ${isDone ? 'checked' : ''} onchange="toggleNote(${n.id})">
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.25rem;">
                                <span class="note-title" style="font-size: 0.95rem; font-weight: 700; color: var(--text);">${escapeHtml(n.title)}</span>
                                <span class="priority-badge ${priorityClass}">${priorityLabel}</span>
                                ${timeText}
                            </div>
                            ${n.description ? `<p style="margin: 0; font-size: 0.85rem; color: var(--text-sub); white-space: pre-wrap;">${escapeHtml(n.description)}</p>` : ''}
                            <div style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.4rem;">
                                <span class="material-symbols-outlined" style="font-size: 14px;">calendar_today</span>
                                <span>${n.note_date}</span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.25rem;">
                            <button onclick="editNote(${n.id})" style="background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 4px;" title="Edit Catatan">
                                <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                            </button>
                            <button onclick="deleteNote(${n.id})" style="background: none; border: none; cursor: pointer; color: #ef4444; padding: 4px;" title="Hapus Catatan">
                                <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function setFilter(filter) {
            currentFilter = filter;
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            if (filter === 'today') document.getElementById('filterToday').classList.add('active');
            if (filter === 'pending') document.getElementById('filterPending').classList.add('active');
            if (filter === 'all') document.getElementById('filterAll').classList.add('active');
            document.getElementById('customDateInput').value = '';
            loadNotes();
        }

        function filterByDate(date) {
            if (!date) return;
            currentFilter = 'custom';
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            fetch(`api.php?action=get_task_notes&date=${date}`)
                .then(r => r.json())
                .then(data => {
                    allNotes = data;
                    renderNotes();
                    updateStats();
                });
        }

        async function toggleNote(id) {
            try {
                const formData = new FormData();
                formData.append('id', id);
                const res = await fetch('api.php?action=toggle_task_note', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    const note = allNotes.find(n => parseInt(n.id) === parseInt(id));
                    if (note) {
                        note.is_done = note.is_done == 1 ? 0 : 1;
                        renderNotes();
                        updateStats();
                    }
                    // Refresh global sidebar badge if function exists
                    if (typeof updateTaskNotesBadge === 'function') updateTaskNotesBadge();
                }
            } catch (err) {
                showToast('Gagal memperbarui status catatan.', 'error');
            }
        }

        function openAddModal() {
            const modal = document.getElementById('noteModal');
            document.getElementById('modalTitle').textContent = 'Tambah Catatan Task';
            document.getElementById('noteId').value = '';
            document.getElementById('noteTitle').value = '';
            document.getElementById('noteDate').value = new Date().toISOString().split('T')[0];
            document.getElementById('noteTime').value = '';
            document.getElementById('notePriority').value = 'medium';
            document.getElementById('noteDescription').value = '';
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function editNote(id) {
            const note = allNotes.find(n => parseInt(n.id) === parseInt(id));
            if (!note) return;

            const modal = document.getElementById('noteModal');
            document.getElementById('modalTitle').textContent = 'Ubah Catatan Task';
            document.getElementById('noteId').value = note.id;
            document.getElementById('noteTitle').value = note.title;
            document.getElementById('noteDate').value = note.note_date;
            document.getElementById('noteTime').value = note.note_time || '';
            document.getElementById('notePriority').value = note.priority || 'medium';
            document.getElementById('noteDescription').value = note.description || '';
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function closeNoteModal() {
            const modal = document.getElementById('noteModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 200);
        }

        // Close on Escape key press
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeNoteModal();
        });

        async function saveNote(e) {
            e.preventDefault();
            const id = document.getElementById('noteId').value;
            const action = id ? 'edit_task_note' : 'add_task_note';

            const formData = new FormData(document.getElementById('noteForm'));
            const btn = document.getElementById('btnSubmitNote');
            btn.disabled = true;

            try {
                const res = await fetch(`api.php?action=${action}`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    showToast(id ? 'Catatan berhasil diperbarui!' : 'Catatan baru berhasil ditambahkan!');
                    closeNoteModal();
                    loadNotes();
                } else {
                    showToast('Gagal: ' + (data.error || 'Terjadi kesalahan'), 'error');
                }
            } catch (err) {
                showToast('Error koneksi ke server.', 'error');
            } finally {
                btn.disabled = false;
            }
        }

        async function deleteNote(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus catatan ini?')) return;

            try {
                const formData = new FormData();
                formData.append('id', id);
                const res = await fetch('api.php?action=delete_task_note', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Catatan berhasil dihapus.');
                    loadNotes();
                } else {
                    showToast('Gagal: ' + (data.error || 'Gagal menghapus'), 'error');
                }
            } catch (err) {
                showToast('Error koneksi.', 'error');
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // Initialize on load
        loadNotes();
    </script>
</body>
</html>
