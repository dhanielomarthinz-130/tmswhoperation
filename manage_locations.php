<?php
require_once 'auth_check.php';
checkLogin();
checkAccess('locations');
$can_write = canWriteMenu('locations');
$user_role = $_SESSION['role'] ?? '';

// Specific rules for 'admin' role
$can_add = $can_write;
$can_edit = $can_write;
$can_delete = $can_write;

if ($user_role === 'admin') {
    $can_add = true;    // Admin can add
    $can_edit = false;  // Admin cannot edit
    $can_delete = false; // Admin cannot delete
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Lokasi | TMS</title>
    <link rel="icon" type="image/png" href="favicon.png?v=3">
    <style>
        .coord-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .coord-mode-toggle {
            display: flex;
            background: var(--surface-2);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
            margin-bottom: 0.75rem;
        }

        .coord-mode-btn {
            flex: 1;
            padding: 0.55rem;
            border: none;
            background: none;
            font-family: 'Inter', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.18s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
        }

        .coord-mode-btn .material-symbols-outlined {
            font-size: 16px;
        }

        .coord-mode-btn.active {
            background: var(--primary);
            color: white;
        }

        .gmaps-input-wrap {
            display: flex;
            gap: 0.5rem;
            align-items: stretch;
        }

        .gmaps-input-wrap input {
            flex: 1;
        }

        .btn-resolve {
            padding: 0 0.875rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            white-space: nowrap;
            transition: 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .btn-resolve:hover {
            background: var(--primary-dark);
        }

        .btn-resolve .material-symbols-outlined {
            font-size: 16px;
        }

        .btn-resolve:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .resolve-result {
            margin-top: 0.5rem;
            font-size: 0.8rem;
            display: none;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-sm);
        }

        .resolve-result .material-symbols-outlined {
            font-size: 16px;
        }

        .resolve-ok {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .resolve-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .type-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.75rem;
        }

        .type-store {
            color: #7c3aed;
        }

        .type-warehouse {
            color: #0369a1;
        }

        .type-office {
            color: #0f172a;
        }

        .addr-cell {
            font-size: 0.72rem;
            color: var(--text-muted);
            max-width: 280px;
            white-space: normal;
            line-height: 1.4;
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(6px);
            padding: 1.5rem;
        }

        .modal-overlay.show {
            display: flex;
        }

        /* Google Maps link in table */
        .maps-link {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: #1a73e8;
            text-decoration: none;
            padding: 0.3rem 0.6rem;
            border-radius: var(--radius-sm);
            border: 1.5px solid #d2e3fc;
            background: #e8f0fe;
            transition: all 0.18s;
            white-space: nowrap;
        }

        .maps-link:hover {
            background: #1a73e8;
            color: white;
            border-color: #1a73e8;
        }

        .maps-link .material-symbols-outlined {
            font-size: 14px;
        }

        .coord-mono {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-family: monospace;
            display: block;
        }

        /* Unified Filter Styles */
        .table-filters {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: nowrap;
        }

        .search-wrapper {
            position: relative;
            width: 280px;
        }

        .search-wrapper input {
            width: 100%;
            height: 42px;
            padding: 0 1rem 0 2.75rem;
            border-radius: var(--radius-md);
            border: 1.5px solid var(--border);
            background: var(--surface);
            font-size: 0.82rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--text);
            box-shadow: var(--shadow-sm);
        }

        .search-wrapper input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-glow);
            background: white;
        }

        .search-wrapper .material-symbols-outlined {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 20px;
            pointer-events: none;
            opacity: 0.8;
        }

        .select-filter {
            height: 42px;
            min-width: 160px;
            padding: 0 2.5rem 0 1rem;
            border-radius: var(--radius-md);
            border: 1.5px solid var(--border);
            background: var(--surface);
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            box-shadow: var(--shadow-sm);
        }

        .select-filter:hover {
            border-color: var(--primary-light);
        }

        .select-filter:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-glow);
        }

        /* Numbering Column */
        .no-col {
            width: 50px;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.72rem;
            font-weight: 600;
        }

        /* General Table Cell Font Size Reduction */
        table tbody td {
            font-size: 0.75rem;
            padding: 0.625rem 0.875rem !important;
        }

        table tbody td strong {
            font-size: 0.78rem;
        }

        mark {
            background: #fde68a;
            color: #92400e;
            padding: 0 2px;
            border-radius: 2px;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <div class="page-title-icon">
                    <span class="material-symbols-outlined">map</span>
                </div>
                <div>
                    <h1>Kelola Lokasi</h1>
                    <p>Tambah gudang, toko, dan titik pengiriman</p>
                </div>
            </div>
            <div class="page-actions">
                <?php if ($can_add): ?>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <span class="material-symbols-outlined">add_location</span>
                        Tambah Lokasi Baru
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Location Table -->
        <div class="card">
            <div class="card-header"
                style="display:flex; justify-content:space-between; align-items:center; gap:2rem; flex-wrap:nowrap; padding-bottom:1.5rem; border-bottom:1px solid var(--border); margin-bottom:1.5rem;">
                <span class="card-title" style="margin-bottom:0; flex-shrink:0;">
                    <span class="material-symbols-outlined" style="font-size:24px;">location_on</span>
                    Daftar Lokasi
                </span>

                <div class="table-filters">
                    <div class="search-wrapper">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" id="locationSearch" placeholder="Cari nama, kota, alamat..."
                            oninput="renderLocations()">
                    </div>
                    <select id="typeFilter" class="select-filter" onchange="renderLocations()" style="margin:0;">
                        <option value="">Semua Tipe</option>
                        <option value="store">🏪 Toko (Store)</option>
                        <option value="warehouse">🏭 Gudang (Warehouse)</option>
                        <option value="office">🏢 Head Office</option>
                    </select>
                </div>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th class="no-col">No</th>
                            <th>Nama Lokasi</th>
                            <th>Type</th>
                            <th>Kota</th>
                            <th>Alamat</th>
                            <th>Koordinat</th>
                            <th>Google Maps</th>
                            <?php if ($can_edit || $can_delete): ?>
                                <th style="text-align:right;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="locationTableBody">
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">autorenew</span>
                                    <p>Memuat data...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== ADD MODAL ===== -->
    <div id="addModal" class="modal-overlay" onclick="if(event.target===this)closeAdd()" style="display: none;">
        <div class="modal-box" style="max-width:500px;">
            <button class="modal-close" onclick="closeAdd()">
                <span class="material-symbols-outlined">close</span>
            </button>
            <div class="modal-title">
                <span class="material-symbols-outlined">add_location</span>
                Tambah Lokasi Baru
            </div>
            <p class="modal-subtitle">Isi informasi dan koordinat lokasi baru</p>

            <form id="locationForm">
                <div class="form-group">
                    <label>Nama Lokasi</label>
                    <input type="text" name="name" required placeholder="Contoh: Alfamart Depok 1">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                    <div class="form-group">
                        <label>Tipe Lokasi</label>
                        <select name="type" required>
                            <option value="store">🏪 Toko (Store)</option>
                            <option value="warehouse">🏭 Gudang (Warehouse)</option>
                            <option value="office">🏢 Head Office</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kota</label>
                        <input type="text" name="city" required placeholder="Contoh: Jakarta">
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <input type="text" name="address" placeholder="Jl. Contoh No. 1, Kecamatan...">
                </div>

                <!-- Coordinate Input Mode -->
                <div class="form-group">
                    <label>Koordinat</label>
                    <div class="coord-mode-toggle">
                        <button type="button" class="coord-mode-btn active" id="btnModeManual"
                            onclick="setCoordMode('manual')">
                            <span class="material-symbols-outlined">edit_location</span> Manual
                        </button>
                        <button type="button" class="coord-mode-btn" id="btnModeGmaps" onclick="setCoordMode('gmaps')">
                            <span class="material-symbols-outlined">share_location</span> Google Maps Link
                        </button>
                    </div>

                    <!-- Manual mode -->
                    <div id="modeManual">
                        <div class="coord-row">
                            <div>
                                <input type="text" id="addLat" name="lat" placeholder="Latitude: -6.1234" required>
                            </div>
                            <div>
                                <input type="text" id="addLng" name="lng" placeholder="Longitude: 106.8765" required>
                            </div>
                        </div>
                    </div>

                    <!-- Google Maps Link mode -->
                    <div id="modeGmaps" style="display:none;">
                        <div class="gmaps-input-wrap">
                            <input type="text" id="gmapsUrl" placeholder="https://maps.app.goo.gl/..."
                                style="font-size:0.82rem;">
                            <button type="button" class="btn-resolve" id="btnResolve" onclick="resolveLink('add')">
                                <span class="material-symbols-outlined">my_location</span>
                                Ambil
                            </button>
                        </div>
                        <div class="resolve-result" id="resolveResult"></div>
                        <!-- Hidden fields for resolved coords -->
                        <input type="hidden" id="addLatHidden" name="lat">
                        <input type="hidden" id="addLngHidden" name="lng">
                    </div>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1rem;">
                    <button type="button" onclick="closeAdd()" class="btn btn-ghost"
                        style="flex:1; justify-content:center;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">
                        <span class="material-symbols-outlined">save</span>
                        Simpan Lokasi
                    </button>
                </div>
                <div id="formMsg" style="margin-top:0.875rem; text-align:center;"></div>
            </form>
        </div>
    </div>

    <!-- ===== EDIT MODAL ===== -->
    <div id="editModal" class="modal-overlay" onclick="if(event.target===this)closeEdit()" style="display: none;">
        <div class="modal-box" style="max-width:500px;">
            <button class="modal-close" onclick="closeEdit()">
                <span class="material-symbols-outlined">close</span>
            </button>
            <div class="modal-title">
                <span class="material-symbols-outlined">edit_location</span>
                Edit Lokasi
            </div>
            <p class="modal-subtitle">Perbarui informasi dan koordinat lokasi</p>

            <form id="editForm">
                <input type="hidden" id="editId" name="id">

                <div class="form-group">
                    <label>Nama Lokasi</label>
                    <input type="text" id="editName" name="name" required placeholder="Nama lokasi">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                    <div class="form-group">
                        <label>Tipe</label>
                        <select id="editType" name="type" required>
                            <option value="store">🏪 Toko</option>
                            <option value="warehouse">🏭 Gudang</option>
                            <option value="office">🏢 Head Office</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kota</label>
                        <input type="text" id="editCity" name="city" required placeholder="Kota">
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <input type="text" id="editAddress" name="address" placeholder="Jl. Contoh No. 1...">
                </div>

                <!-- Coordinate Mode for Edit -->
                <div class="form-group">
                    <label>Koordinat</label>
                    <div class="coord-mode-toggle">
                        <button type="button" class="coord-mode-btn active" id="editBtnModeManual"
                            onclick="setEditCoordMode('manual')">
                            <span class="material-symbols-outlined">edit_location</span> Manual
                        </button>
                        <button type="button" class="coord-mode-btn" id="editBtnModeGmaps"
                            onclick="setEditCoordMode('gmaps')">
                            <span class="material-symbols-outlined">share_location</span> Google Maps Link
                        </button>
                    </div>

                    <div id="editModeManual">
                        <div class="coord-row">
                            <div>
                                <input type="text" id="editLat" name="lat" placeholder="Latitude" required>
                            </div>
                            <div>
                                <input type="text" id="editLng" name="lng" placeholder="Longitude" required>
                            </div>
                        </div>
                    </div>

                    <div id="editModeGmaps" style="display:none;">
                        <div class="gmaps-input-wrap">
                            <input type="text" id="editGmapsUrl" placeholder="https://maps.app.goo.gl/..."
                                style="font-size:0.82rem;">
                            <button type="button" class="btn-resolve" id="editBtnResolve" onclick="resolveLink('edit')">
                                <span class="material-symbols-outlined">my_location</span>
                                Ambil
                            </button>
                        </div>
                        <div class="resolve-result" id="editResolveResult"></div>
                        <input type="hidden" id="editLatHidden" name="lat">
                        <input type="hidden" id="editLngHidden" name="lng">
                    </div>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1rem;">
                    <button type="button" onclick="closeEdit()" class="btn btn-ghost"
                        style="flex:1; justify-content:center;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">
                        <span class="material-symbols-outlined">save</span>
                        Simpan Perubahan
                    </button>
                </div>
                <div id="editFormMsg" style="margin-top:0.75rem; text-align:center; font-size:0.85rem;"></div>
            </form>
        </div>
    </div>

    <script>
        const API_URL = 'api.php';
        const CAN_ADD = <?php echo $can_add ? 'true' : 'false'; ?>;
        const CAN_EDIT = <?php echo $can_edit ? 'true' : 'false'; ?>;
        const CAN_DELETE = <?php echo $can_delete ? 'true' : 'false'; ?>;
        let coordMode = 'manual';
        let editCoordMode = 'manual';

        // ===== OPEN / CLOSE ADD MODAL =====
        function openAddModal() {
            // Reset form
            document.getElementById('locationForm').reset();
            document.getElementById('addLat').value = '';
            document.getElementById('addLng').value = '';
            document.getElementById('addLatHidden').value = '';
            document.getElementById('addLngHidden').value = '';
            document.getElementById('resolveResult').style.display = 'none';
            document.getElementById('formMsg').innerHTML = '';
            setCoordMode('manual');
            const m = document.getElementById('addModal');
            m.style.display = 'flex';
            setTimeout(() => m.classList.add('show'), 10);
        }

        function closeAdd() {
            const m = document.getElementById('addModal');
            m.classList.remove('show');
            setTimeout(() => m.style.display = 'none', 300);
        }

        // ===== COORDINATE MODE TOGGLE =====
        function setCoordMode(mode) {
            coordMode = mode;
            document.getElementById('modeManual').style.display = mode === 'manual' ? 'block' : 'none';
            document.getElementById('modeGmaps').style.display = mode === 'gmaps' ? 'block' : 'none';
            document.getElementById('btnModeManual').classList.toggle('active', mode === 'manual');
            document.getElementById('btnModeGmaps').classList.toggle('active', mode === 'gmaps');

            document.getElementById('addLat').required = mode === 'manual';
            document.getElementById('addLng').required = mode === 'manual';
            if (mode === 'manual') {
                document.getElementById('addLatHidden').name = '';
                document.getElementById('addLngHidden').name = '';
                document.getElementById('addLat').name = 'lat';
                document.getElementById('addLng').name = 'lng';
            } else {
                document.getElementById('addLat').name = '';
                document.getElementById('addLng').name = '';
                document.getElementById('addLatHidden').name = 'lat';
                document.getElementById('addLngHidden').name = 'lng';
            }
        }

        function setEditCoordMode(mode) {
            editCoordMode = mode;
            document.getElementById('editModeManual').style.display = mode === 'manual' ? 'block' : 'none';
            document.getElementById('editModeGmaps').style.display = mode === 'gmaps' ? 'block' : 'none';
            document.getElementById('editBtnModeManual').classList.toggle('active', mode === 'manual');
            document.getElementById('editBtnModeGmaps').classList.toggle('active', mode === 'gmaps');

            document.getElementById('editLat').required = mode === 'manual';
            document.getElementById('editLng').required = mode === 'manual';
            if (mode === 'manual') {
                document.getElementById('editLatHidden').name = '';
                document.getElementById('editLngHidden').name = '';
                document.getElementById('editLat').name = 'lat';
                document.getElementById('editLng').name = 'lng';
            } else {
                document.getElementById('editLat').name = '';
                document.getElementById('editLng').name = '';
                document.getElementById('editLatHidden').name = 'lat';
                document.getElementById('editLngHidden').name = 'lng';
            }
        }

        // ===== RESOLVE GOOGLE MAPS LINK =====
        async function resolveLink(context) {
            const isEdit = context === 'edit';
            const urlInput = document.getElementById(isEdit ? 'editGmapsUrl' : 'gmapsUrl');
            const resultDiv = document.getElementById(isEdit ? 'editResolveResult' : 'resolveResult');
            const btn = document.getElementById(isEdit ? 'editBtnResolve' : 'btnResolve');
            const latField = document.getElementById(isEdit ? 'editLatHidden' : 'addLatHidden');
            const lngField = document.getElementById(isEdit ? 'editLngHidden' : 'addLngHidden');
            const url = urlInput.value.trim();

            if (!url) {
                showResolveResult(resultDiv, false, 'Masukkan URL Google Maps terlebih dahulu.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:16px; animation:spin 0.8s linear infinite;">autorenew</span> Proses...';
            resultDiv.style.display = 'none';

            try {
                const fd = new FormData();
                fd.append('url', url);
                const res = await fetch(`${API_URL}?action=resolve_maps_link`, { method: 'POST', body: fd });
                const data = await res.json();

                if (data.success) {
                    latField.value = data.lat;
                    lngField.value = data.lng;
                    document.getElementById(isEdit ? 'editLat' : 'addLat').placeholder = data.lat;
                    document.getElementById(isEdit ? 'editLng' : 'addLng').placeholder = data.lng;
                    showResolveResult(resultDiv, true, `✓ Koordinat ditemukan: ${data.lat}, ${data.lng}`);
                } else {
                    showResolveResult(resultDiv, false, data.error);
                }
            } catch (err) {
                showResolveResult(resultDiv, false, 'Gagal terhubung ke server.');
            }

            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:16px;">my_location</span> Ambil';
        }

        function showResolveResult(div, ok, msg) {
            div.className = 'resolve-result ' + (ok ? 'resolve-ok' : 'resolve-error');
            div.innerHTML = `<span class="material-symbols-outlined">${ok ? 'check_circle' : 'error'}</span> ${msg}`;
            div.style.display = 'flex';
        }

        // ===== LOAD LOCATIONS TABLE (SMART SEARCH) =====
        let allLocations = [];

        async function initLocations() {
            try {
                const res = await fetch(`${API_URL}?action=get_locations`);
                allLocations = await res.json();
                renderLocations();
            } catch (err) {
                console.error('Init locations error:', err);
            }
        }

        function renderLocations() {
            const searchTerm = document.getElementById('locationSearch').value.toLowerCase();
            const typeFilter = document.getElementById('typeFilter').value;
            const tbody = document.getElementById('locationTableBody');

            const filtered = allLocations.filter(l => {
                const matchesSearch = !searchTerm ||
                    l.name.toLowerCase().includes(searchTerm) ||
                    l.city.toLowerCase().includes(searchTerm) ||
                    (l.address && l.address.toLowerCase().includes(searchTerm));

                const matchesType = !typeFilter || l.type === typeFilter;
                return matchesSearch && matchesType;
            });

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8">
                    <div class="empty-state">
                        <span class="material-symbols-outlined">location_off</span>
                        <p>Tidak ada lokasi ditemukan.</p>
                    </div>
                </td></tr>`;
                return;
            }

            tbody.innerHTML = filtered.map((l, index) => {
                const isStore = l.type === 'store';
                const isWarehouse = l.type === 'warehouse';
                const typeHtml = isStore
                    ? `<span class="type-indicator type-store"><span class="material-symbols-outlined" style="font-size:16px;">storefront</span> Toko</span>`
                    : (isWarehouse
                        ? `<span class="type-indicator type-warehouse"><span class="material-symbols-outlined" style="font-size:16px;">warehouse</span> Gudang</span>`
                        : `<span class="type-indicator type-office"><span class="material-symbols-outlined" style="font-size:16px;">corporate_fare</span> Head Office</span>`);

                const highlight = (text) => {
                    if (!searchTerm || !text) return text || '-';
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    return text.replace(regex, '<mark>$1</mark>');
                };

                const addrHtml = l.address
                    ? `<span class="addr-cell" title="${l.address}">${highlight(l.address)}</span>`
                    : `<span style="color:var(--text-muted); font-size:0.78rem; font-style:italic;">-</span>`;

                const lat = parseFloat(l.lat).toFixed(6);
                const lng = parseFloat(l.lng).toFixed(6);
                const mapsUrl = `https://www.google.com/maps?q=${lat},${lng}`;

                return `
                    <tr>
                        <td class="no-col">${index + 1}</td>
                        <td style="white-space:nowrap;"><strong>${highlight(l.name)}</strong></td>
                        <td style="white-space:nowrap;">${typeHtml}</td>
                        <td style="white-space:nowrap;">${highlight(l.city)}</td>
                        <td>${addrHtml}</td>
                        <td>
                            <span class="coord-mono">${lat}</span>
                            <span class="coord-mono">${lng}</span>
                        </td>
                        <td>
                            <a href="${mapsUrl}" target="_blank" rel="noopener noreferrer" class="maps-link" title="Buka di Google Maps">
                                <span class="material-symbols-outlined">map</span>
                                Lihat Map
                            </a>
                        </td>
                        ${(CAN_EDIT || CAN_DELETE) ? `
                        <td style="text-align:right;">
                            <div style="display:inline-flex; gap:0.375rem;">
                                ${CAN_EDIT ? `
                                <button class="btn-icon" onclick="openEdit(${l.id})" title="Edit Lokasi">
                                    <span class="material-symbols-outlined">edit</span>
                                </button>
                                ` : ''}
                                ${CAN_DELETE ? `
                                <button class="btn-icon danger" onclick="deleteLocation(${l.id})" title="Hapus Lokasi">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                                ` : ''}
                            </div>
                        </td>
                        ` : ''}
                    </tr>`;
            }).join('');
        }

        async function loadLocations() {
            await initLocations();
        }

        // ===== ADD LOCATION =====
        document.getElementById('locationForm').onsubmit = async (e) => {
            e.preventDefault();
            const msg = document.getElementById('formMsg');
            msg.innerHTML = '<span style="color:var(--text-muted);">Menyimpan...</span>';

            try {
                const lat = e.target.querySelector('[name="lat"]').value;
                const lng = e.target.querySelector('[name="lng"]').value;
                if (!lat || !lng) {
                    msg.innerHTML = '<span style="color:var(--danger); font-weight:600;">Koordinat belum diisi atau belum di-resolve dari Google Maps.</span>';
                    return;
                }

                const res = await fetch(`${API_URL}?action=add_location`, { method: 'POST', body: new FormData(e.target) });
                const data = await res.json();
                if (data.success) {
                    msg.innerHTML = '<span style="color:var(--success); font-weight:600; display:flex; align-items:center; gap:4px; justify-content:center;"><span class="material-symbols-outlined" style="font-size:16px;">check_circle</span> Lokasi berhasil disimpan!</span>';
                    loadLocations();
                    setTimeout(() => closeAdd(), 1500);
                } else {
                    msg.innerHTML = '<span style="color:var(--danger); font-weight:600;">' + (data.error || 'Gagal menyimpan lokasi.') + '</span>';
                }
            } catch (err) {
                msg.innerHTML = '<span style="color:var(--danger); font-weight:600;">Terjadi kesalahan pada server.</span>';
                console.error(err);
            }
        };

        // ===== EDIT MODAL =====
        async function openEdit(id) {
            setEditCoordMode('manual');
            document.getElementById('editResolveResult').style.display = 'none';
            document.getElementById('editGmapsUrl').value = '';
            document.getElementById('editFormMsg').innerHTML = '';

            const res = await fetch(`${API_URL}?action=get_location&id=${id}`);
            const loc = await res.json();
            if (loc.error) { alert('Gagal memuat data lokasi.'); return; }

            document.getElementById('editId').value = loc.id;
            document.getElementById('editName').value = loc.name;
            document.getElementById('editType').value = loc.type;
            document.getElementById('editCity').value = loc.city;
            document.getElementById('editAddress').value = loc.address || '';
            document.getElementById('editLat').value = loc.lat;
            document.getElementById('editLng').value = loc.lng;

            const m = document.getElementById('editModal');
            m.style.display = 'flex';
            setTimeout(() => m.classList.add('show'), 10);
        }

        function closeEdit() {
            const m = document.getElementById('editModal');
            m.classList.remove('show');
            setTimeout(() => m.style.display = 'none', 300);
        }

        document.getElementById('editForm').onsubmit = async (e) => {
            e.preventDefault();
            const msg = document.getElementById('editFormMsg');
            msg.innerHTML = '<span style="color:var(--text-muted);">Menyimpan...</span>';

            try {
                const lat = e.target.querySelector('[name="lat"]').value;
                const lng = e.target.querySelector('[name="lng"]').value;
                if (!lat || !lng) {
                    msg.innerHTML = '<span style="color:var(--danger); font-weight:600;">Koordinat belum diisi atau belum di-resolve.</span>';
                    return;
                }

                const res = await fetch(`${API_URL}?action=edit_location`, { method: 'POST', body: new FormData(e.target) });
                const data = await res.json();
                if (data.success) {
                    msg.innerHTML = '<span style="color:var(--success); font-weight:600;">✓ Perubahan disimpan!</span>';
                    loadLocations();
                    setTimeout(() => closeEdit(), 1200);
                } else {
                    msg.innerHTML = '<span style="color:var(--danger); font-weight:600;">' + (data.error || 'Gagal menyimpan perubahan.') + '</span>';
                }
            } catch (err) {
                msg.innerHTML = '<span style="color:var(--danger); font-weight:600;">Terjadi kesalahan pada server.</span>';
                console.error(err);
            }
        };

        // ===== DELETE =====
        async function deleteLocation(id) {
            if (!confirm('Hapus lokasi ini? Tindakan ini tidak bisa dibatalkan.')) return;
            const res = await fetch(`${API_URL}?action=delete_location`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            });
            const data = await res.json();
            if (data.success) loadLocations();
        }

        // Spin animation
        const style = document.createElement('style');
        style.textContent = '@keyframes spin { 100% { transform: rotate(360deg); } }';
        document.head.appendChild(style);

        initLocations();
    </script>
</body>

</html>