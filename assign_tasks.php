<?php
require_once 'auth_check.php';
checkLogin();
checkAccess('assign_tasks');
$can_write = canWriteMenu('assign_tasks');
$user_role = $_SESSION['role'] ?? '';

// Perubahan Akses: Role 'admin' hanya diperbolehkan melihat detail (Tombol Mata), melakukan Assign, dan inline quick-edit.
// Tombol aksi lainnya (Edit, Hapus) di kolom Aksi hanya ditampilkan untuk Management dan Super Admin (Controller).
$can_assign = $can_write;
$can_edit = $can_write;
if ($user_role === 'admin') {
    $can_write = false;
    $can_assign = true;
    $can_edit = true;
}

date_default_timezone_set('Asia/Jakarta');

// Get initial pending counts for tabs
$init_wh = $pdo->query("SELECT COUNT(*) FROM pickup_requests WHERE status = 'pending'")->fetchColumn();
$init_exp = $pdo->query("SELECT COUNT(*) FROM expedisi_tasks WHERE status = 'pending' AND (target_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') OR (target_date IS NULL AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')))")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penugasan Driver | TMS</title>
    <link rel="icon" type="image/png" href="favicon.png?v=3">
    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .success-msg {
            color: #16a34a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .error-msg {
            color: var(--danger);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
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

        /* Google Maps link badge */

        .maps-link {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.74rem;
            font-weight: 600;
            color: #1a73e8;
            text-decoration: none;
            padding: 0.25rem 0.55rem;
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
            font-size: 13px;
        }

        .dest-cell {
            font-size: 0.85rem;
        }

        .dest-cell .maps-link {
            margin-top: 0.25rem;
        }

        /* Export button (Icon Only) */
        .btn-excel {
            background: #16a34a;
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-excel:hover {
            background: #15803d;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(22, 163, 74, 0.4);
        }

        .row-count-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 9999px;
            padding: 0.2rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
        }

        /* Driver Checkbox Style */
        .driver-checkbox-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            cursor: pointer;
            transition: 0.2s;
        }

        .driver-checkbox-item:hover {
            background: #f8fafc;
            border-color: var(--primary);
        }

        .driver-checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .driver-checkbox-item .driver-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .driver-checkbox-item .driver-info .name {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text);
        }

        .driver-checkbox-item .driver-info .status {
            font-size: 0.7rem;
            font-weight: 600;
        }

        .driver-checkbox-item .driver-info .status.free {
            color: var(--success);
        }

        .driver-checkbox-item .driver-info .status.busy {
            color: var(--warning);
        }

        /* Toast Notification moved to global style.css */

        /* Map Container in Modal */
        #detailMap {
            height: 160px;
            width: 100%;
            border-radius: 12px;
            margin: 1rem 0;
            border: 1px solid var(--border);
            z-index: 1;
        }

        .tab-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f59e0b;
            /* Amber */
            color: white;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 99px;
            margin-left: 6px;
            min-width: 18px;
            height: 18px;
            line-height: 1;
            vertical-align: middle;
        }

        .tab-badge.badge-red {
            background: #ef4444;
            /* Red */
            animation: tabPulse 2s infinite;
        }

        @keyframes tabPulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
            }

            70% {
                transform: scale(1.1);
                box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        .time-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .time-label {
            font-size: 0.65rem;
            color: var(--text-sub);
            font-weight: 700;
            text-transform: uppercase;
        }

        .time-value {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text);
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.25rem;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .detail-table tr {
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-table tr:last-child {
            border-bottom: none;
        }

        .detail-table td {
            padding: 10px 14px;
            font-size: 0.85rem;
            vertical-align: top;
        }

        .detail-table td:first-child {
            width: 130px;
            background: #f8fafc;
            font-weight: 700;
            color: var(--text-sub);
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.5px;
            border-right: 1px solid #f1f5f9;
        }

        .detail-table td:last-child {
            color: var(--text);
            font-weight: 600;
        }

        .detail-badge {
            display: inline-flex;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        /* Legacy animations removed */

        /* Image Popup Lightbox */
        .lightbox-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            cursor: pointer;
        }

        /* Table Content Density Adjustment (10px) */
        #historyTable tbody td,
        #historyTable tbody td div,
        #historyTable tbody td span:not(.material-symbols-outlined),
        #historyTable tbody td strong {
            font-size: 10px !important;
        }

        #historyTable .badge,
        #historyTable .detail-badge {
            font-size: 9px !important;
            padding: 1px 6px !important;
        }

        #historyTable .material-symbols-outlined {
            font-size: 14px !important;
        }

        #historyTable .maps-link {
            font-size: 9px !important;
            padding: 1px 4px !important;
        }

        #imagePopup {
            animation: none;
        }

        #imagePopup.show {
            animation: popupFadeIn 0.2s ease-out forwards;
        }

        #imagePopup #popupImg {
            animation: popupImgScale 0.25s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        @keyframes popupFadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes popupImgScale {
            from {
                transform: scale(0.88);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Clickable photo thumbnails */
        .photo-thumb {
            cursor: zoom-in;
            transition: transform 0.18s, box-shadow 0.18s;
        }

        .photo-thumb:hover {
            transform: scale(1.04);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
        }

        /* Tabs Styling */
        .tab-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            background: #e2e8f0;
            padding: 0.4rem;
            border-radius: 12px;
            width: fit-content;
        }

        .tab-link {
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.875rem;
            color: var(--text-muted);
            transition: all 0.2s;
        }

        .tab-link.active {
            background: var(--surface);
            color: var(--primary);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Integrated Searchable Select */
        .searchable-group {
            position: relative;
            margin-bottom: 0.75rem;
        }

        .searchable-input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.2s;
            background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' height='20' viewBox='0 -960 960 960' width='20'%3E%3Cpath d='M480-345 240-585l56-56 184 184 184-184 56 56-240 240Z'/%3E%3C/svg%3E") no-repeat right 12px center;
        }

        .searchable-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .options-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 250px;
            overflow-y: auto;
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-top: 5px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            z-index: 1001;
            display: none;
        }

        .options-list.show {
            display: block;
        }

        .option-item {
            padding: 10px 14px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .option-item:hover {
            background: #f1f5f9;
        }

        .option-item.selected {
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
        }

        .required-star {
            color: #ef4444;
            margin-left: 2px;
        }

        /* Professional Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
            margin-top: 0.5rem;
        }

        .form-grid .form-group {
            margin-bottom: 0;
        }

        .form-grid .full-width {
            grid-column: span 2;
        }

        .modal-box.wide {
            max-width: 800px !important;
        }

        /* Image Preview Styling */
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .preview-item {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Inline Edit Styling */
        .editable-cell {
            position: relative;
            transition: background 0.2s;
            border-radius: 6px;
        }

        .editable-cell:hover {
            background: rgba(99, 102, 241, 0.05);
        }

        .inline-edit-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: all 0.2s;
            color: var(--primary);
            background: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border);
            z-index: 5;
        }

        .editable-cell:hover .inline-edit-btn {
            opacity: 1;
            right: 12px;
        }

        .editable-cell.disabled {
            cursor: default !important;
        }

        .editable-cell.disabled:hover {
            background: transparent !important;
        }

        .preview-item {
            position: relative;
        }

        .preview-remove {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            z-index: 2;
            transition: transform 0.1s;
        }

        .preview-remove:hover {
            transform: scale(1.1);
            background: #ef4444;
        }

        /* Custom Upload Box */
        .upload-box {
            border: 1.5px dashed var(--border);
            border-radius: 10px;
            padding: 0.6rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: var(--text-muted);
        }

        .upload-box:hover {
            border-color: var(--primary);
            background: var(--primary-light);
            color: var(--primary);
        }

        .upload-box .material-symbols-outlined {
            font-size: 20px;
        }

        .upload-box span {
            font-size: 0.75rem;
            font-weight: 700;
        }

        .pagination-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: var(--surface);
            border-radius: 1rem;
            border: 1px solid var(--border);
        }

        .pagination-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .pagination-btn {
            height: 34px;
            padding: 0 0.75rem;
            border: 1px solid var(--border);
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
            color: var(--text-muted);
            transition: all 0.2s;
        }

        .pagination-btn:hover:not(:disabled) {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        .pagination-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .rows-per-page {
            height: 34px;
            padding: 0 0.5rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-muted);
            outline: none;
        }

        /* Premium Table Sorting Styles */
        .th-content {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            justify-content: flex-start;
        }

        th.sortable {
            cursor: pointer;
            user-select: none;
            transition: color 0.2s;
        }
        th.sortable:hover {
            color: var(--primary) !important;
        }
        th.sortable:hover .sort-indicator {
            color: var(--primary) !important;
            opacity: 1;
        }
        .sort-indicator {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px !important;
            color: #94a3b8;
            opacity: 0.6;
            transition: all 0.2s ease;
        }
        th.active-sort {
            color: var(--primary) !important;
            border-bottom: 2px solid var(--primary) !important;
        }
        th.active-sort .sort-indicator {
            color: var(--primary) !important;
            opacity: 1;
            transform: scale(1.1);
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
                    <span class="material-symbols-outlined">assignment</span>
                </div>
                <div>
                    <h1>Penugasan Driver</h1>
                    <p>Buat tugas baru dan lihat riwayat pengiriman</p>
                </div>
            </div>
        </div>

        <!-- TABS -->
        <div class="tab-container">
            <a href="assign_tasks" class="tab-link active">
                WH Operation
                <span id="tabBadgeWH" class="tab-badge badge-red" <?php echo $init_wh > 0 ? '' : 'style="display:none;"'; ?>><?php echo $init_wh; ?></span>
            </a>
            <a href="expedisi_tasks" class="tab-link">
                Expedisi
                <span id="tabBadgeExp" class="tab-badge" <?php echo $init_exp > 0 ? '' : 'style="display:none;"'; ?>><?php echo $init_exp; ?></span>
            </a>
        </div>

        <!-- Filter Card -->
        <div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
            <div class="filter-bar"
                style="margin-bottom:0; display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap;">
                <div class="filter-group" style="flex: 1; min-width: 200px;">
                    <label
                        style="font-size: 0.75rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; margin-bottom: 0.5rem; display: block;">Cari
                        Data</label>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <div style="position: relative; flex: 1;">
                            <span class="material-symbols-outlined"
                                style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 18px; color: var(--text-muted);">search</span>
                            <input type="text" id="searchInput" placeholder="Search..." onkeyup="loadHistory()"
                                style="width: 100%; padding: 0.6rem 0.6rem 0.6rem 35px; border-radius: 8px; border: 1px solid var(--border); outline: none;">
                        </div>
                        <button class="btn-excel" onclick="exportToExcel()" id="exportBtn" title="Export Excel"
                            style="height: 38px; width: 38px; margin: 0; flex-shrink: 0; box-sizing: border-box; display: flex; align-items: center; justify-content: center;">
                            <span class="material-symbols-outlined">table_view</span>
                        </button>
                    </div>
                </div>
                <div class="filter-group" style="width: 150px;">
                    <label
                        style="font-size: 0.75rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; margin-bottom: 0.5rem; display: block;">Dari</label>
                    <input type="date" id="date_from" onchange="loadHistory()" value="<?php echo date('Y-m-d'); ?>"
                        style="width: 100%; padding: 0.6rem; border-radius: 8px; border: 1px solid var(--border); outline: none;">
                </div>
                <div class="filter-group" style="width: 150px;">
                    <label
                        style="font-size: 0.75rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; margin-bottom: 0.5rem; display: block;">Sampai</label>
                    <input type="date" id="date_to" onchange="loadHistory()" value="<?php echo date('Y-m-d'); ?>"
                        style="width: 100%; padding: 0.6rem; border-radius: 8px; border: 1px solid var(--border); outline: none;">
                </div>
                <div class="filter-group" style="width: 160px;">
                    <label
                        style="font-size: 0.75rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; margin-bottom: 0.5rem; display: block;">Driver</label>
                    <select id="driverFilter" onchange="loadHistory()"
                        style="width: 100%; padding: 0.6rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white;">
                        <option value="">Semua Driver</option>
                    </select>
                </div>
                <div class="filter-group" style="width: 140px;">
                    <label
                        style="font-size: 0.75rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; margin-bottom: 0.5rem; display: block;">Status</label>
                    <select id="statusFilter" onchange="loadHistory()"
                        style="width: 100%; padding: 0.6rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white;">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="in_transit">In Transit</option>
                        <option value="completed">Completed</option>
                        <option value="canceled">Cancel</option>
                    </select>
                </div>
                <div style="display: flex; gap: 0.5rem; margin-left: auto;">
                    <?php if ($can_assign): ?>
                        <button class="btn btn-ghost" onclick="openBulkAssignModal()" id="bulkAssignBtn"
                            style="display:none; height: 40px; border: 1px solid var(--border);">
                            <span class="material-symbols-outlined">group_add</span>
                            Bulk (<span id="bulkCount">0</span>)
                        </button>
                    <?php endif; ?>
                    <?php if ($can_write || $user_role === 'admin'): ?>
                        <button class="btn btn-primary" onclick="openAddModal()"
                            style="height: 40px; white-space: nowrap; padding: 0 1rem; border-radius: 8px; display: flex; align-items: center; gap: 8px;">
                            <span class="material-symbols-outlined" style="font-size: 20px;">add_task</span>
                            Tugaskan Driver
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title">
                    <span class="material-symbols-outlined">history</span>
                    Riwayat Penugasan
                    <span class="row-count-badge" id="rowCountBadge" style="display:none;">
                        <span class="material-symbols-outlined" style="font-size:14px;">table_rows</span>
                        <span id="rowCountNum">0</span> data
                    </span>
                </span>
            </div>

            <div class="table-wrapper">
                <table id="historyTable">
                    <thead>
                        <tr>
                            <th style="width:40px;"><input type="checkbox" id="selectAllReqs"
                                    onclick="toggleSelectAll(this)"></th>
                            <th class="sortable" onclick="handleSort('assign_by')">
                                <div class="th-content">
                                    Assign By
                                    <span class="material-symbols-outlined sort-indicator" id="sort-assign_by">swap_vert</span>
                                </div>
                            </th>
                            <th class="sortable" onclick="handleSort('date')">
                                <div class="th-content">
                                    Tanggal / Driver
                                    <span class="material-symbols-outlined sort-indicator" id="sort-date">swap_vert</span>
                                </div>
                            </th>
                            <th class="sortable" onclick="handleSort('route')">
                                <div class="th-content">
                                    Rute (Asal &raquo; Tujuan)
                                    <span class="material-symbols-outlined sort-indicator" id="sort-route">swap_vert</span>
                                </div>
                            </th>
                            <th class="sortable" onclick="handleSort('sj')">
                                <div class="th-content">
                                    SJ / Koli
                                    <span class="material-symbols-outlined sort-indicator" id="sort-sj">swap_vert</span>
                                </div>
                            </th>
                            <th class="sortable" onclick="handleSort('receiver')">
                                <div class="th-content">
                                    Penerima
                                    <span class="material-symbols-outlined sort-indicator" id="sort-receiver">swap_vert</span>
                                </div>
                            </th>
                            <th class="sortable" onclick="handleSort('type')">
                                <div class="th-content">
                                    Type
                                    <span class="material-symbols-outlined sort-indicator" id="sort-type">swap_vert</span>
                                </div>
                            </th>
                            <th class="sortable" onclick="handleSort('status')">
                                <div class="th-content">
                                    Status
                                    <span class="material-symbols-outlined sort-indicator" id="sort-status">swap_vert</span>
                                </div>
                            </th>
                            <th class="sortable" onclick="handleSort('notes')">
                                <div class="th-content">
                                    Keterangan
                                    <span class="material-symbols-outlined sort-indicator" id="sort-notes">swap_vert</span>
                                </div>
                            </th>
                            <th style="text-align:right;">
                                <div class="th-content" style="justify-content:flex-end; width: 100%;">
                                    Aksi
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="13">
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

        <!-- Pagination Bar -->
        <div class="pagination-bar">
            <div class="pagination-controls">
                <label style="font-size:0.75rem; font-weight:700; color:var(--text-sub);">Rows:</label>
                <select class="rows-per-page" id="rowsPerPage" onchange="changeRowsPerPage()">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <button class="pagination-btn" id="btnPrev" onclick="changePage(-1)">
                    <span class="material-symbols-outlined" style="font-size:18px">chevron_left</span> Prev
                </button>
                <button class="pagination-btn" id="btnNext" onclick="changePage(1)">
                    Next <span class="material-symbols-outlined" style="font-size:18px">chevron_right</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ===== ADD TASK MODAL ===== -->
    <div id="addModal" class="modal-overlay" onclick="if(event.target===this)closeAdd()" style="display: none;">
        <div class="modal-box wide">
            <button class="modal-close" onclick="closeAdd()">
                <span class="material-symbols-outlined">close</span>
            </button>
            <div class="modal-title">
                <span class="material-symbols-outlined">add_task</span>
                Buat Tugas Baru
            </div>
            <p class="modal-subtitle">Tetapkan pengiriman untuk driver</p>

            <form id="taskForm">
                <div class="form-grid">
                    <div class="form-group searchable-group">
                        <label>Pilih Driver <span class="required-star">*</span></label>
                        <input type="text" class="searchable-input" placeholder="Cari driver..." readonly
                            onclick="toggleOptions('driverList')">
                        <div id="driverList" class="options-list"></div>
                        <input type="hidden" name="driver_id" id="driverSelect" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal & Jam <span class="required-star">*</span></label>
                        <input type="datetime-local" name="target_date" id="taskFormTargetDate" required>
                    </div>

                    <div class="form-group">
                        <label>Tipe Tugas <span class="required-star">*</span></label>
                        <select name="task_type" id="taskType" required>
                            <option value="antar">Delivery</option>
                            <option value="kirim">Pickup</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>No Surat Jalan <span class="required-star">*</span></label>
                        <input type="text" name="surat_jalan" id="taskFormSuratJalan"
                            placeholder="Contoh: SJ-001/ABC/2024" required oninput="this.value=this.value.toUpperCase()"
                            style="text-transform:uppercase;">
                    </div>

                    <div class="form-group searchable-group">
                        <label>Lokasi Asal <span class="required-star">*</span></label>
                        <input type="text" class="searchable-input" placeholder="Cari atau pilih lokasi asal..."
                            readonly onclick="toggleOptions('originList')">
                        <div id="originList" class="options-list"></div>
                        <input type="hidden" name="origin_id" id="originSelect" required>
                        <input type="hidden" name="origin_name" id="origin_name">
                    </div>
                    <div class="form-group searchable-group">
                        <label>Lokasi Tujuan<span class="required-star">*</span></label>
                        <input type="text" class="searchable-input" placeholder="Cari atau pilih tujuan..." readonly
                            onclick="toggleOptions('destList')">
                        <div id="destList" class="options-list"></div>
                        <input type="hidden" name="dest_id" id="destSelect" required>
                        <input type="hidden" name="dest_name" id="dest_name">
                        <input type="hidden" name="dest_lat" id="dest_lat">
                        <input type="hidden" name="dest_lng" id="dest_lng">
                    </div>

                    <div class="form-group">
                        <label>Total Koli <span class="required-star">*</span></label>
                        <input type="number" name="total_koli" id="taskFormKoli" value="1" min="1" required>
                    </div>
                    <div class="form-group">
                        <!-- Spacer for grid alignment -->
                    </div>

                    <div class="form-group">
                        <label>Foto Surat Jalan <span class="required-star">*</span></label>
                        <input type="file" id="taskFormFile" name="surat_jalan_file[]" accept="image/*" multiple
                            style="display:none;" onchange="handleImagePreview(this, 'addPreview')">
                        <div class="upload-box" onclick="document.getElementById('taskFormFile').click()">
                            <span class="material-symbols-outlined">add_a_photo</span>
                            <span>Tambah Foto SJ</span>
                        </div>
                        <div id="addPreview" class="preview-container"></div>
                    </div>
                    <div class="form-group">
                        <label>Foto Koli / Barang <span class="required-star">*</span></label>
                        <input type="file" id="taskFormGoodsFile" name="goods_file[]" accept="image/*" multiple
                            style="display:none;" onchange="handleImagePreview(this, 'addGoodsPreview')">
                        <div class="upload-box" onclick="document.getElementById('taskFormGoodsFile').click()">
                            <span class="material-symbols-outlined">inventory_2</span>
                            <span>Tambah Foto Barang</span>
                        </div>
                        <div id="addGoodsPreview" class="preview-container"></div>
                    </div>

                    <div class="form-group full-width">
                        <label>Catatan</label>
                        <textarea name="notes" id="taskFormNotes" placeholder="Tambahkan catatan jika ada..." rows="2"
                            style="width:100%; padding:0.6rem; border:1.5px solid var(--border); border-radius:var(--radius-sm); font-family:inherit;"></textarea>
                    </div>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:2rem;">
                    <button type="button" onclick="closeAdd()" class="btn btn-ghost"
                        style="flex:1; justify-content:center;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">
                        <span class="material-symbols-outlined">send</span>
                        Berikan Tugas
                    </button>
                </div>
                <div id="formMsg" style="margin-top:0.875rem; text-align:center;"></div>
            </form>
        </div>
    </div>



    <!-- ===== EDIT TASK MODAL ===== -->
    <div id="editModal" class="modal-overlay" onclick="if(event.target===this)closeEdit()" style="display: none;">
        <div class="modal-box wide">
            <button class="modal-close" onclick="closeEdit()">
                <span class="material-symbols-outlined">close</span>
            </button>
            <div class="modal-title">
                <span class="material-symbols-outlined">edit_note</span>
                Edit Penugasan
            </div>
            <p class="modal-subtitle">Ubah informasi tugas pengiriman</p>

            <form id="editForm">
                <input type="hidden" id="editId" name="id">

                <div class="form-grid">
                    <div class="form-group searchable-group" data-edit-field="driver">
                        <label>Pilih Driver <span class="required-star">*</span></label>
                        <input type="text" class="searchable-input" placeholder="Cari driver..." readonly
                            onclick="toggleOptions('editDriverList')">
                        <div id="editDriverList" class="options-list"></div>
                        <input type="hidden" name="driver_id" id="editDriverSelect" required>
                    </div>
                    <div class="form-group" data-edit-field="date">
                        <label>Tanggal & Jam <span class="required-star">*</span></label>
                        <input type="datetime-local" name="target_date" id="editTargetDate" required>
                    </div>

                    <div class="form-group" data-edit-field="type">
                        <label>Tipe Tugas <span class="required-star">*</span></label>
                        <select name="task_type" id="editTaskType" required>
                            <option value="antar">Delivery</option>
                            <option value="kirim">Pickup</option>
                        </select>
                    </div>
                    <div class="form-group" data-edit-field="status">
                        <label>Status <span class="required-star">*</span></label>
                        <select name="status" id="editStatus" required>
                            <option value="pending">Pending</option>
                            <option value="in_transit">In Transit</option>
                            <option value="completed">Completed</option>
                            <option value="canceled">Cancel</option>
                        </select>
                    </div>

                    <div class="form-group searchable-group" data-edit-field="route">
                        <label>Lokasi Penjemputan (Asal) <span class="required-star">*</span></label>
                        <input type="text" class="searchable-input" placeholder="Cari atau pilih lokasi asal..."
                            readonly onclick="toggleOptions('editOriginList')">
                        <div id="editOriginList" class="options-list"></div>
                        <input type="hidden" name="origin_id" id="editOriginSelect" required>
                        <input type="hidden" name="origin_name" id="edit_origin_name">
                    </div>

                    <div class="form-group searchable-group" data-edit-field="route">
                        <label>Tujuan (Toko / Store) <span class="required-star">*</span></label>
                        <input type="text" class="searchable-input" placeholder="Cari atau pilih tujuan..." readonly
                            onclick="toggleOptions('editDestList')">
                        <div id="editDestList" class="options-list"></div>
                        <input type="hidden" name="dest_id" id="editDestSelect" required>
                        <input type="hidden" name="dest_name" id="edit_dest_name">
                        <input type="hidden" name="dest_lat" id="edit_dest_lat">
                        <input type="hidden" name="dest_lng" id="edit_dest_lng">
                    </div>

                    <div class="form-group" data-edit-field="sj">
                        <label>No Surat Jalan <span class="required-star">*</span></label>
                        <input type="text" name="surat_jalan" id="editSuratJalan" placeholder="Contoh: SJ-001/ABC/2024"
                            required oninput="this.value=this.value.toUpperCase()" style="text-transform:uppercase;">
                    </div>
                    <div class="form-group" data-edit-field="sj">
                        <label>Total Koli <span class="required-star">*</span></label>
                        <input type="number" name="total_koli" id="editKoli" min="1" required>
                    </div>

                    <div class="form-group full-width" data-edit-field="notes">
                        <label>Catatan</label>
                        <textarea name="notes" id="editNotes" placeholder="Tambahkan catatan jika ada..." rows="2"
                            style="width:100%; padding:0.6rem; border:1.5px solid var(--border); border-radius:var(--radius-sm); font-family:inherit;"></textarea>
                    </div>

                    <div class="form-group" data-edit-field="receiver">
                        <label>Nama Penerima</label>
                        <input type="text" name="receiver_name" id="editReceiverName" placeholder="Nama penerima barang"
                            oninput="this.value=this.value.toUpperCase()" style="text-transform:uppercase;">
                    </div>

                    <div class="form-group" data-edit-field="files">
                        <label>Foto Surat Jalan</label>
                        <input type="file" id="editSjInput" name="surat_jalan_file[]" accept="image/*" multiple style="display:none;" onchange="handleEditFileChange(this, 'editSjPrev')">
                        <div class="upload-box" onclick="document.getElementById('editSjInput').click()">
                            <span class="material-symbols-outlined">add_a_photo</span>
                            <span>Tambah Foto SJ</span>
                        </div>
                        <div id="editSjPrev" class="preview-container"></div>
                        <input type="hidden" name="existing_surat_jalan_file" id="editSjExisting" value="">
                    </div>

                    <div class="form-group" data-edit-field="files">
                        <label>Foto Barang / Koli</label>
                        <input type="file" id="editGoodsInput" name="goods_file[]" accept="image/*" multiple style="display:none;" onchange="handleEditFileChange(this, 'editGoodsPrev')">
                        <div class="upload-box" onclick="document.getElementById('editGoodsInput').click()">
                            <span class="material-symbols-outlined">inventory_2</span>
                            <span>Tambah Foto Barang</span>
                        </div>
                        <div id="editGoodsPrev" class="preview-container"></div>
                        <input type="hidden" name="existing_goods_file" id="editGoodsExisting" value="">
                    </div>

                    <div class="form-group full-width" data-edit-field="files">
                        <label>Foto Bukti Selesai (Driver)</label>
                        <input type="file" id="editProofInput" name="proof_file[]" accept="image/*" multiple style="display:none;" onchange="handleEditFileChange(this, 'editProofPrev')">
                        <div class="upload-box" onclick="document.getElementById('editProofInput').click()">
                            <span class="material-symbols-outlined">receipt_long</span>
                            <span>Tambah Foto Bukti</span>
                        </div>
                        <div id="editProofPrev" class="preview-container"></div>
                        <input type="hidden" name="existing_proof_file" id="editProofExisting" value="">
                    </div>
                </div>
                <div style="margin-top:1rem; text-align:right;" id="showAllEditWrap">
                    <button type="button" class="btn-link" onclick="showAllEditFields()"
                        style="font-size:0.8rem; color:var(--primary); background:none; border:none; cursor:pointer; font-weight:600;">Lihat
                        Semua Kolom</button>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:2rem;">
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

    <!-- SheetJS for Excel export (CDN) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <!-- ===== ASSIGN DRIVER MODAL ===== -->
    <div id="assignModal" class="modal-overlay" onclick="if(event.target===this)closeAssignModal()"
        style="display: none;">
        <div class="modal-box" style="max-width:440px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <h2 id="assignModalTitle" style="font-size:1.25rem; font-weight:800; margin:0;">Assign Driver</h2>
                <button class="modal-close" onclick="closeAssignModal()" style="position:static;">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div style="display:grid; grid-template-columns:1fr; gap:0.75rem; margin-bottom:1.25rem;">
                <div class="form-group">
                    <label
                        style="font-size:0.8rem; font-weight:700; color:#475569; display:block; margin-bottom:0.4rem;">Tanggal
                        & Jam Jadwal</label>
                    <input type="datetime-local" id="assignTargetDate"
                        style="width:100%; padding:0.75rem; border:1.5px solid var(--border); border-radius:0.75rem; font-family:inherit; font-size:0.9rem; box-sizing:border-box;">
                </div>
            </div>

            <p style="color:var(--text-sub); font-size:0.9rem; margin-bottom:1.25rem;">Pilih satu atau beberapa driver
                untuk tugas ini:</p>

            <div id="driverCheckboxList"
                style="max-height:300px; overflow-y:auto; display:flex; flex-direction:column; gap:0.5rem; margin-bottom:1.5rem; padding-right:5px;">
                <!-- Drivers with checkboxes will be injected here -->
            </div>

            <div style="display:flex; gap:0.75rem;">
                <button type="button" onclick="closeAssignModal()" class="btn btn-ghost"
                    style="flex:1; justify-content:center;">Batal</button>
                <button id="submitAssignBtn" onclick="submitMultipleAssign()" class="btn btn-primary"
                    style="flex:1; justify-content:center;">
                    <span class="material-symbols-outlined">send</span>
                    Konfirmasi Penugasan
                </button>
            </div>
        </div>
    </div><!-- END assignModal -->

    <!-- ===== QUICK EDIT MODAL (FOR ADMIN/MANAGEMENT) ===== -->
    <div id="quickEditModal" class="modal-overlay" onclick="if(event.target===this)closeQuickEdit()"
        style="display: none;">
        <div class="modal-box" style="max-width:420px; padding: 1.5rem; border-radius: 1.25rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <div id="qeIcon" class="material-symbols-outlined" style="color:var(--primary); font-size:1.5rem;">
                        edit</div>
                    <h3 id="quickEditTitle" style="font-size:1.1rem; font-weight:800; margin:0; color:var(--text);">
                        Quick Edit</h3>
                </div>
                <button class="modal-close" onclick="closeQuickEdit()"
                    style="position:static; background:var(--bg); border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; color:var(--text-sub);">
                    <span class="material-symbols-outlined" style="font-size:18px;">close</span>
                </button>
            </div>

            <form id="quickEditForm">
                <input type="hidden" id="qe_id" name="id">
                <input type="hidden" id="qe_field" name="field">

                <div id="qe_content" style="display:flex; flex-direction:column; gap:1rem;">
                    <!-- Dynamic content will be injected here -->
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:2rem;">
                    <button type="button" onclick="closeQuickEdit()" class="btn btn-ghost"
                        style="flex:1; justify-content:center; border-radius:0.75rem;">Batal</button>
                    <button type="submit" id="qe_submit_btn" class="btn btn-primary"
                        style="flex:1; justify-content:center; border-radius:0.75rem;">
                        <span class="material-symbols-outlined">save</span> Simpan
                    </button>
                </div>
                <div id="qeMsg" style="margin-top:0.75rem; text-align:center; font-size:0.85rem;"></div>
            </form>
        </div>
    </div>



    <script>
        // ===== UTILITY FUNCTIONS =====
        function formatDateTime(str) {
            if (!str) return '-';
            const dt = new Date(str);
            if (isNaN(dt.getTime())) return str;
            const date = dt.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
            const time = dt.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });
            return `${date}, ${time} WIB`;
        }

        // ===== IMAGE POPUP LIGHTBOX =====
        function showImagePopup(url) {
            const popup = document.getElementById('imagePopup');
            const img = document.getElementById('popupImg');
            const dlBtn = document.getElementById('downloadImageBtn');
            if (!popup || !img) return;

            img.src = url;
            if (dlBtn) dlBtn.href = url;

            popup.style.display = 'flex';
            setTimeout(() => popup.classList.add('show'), 10);
        }

        function closeImagePopup() {
            const popup = document.getElementById('imagePopup');
            if (!popup) return;
            popup.classList.remove('show');
            setTimeout(() => {
                popup.style.display = 'none';
                document.getElementById('popupImg').src = '';
            }, 200);
        }


        // Close with ESC key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const popup = document.getElementById('imagePopup');
                if (popup && popup.style.display === 'flex') {
                    closeImagePopup();
                }
            }
        });

        const USER_ROLE = '<?php echo $_SESSION["role"] ?? ""; ?>';
        const CAN_WRITE = <?php echo $can_write ? 'true' : 'false'; ?>;
        const CAN_ASSIGN = <?php echo $can_assign ? 'true' : 'false'; ?>;
        const CAN_EDIT = <?php echo $can_edit ? 'true' : 'false'; ?>;
        const API_URL = 'api.php';
        let locationData = [];
        let allDrivers = [];
        let historyData = []; // Store for export
        let currentPage = 1;
        let rowsPerPage = 25;
        let sortColumn = 'date';
        let sortDirection = 'desc';

        // ===== BULK ASSIGN LOGIC =====
        let isBulkMode = false;
        let selectedReqIds = [];

        function toggleSelectAll(masterCb) {
            const cbs = document.querySelectorAll('.req-checkbox');
            cbs.forEach(cb => cb.checked = masterCb.checked);
            updateBulkBtn();
        }

        function updateBulkBtn() {
            const selected = Array.from(document.querySelectorAll('.req-checkbox:checked')).map(cb => parseInt(cb.value));
            selectedReqIds = selected;
            const btn = document.getElementById('bulkAssignBtn');
            const countSpan = document.getElementById('bulkCount');

            if (selected.length > 0) {
                btn.style.display = 'inline-flex';
                countSpan.innerText = selected.length;
            } else {
                btn.style.display = 'none';
            }
        }

        function openBulkAssignModal() {
            if (selectedReqIds.length === 0) return;
            isBulkMode = true;

            // Re-use assignModal but change title
            document.getElementById('assignModalTitle').innerText = `Bulk Assign: ${selectedReqIds.length} Tugas`;

            // Populate driver list
            const container = document.getElementById('driverCheckboxList');
            container.innerHTML = allDrivers.map(d => `
                <label class="driver-checkbox-item">
                    <input type="checkbox" name="assign_drivers" value="${d.user_id}">
                    <div class="driver-info">
                        <span class="name">${d.driver_name}</span>
                        <span class="status ${d.is_shipping ? 'busy' : 'free'}">${d.is_shipping ? 'Sedang Jalan' : 'Tersedia'}</span>
                    </div>
                </label>
            `).join('');

            // Pre-fill date (default to today for bulk)
            document.getElementById('assignTargetDate').value = todayDateTimeStr();

            document.getElementById('assignModal').classList.add('show');
        }

        // ── Helpers ─────────────────────────────────────────────────────────
        function todayStr() {
            const now = new Date();
            const y = now.getFullYear();
            const m = String(now.getMonth() + 1).padStart(2, '0');
            const d = String(now.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }

        function todayDateTimeStr() {
            return "<?php echo date('Y-m-d\TH:i'); ?>";
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;

            const icon = type === 'success' ? 'check_circle' : 'error';
            toast.innerHTML = `
                <span class="material-symbols-outlined toast-icon">${icon}</span>
                <div class="toast-content">${message}</div>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'toastFadeOut 0.3s ease-in forwards';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function resetFilters() {
            const today = todayStr();
            document.getElementById('date_from').value = today;
            document.getElementById('date_to').value = today;
            document.getElementById('driverFilter').value = '';
            document.getElementById('statusFilter').value = '';
            currentPage = 1;
            loadHistory();
        }

        // ===== OPEN / CLOSE ADD MODAL =====
        function openAddModal() {
            const modal = document.getElementById('addModal');
            document.getElementById('taskForm').reset();
            const hiddenReqId = document.getElementById('link_pickup_id');
            if (hiddenReqId) hiddenReqId.value = '';

            document.getElementById('taskFormSuratJalan').value = '';
            document.getElementById('taskFormKoli').value = '1';
            document.getElementById('taskFormNotes').value = '';
            document.getElementById('taskFormFile').value = '';
            document.getElementById('taskFormGoodsFile').value = '';

            document.getElementById('taskFormTargetDate').value = todayDateTimeStr();
            document.getElementById('formMsg').innerHTML = '';

            document.querySelectorAll('.searchable-input').forEach(inp => inp.value = '');
            document.querySelectorAll('input[type="hidden"][name*="id"]').forEach(inp => inp.value = '');
            document.getElementById('addPreview').innerHTML = '';
            document.getElementById('addGoodsPreview').innerHTML = '';

            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
        }
        function closeAdd() {
            const modal = document.getElementById('addModal');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = 'none', 200);
        }

        // ===== DETAIL MODAL LOGIC =====
        let detailMap = null;
        let detailMarker = null;

        function openDetail(id) {
            const del = historyData.find(d => d.id === id && !d.is_request);
            if (!del) return;

            const content = document.getElementById('detailContent');

            let proofHtml = '';
            if (del.proof_file_arr && del.proof_file_arr.length > 0) {
                const photos = del.proof_file_arr;
                proofHtml = `
                    <div style="margin-top: 1.25rem;">
                        <div class="time-label" style="margin-bottom:8px;">Foto Bukti Selesai (${photos.length})</div>
                        <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 0.4rem;">
                            ${photos.map(f => `<img src="uploads/${f}" class="photo-thumb" onclick="window.showImagePopup('uploads/${f}')" style="width:100%; aspect-ratio:1/1; object-fit:cover; border-radius:6px; border:1px solid var(--border);" title="Klik untuk memperbesar">`).join('')}
                        </div>
                    </div>
                `;
            }

            const isReq = del.pickup_id || del.request_created_at;
            const typeLabel = del.task_type === 'antar' ? 'DELIVERY' : (isReq ? 'REQ PICKUP' : 'PICKUP');
            const typeClass = del.task_type === 'antar' ? 'badge-antar' : 'badge-kirim';
            const statusClass = del.status === 'pending' ? 'badge-pending' : (del.status === 'in_transit' ? 'badge-transit' : (del.status === 'canceled' ? 'badge-danger' : 'badge-completed'));

            content.innerHTML = `
                <table class="detail-table">
                    <tr>
                        <td>Tipe Tugas</td>
                        <td><span class="detail-badge ${typeClass}">${typeLabel}</span></td>
                    </tr>
                    ${isReq ? `
                    <tr>
                        <td>Request By</td>
                        <td style="color:var(--primary);">
                            <span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle;">person</span>
                            ${del.requester_name || 'User'}
                        </td>
                    </tr>` : ''}
                    <tr>
                        <td>Assign By</td>
                        <td>
                            <span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle; color:var(--primary);">person_edit</span>
                            ${del.creator_name || 'System'}
                        </td>
                    </tr>
                    <tr>
                        <td>No. SJ</td>
                        <td style="color:var(--primary); font-family: monospace; font-size: 0.9rem;">${del.surat_jalan || '-'}</td>
                    </tr>
                    <tr>
                        <td>Driver</td>
                        <td>${del.driver_name}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td><span class="detail-badge ${statusClass}">${(del.status || 'pending').replace('_', ' ').toUpperCase()}</span></td>
                    </tr>
                    <tr>
                        <td>Rute</td>
                        <td style="line-height:1.4;">
                            <div style="font-size:0.75rem; color:var(--text-sub);">${del.origin_name}</div>
                            <div style="font-weight:700;">&raquo; ${del.destination_name}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>Penerima</td>
                        <td>${del.receiver_name || '-'}</td>
                    </tr>
                    <tr>
                        <td>Jadwal</td>
                        <td>${formatDateTime(del.target_date)}</td>
                    </tr>
                    ${del.notes ? `
                    <tr>
                        <td>Catatan Admin</td>
                        <td style="font-weight:400; font-size:0.8rem; color:var(--text-sub); word-break:break-all;">
                            ${(del.notes.trim().match(/^https?:\/\//i)) ? 
                                `<a href="${del.notes.trim().toLowerCase()}" target="_blank" rel="noopener noreferrer" style="color:var(--primary); font-style:italic; text-decoration:underline;">"${del.notes.trim().toLowerCase()}"</a>` : 
                                `"${del.notes}"`}
                        </td>
                    </tr>` : ''}
                    ${del.driver_notes ? `
                    <tr>
                        <td>Ket. Driver</td>
                        <td style="color:var(--success); font-weight:600;">
                            <span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle;">chat</span>
                            ${del.driver_notes}
                        </td>
                    </tr>` : ''}
                    ${del.late_reason ? `
                    <tr>
                        <td>Alasan Terlambat</td>
                        <td style="color:var(--danger); font-weight:600;">
                            <span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle;">warning</span>
                            ${del.late_reason}
                        </td>
                    </tr>` : ''}
                </table>
                <div style="margin-top: 0.5rem;">
                    <div class="time-label" style="margin-bottom:8px;">Foto Surat Jalan (Admin/Request)</div>
                    ${(() => {
                    let files = [];
                    const raw = del.surat_jalan_file; // Array from API
                    const reqRaw = del.request_sj_file_arr; // Array from API

                    if (Array.isArray(raw) && raw.length > 0) {
                        files = raw;
                    } else if (Array.isArray(reqRaw) && reqRaw.length > 0) {
                        files = reqRaw;
                    }

                    if (files.length === 0) return '<div class="time-value">- Tidak ada foto -</div>';
                    return `
                            <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 0.4rem;">
                                ${files.map(f => `<img src="uploads/${f}" class="photo-thumb" onclick="window.showImagePopup('uploads/${f}')" style="width:100%; aspect-ratio:1/1; object-fit:cover; border-radius:6px; border:1px solid var(--border);" title="Klik untuk memperbesar">`).join('')}
                            </div>
                        `;
                })()}
                </div>

                ${(() => {
                    let allGoods = [];

                    // From Delivery
                    const rawGoods = del.goods_file_arr || del.goods_file;
                    if (Array.isArray(rawGoods)) {
                        allGoods = [...allGoods, ...rawGoods];
                    } else if (rawGoods) {
                        try {
                            const parsed = JSON.parse(rawGoods);
                            if (parsed) {
                                allGoods = [...allGoods, ...(Array.isArray(parsed) ? parsed : [parsed])];
                            }
                        } catch (e) {
                            if (typeof rawGoods === 'string' && !rawGoods.startsWith('[') && !rawGoods.startsWith('{')) {
                                allGoods.push(rawGoods);
                            }
                        }
                    }

                    // From Request
                    const reqRawGoods = del.request_goods;
                    if (Array.isArray(reqRawGoods)) {
                        allGoods = [...allGoods, ...reqRawGoods];
                    }

                    // Deduplicate and filter empty
                    const uniqueGoods = [...new Set(allGoods)].filter(f => f && typeof f === 'string' && f.trim() !== '');

                    if (uniqueGoods.length === 0) return '';
                    return `
                        <div style="margin-top: 0.6rem;">
                            <div class="time-label" style="margin-bottom:6px; font-size:0.6rem;">Foto Barang / Koli (${uniqueGoods.length})</div>
                            <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 0.4rem;">
                                ${uniqueGoods.map(f => `<img src="uploads/${f}" class="photo-thumb" onclick="window.showImagePopup('uploads/${f}')" style="width:100%; aspect-ratio:1/1; object-fit:cover; border-radius:6px; border:1px solid var(--border);" title="Klik untuk memperbesar">`).join('')}
                            </div>
                        </div>
                    `;
                })()}
                ${proofHtml}
            `;

            document.getElementById('detailStartTime').innerText = formatDateTime(del.start_time) || 'Belum Mulai';
            document.getElementById('detailEndTime').innerText = formatDateTime(del.end_time) || 'Belum Selesai';

            const modal = document.getElementById('detailModal');
            const mapContainer = document.getElementById('detailMap');
            if (del.status === 'completed') {
                mapContainer.style.display = 'none';
            } else {
                mapContainer.style.display = 'block';
                setTimeout(() => {
                    if (!detailMap) {
                        detailMap = L.map('detailMap').setView([-6.200000, 106.816666], 13);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(detailMap);
                    }
                    if (detailMarker) detailMap.removeLayer(detailMarker);
                    if (del.driver_lat && del.driver_lng) {
                        const latlng = [del.driver_lat, del.driver_lng];
                        detailMap.setView(latlng, 15);
                        detailMarker = L.marker(latlng).addTo(detailMap)
                            .bindPopup(`<b>${del.driver_name}</b><br>Posisi Terakhir<br><small>${del.location_updated}</small>`)
                            .openPopup();
                    } else {
                        detailMap.setView([-6.200000, 106.816666], 11);
                    }
                    detailMap.invalidateSize();
                }, 300);
            }

            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function closeDetail() {
            const modal = document.getElementById('detailModal');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = 'none', 200);
        }

        // (Functions moved to top)

        // Updated single Assign Modal logic
        let currentAssignReqId = null;
        function openAssignModal(id) {
            isBulkMode = false;
            currentAssignReqId = id;
            const req = historyData.find(r => r.id === id && r.is_request);
            if (!req) return;

            document.getElementById('assignModalTitle').innerText = `Assign Tugas: ${req.surat_jalan || 'Request'}`;

            // Populate driver list with checkboxes
            const container = document.getElementById('driverCheckboxList');
            container.innerHTML = allDrivers.map(d => `
                <label class="driver-checkbox-item">
                    <input type="checkbox" name="assign_drivers" value="${d.user_id}">
                    <div class="driver-info">
                        <span class="name">${d.driver_name}</span>
                        <span class="status ${d.is_shipping ? 'busy' : 'free'}">${d.is_shipping ? 'Sedang Jalan' : 'Tersedia'}</span>
                    </div>
                </label>
            `).join('');

            // Pre-fill date
            document.getElementById('assignTargetDate').value = req.scheduled_date ? req.scheduled_date.replace(' ', 'T') : todayDateTimeStr();

            const modal = document.getElementById('assignModal');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function closeAssignModal() {
            const modal = document.getElementById('assignModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                currentAssignReqId = null;
            }, 200);
        }

        async function submitMultipleAssign() {
            const selectedDrivers = Array.from(document.querySelectorAll('input[name="assign_drivers"]:checked')).map(cb => cb.value);
            if (selectedDrivers.length === 0) {
                showToast('Pilih minimal satu driver!', 'error');
                return;
            }

            const btn = document.getElementById('submitAssignBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined rotating">autorenew</span> Memproses...';

            const targets = isBulkMode ? selectedReqIds : [currentAssignReqId];
            const targetDate = document.getElementById('assignTargetDate').value;

            try {
                let successCount = 0;
                for (const requestId of targets) {
                    const req = historyData.find(r => r.id === requestId);
                    if (!req) continue;

                    for (const driverId of selectedDrivers) {
                        const formData = new FormData();
                        formData.append('driver_id', driverId);
                        formData.append('pickup_id', req.id);
                        formData.append('origin_name', req.origin_name);
                        formData.append('dest_name', req.destination_name);
                        formData.append('task_type', 'kirim');
                        formData.append('surat_jalan', req.surat_jalan);
                        formData.append('total_koli', req.total_koli);
                        formData.append('notes', req.notes);
                        formData.append('target_date', targetDate);

                        if (req.surat_jalan_file) {
                            formData.append('existing_file', req.surat_jalan_file);
                        }

                        if (locationData) {
                            const loc = locationData.find(l => l.name === req.destination_name);
                            if (loc) {
                                formData.append('dest_id', loc.id);
                                formData.append('dest_lat', loc.lat);
                                formData.append('dest_lng', loc.lng);
                            }
                        }

                        await fetch(`${API_URL}?action=assign_task`, { method: 'POST', body: formData });
                    }
                    successCount++;
                }

                closeAssignModal();
                loadHistory();
                if (window.updateSidebarBadge) updateSidebarBadge();
                showToast(`Berhasil menugaskan ${successCount} permintaan ke ${selectedDrivers.length} driver.`);

                // Reset bulk
                isBulkMode = false;
                selectedReqIds = [];
                updateBulkBtn();
                const selAll = document.getElementById('selectAllReqs');
                if (selAll) selAll.checked = false;

            } catch (err) {
                showToast('Terjadi kesalahan saat memproses penugasan.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined">send</span> Konfirmasi Penugasan';
            }
        }

        // ===== OPEN / CLOSE EDIT MODAL =====
        async function openEdit(id, fieldToEdit = null) {
            if (USER_ROLE === 'admin') {
                showToast('Akses ditolak', 'error');
                return;
            }
            document.getElementById('editFormMsg').innerHTML = '';
            const res = await fetch(`${API_URL}?action=get_delivery&id=${id}`);
            const d = await res.json();
            if (d.error) {
                showToast('Gagal memuat data penugasan.', 'error');
                return;
            }

            document.getElementById('editId').value = d.id;
            document.getElementById('editTargetDate').value = d.target_date ? d.target_date.replace(' ', 'T').slice(0, 16) : todayDateTimeStr();
            document.getElementById('editTaskType').value = d.task_type;
            document.getElementById('editStatus').value = d.status;

            // Populate driver + location selects then set values
            populateEditLocationSelects(d.origin_id, d.destination_id, d.driver_id);

            document.getElementById('edit_origin_name').value = d.origin_name;
            document.getElementById('edit_dest_name').value = d.destination_name;
            document.getElementById('edit_dest_lat').value = d.destination_lat;
            document.getElementById('edit_dest_lng').value = d.destination_lng;

            document.getElementById('editSuratJalan').value = d.surat_jalan || '';
            document.getElementById('editKoli').value = d.total_koli || 1;
            let editNotesVal = d.notes || '';
            if (editNotesVal.trim().match(/^https?:\/\//i)) {
                editNotesVal = editNotesVal.trim().toLowerCase();
            }
            document.getElementById('editNotes').value = editNotesVal;
            document.getElementById('editReceiverName').value = d.receiver_name || '';

            document.querySelectorAll('.searchable-input').forEach(inp => inp.value = '');

            // ===== Populate existing files into hidden inputs & render previews =====
            editSelectedFiles = { 'editSjPrev': [], 'editGoodsPrev': [], 'editProofPrev': [] };
            document.getElementById('editSjInput').value = '';
            document.getElementById('editGoodsInput').value = '';
            document.getElementById('editProofInput').value = '';

            // Parse existing file JSON from delivery data
            const parseSafeJson = (val) => {
                if (!val) return [];
                try {
                    const p = JSON.parse(val);
                    return Array.isArray(p) ? p : (p ? [p] : []);
                } catch(e) { return val ? [val] : []; }
            };
            document.getElementById('editSjExisting').value = d.surat_jalan_file ? (typeof d.surat_jalan_file === 'string' ? d.surat_jalan_file : JSON.stringify(d.surat_jalan_file)) : '';
            document.getElementById('editGoodsExisting').value = d.goods_file ? (typeof d.goods_file === 'string' ? d.goods_file : JSON.stringify(d.goods_file)) : '';
            document.getElementById('editProofExisting').value = d.proof_file ? (typeof d.proof_file === 'string' ? d.proof_file : JSON.stringify(d.proof_file)) : '';

            renderEditContainer('sj');
            renderEditContainer('goods');
            renderEditContainer('proof');

            // Field Visibility Logic
            if (fieldToEdit) {
                showEditField(fieldToEdit);
            } else {
                showAllEditFields();
            }

            const modal = document.getElementById('editModal');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function showEditField(fieldName) {
            const groups = document.querySelectorAll('#editModal .form-group');
            let found = false;
            groups.forEach(g => {
                const field = g.getAttribute('data-edit-field');
                // Special case: 'driver' column can also show 'date'
                if (field === fieldName || (fieldName === 'driver' && field === 'date')) {
                    g.style.display = 'block';
                    found = true;
                } else {
                    g.style.display = 'none';
                }
            });
            document.getElementById('showAllEditWrap').style.display = 'block';

            // Update title
            const titles = { 'driver': 'Edit Driver & Tanggal', 'route': 'Edit Rute Pengiriman', 'sj': 'Edit Surat Jalan & Koli', 'status': 'Edit Status' };
            document.querySelector('#editModal .modal-title').innerHTML = `<span class="material-symbols-outlined">edit_note</span> ${titles[fieldName] || 'Edit Penugasan'}`;
        }

        function showAllEditFields() {
            document.querySelectorAll('#editModal .form-group').forEach(g => g.style.display = 'block');
            document.getElementById('showAllEditWrap').style.display = 'none';
            document.querySelector('#editModal .modal-title').innerHTML = `<span class="material-symbols-outlined">edit_note</span> Edit Penugasan`;
        }

        function closeEdit() {
            const modal = document.getElementById('editModal');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = 'none', 200);

            // Reset file state
            editSelectedFiles = { 'editSjPrev': [], 'editGoodsPrev': [], 'editProofPrev': [] };
            document.getElementById('editSjExisting').value = '';
            document.getElementById('editGoodsExisting').value = '';
            document.getElementById('editProofExisting').value = '';
            document.getElementById('editSjPrev').innerHTML = '';
            document.getElementById('editGoodsPrev').innerHTML = '';
            document.getElementById('editProofPrev').innerHTML = '';
            document.getElementById('editSjInput').value = '';
            document.getElementById('editGoodsInput').value = '';
            document.getElementById('editProofInput').value = '';
        }

        // ===== LOAD FORM DATA (drivers + locations) =====
        async function loadFormData() {
            try {
                const dRes = await fetch(`${API_URL}?action=get_drivers`);
                allDrivers = await dRes.json();
                populateDriverSelect('driverSelect');
                populateDriverSelect('driverFilter', true);

                const lRes = await fetch(`${API_URL}?action=get_locations`);
                locationData = await lRes.json();
                populateLocationSelects('originSelect', 'destSelect');
            } catch (err) { console.error(err); }
        }

        function populateDriverSelect(selectId, addAll = false) {
            const sel = document.getElementById(selectId);
            const cur = sel.value;
            sel.innerHTML = addAll
                ? '<option value="">Semua Driver</option>'
                : '<option value="">-- Pilih Driver --</option>';
            allDrivers.forEach(d => {
                sel.innerHTML += `<option value="${d.user_id}">${d.driver_name}</option>`;
            });
            if (cur) sel.value = cur;
        }

        function populateLocationSelects(originId, destId) {
            renderLocationOptions('originList', 'originSelect', 'origin_name');
            renderLocationOptions('destList', 'destSelect', 'dest_name', true);
            renderDriverOptions('driverList', 'driverSelect');
        }

        function renderDriverOptions(listId, hiddenInputId) {
            const list = document.getElementById(listId);
            list.innerHTML = `
                <div style="padding:10px; border-bottom:1px solid var(--border); position:sticky; top:0; background:white;">
                    <input type="text" placeholder="Cari driver..." class="select-search" onkeyup="filterOptions(this, '${listId}')" style="margin:0;">
                </div>
            `;

            allDrivers.sort((a, b) => a.driver_name.localeCompare(b.driver_name)).forEach(d => {
                const div = document.createElement('div');
                div.className = 'option-item';
                div.innerHTML = `👤 ${d.driver_name} ${d.is_shipping ? '<span style="font-size:0.7rem; color:#f59e0b; margin-left:auto;">Sedang Jalan</span>' : ''}`;
                div.onclick = () => selectDriver(d, listId, hiddenInputId);
                list.appendChild(div);
            });
        }

        function selectDriver(driver, listId, hiddenInputId) {
            const list = document.getElementById(listId);
            const hidden = document.getElementById(hiddenInputId);
            const display = list.parentElement.querySelector('.searchable-input');

            hidden.value = driver.user_id;
            display.value = driver.driver_name;

            list.classList.remove('show');
            list.querySelectorAll('.option-item').forEach(item => item.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
        }

        function renderLocationOptions(listId, hiddenInputId, nameInputId, isDest = false) {
            const list = document.getElementById(listId);
            list.innerHTML = `
                <div style="padding:10px; border-bottom:1px solid var(--border); position:sticky; top:0; background:white;">
                    <input type="text" placeholder="Ketik untuk mencari..." class="select-search" onkeyup="filterOptions(this, '${listId}')" style="margin:0;">
                </div>
            `;

            locationData.sort((a, b) => a.name.localeCompare(b.name)).forEach(loc => {
                const div = document.createElement('div');
                div.className = 'option-item';
                div.innerHTML = `${loc.name}`;
                div.onclick = () => selectLocation(loc, listId, hiddenInputId, nameInputId, isDest);
                list.appendChild(div);
            });
        }

        function toggleOptions(listId) {
            // Close other lists
            document.querySelectorAll('.options-list').forEach(l => {
                if (l.id !== listId) l.classList.remove('show');
            });
            const list = document.getElementById(listId);
            list.classList.toggle('show');

            // Focus search input automatically
            if (list.classList.contains('show')) {
                const searchInput = list.querySelector('.select-search');
                if (searchInput) {
                    setTimeout(() => {
                        searchInput.value = ''; // Clear search when opening
                        searchInput.focus();
                        // Reset filter to show all
                        filterOptions(searchInput, listId);
                    }, 50);
                }
            }
        }

        function filterOptions(input, listId) {
            const filter = input.value.toLowerCase();
            const list = document.getElementById(listId);
            const items = list.querySelectorAll('.option-item');
            items.forEach(item => {
                const txt = item.innerText.toLowerCase();
                item.style.display = txt.includes(filter) ? 'flex' : 'none';
            });
        }

        function selectLocation(loc, listId, hiddenInputId, nameInputId, isDest) {
            const list = document.getElementById(listId);
            const hidden = document.getElementById(hiddenInputId);
            const nameInp = document.getElementById(nameInputId);
            const display = list.parentElement.querySelector('.searchable-input');

            hidden.value = loc.id;
            nameInp.value = loc.name;
            display.value = loc.name;

            if (isDest) {
                let latId = 'dest_lat';
                let lngId = 'dest_lng';

                if (hiddenInputId.includes('edit')) {
                    latId = 'edit_dest_lat';
                    lngId = 'edit_dest_lng';
                } else if (hiddenInputId.startsWith('qe_')) {
                    latId = 'qe_dest_lat';
                    lngId = 'qe_dest_lng';
                }

                const latInp = document.getElementById(latId);
                const lngInp = document.getElementById(lngId);
                if (latInp) latInp.value = loc.lat;
                if (lngInp) lngInp.value = loc.lng;
            }

            list.classList.remove('show');

            // Highlight selected
            list.querySelectorAll('.option-item').forEach(item => item.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.searchable-group')) {
                document.querySelectorAll('.options-list').forEach(l => l.classList.remove('show'));
            }
        });

        function filterSelect(input, selectId) {
            // Deprecated, using filterOptions
        }

        function populateEditSelects() {
            renderDriverOptions('editDriverList', 'editDriverSelect');
        }

        function populateEditLocationSelects(selectedOriginId, selectedDestId, selectedDriverId) {
            renderLocationOptions('editOriginList', 'editOriginSelect', 'edit_origin_name');
            renderLocationOptions('editDestList', 'editDestSelect', 'edit_dest_name', true);
            renderDriverOptions('editDriverList', 'editDriverSelect');

            if (selectedDriverId) {
                const d = allDrivers.find(drv => drv.user_id == selectedDriverId);
                if (d) {
                    document.getElementById('editDriverSelect').value = d.user_id;
                    const container = document.querySelector('#editDriverList').parentElement;
                    const display = container.querySelector('.searchable-input');
                    if (display) display.value = d.driver_name;
                }
            }
            if (selectedOriginId) {
                const loc = locationData.find(l => l.id == selectedOriginId);
                if (loc) {
                    document.getElementById('editOriginSelect').value = loc.id;
                    document.getElementById('edit_origin_name').value = loc.name;
                    const container = document.querySelector('#editOriginList').parentElement;
                    const display = container.querySelector('.searchable-input');
                    if (display) display.value = loc.name;
                }
            }
            if (selectedDestId) {
                const loc = locationData.find(l => l.id == selectedDestId);
                if (loc) {
                    document.getElementById('editDestSelect').value = loc.id;
                    document.getElementById('edit_dest_name').value = loc.name;
                    const container = document.querySelector('#editDestList').parentElement;
                    const display = container.querySelector('.searchable-input');
                    if (display) display.value = loc.name;
                }
            }
        }

        // ===== HELPERS FOR HIDDEN FIELDS =====
        function updateOriginName(sel) {
            const loc = locationData.find(l => l.id == sel.value);
            document.getElementById('origin_name').value = loc ? loc.name : '';
        }
        function updateDestDetails(sel) {
            const loc = locationData.find(l => l.id == sel.value);
            if (loc) {
                document.getElementById('dest_name').value = loc.name;
                document.getElementById('dest_lat').value = loc.lat;
                document.getElementById('dest_lng').value = loc.lng;
            }
        }
        function updateEditOriginName(sel) {
            const loc = locationData.find(l => l.id == sel.value);
            document.getElementById('edit_origin_name').value = loc ? loc.name : '';
        }

        const ADMIN_NAME = '<?php echo $_SESSION['name'] ?? "Admin"; ?>';

        // Global storage for incremental file selection
        let selectedFiles = {
            'addPreview': [],
            'addGoodsPreview': []
        };

        let editSelectedFiles = {
            'editSjPrev': [],
            'editGoodsPrev': [],
            'editProofPrev': []
        };

        async function watermarkFile(file, data) {
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        const canvas = document.getElementById('watermarkCanvas');
                        const ctx = canvas.getContext('2d');
                        const maxW = 1200;
                        let w = img.width;
                        let h = img.height;
                        if (w > maxW) {
                            h = h * (maxW / w);
                            w = maxW;
                        }
                        canvas.width = w;
                        canvas.height = h;
                        ctx.drawImage(img, 0, 0, w, h);

                        const overlayH = h * 0.18;
                        const gradient = ctx.createLinearGradient(0, h - overlayH, 0, h);
                        gradient.addColorStop(0, 'rgba(0,0,0,0)');
                        gradient.addColorStop(0.3, 'rgba(0,0,0,0.6)');
                        gradient.addColorStop(1, 'rgba(0,0,0,0.8)');
                        ctx.fillStyle = gradient;
                        ctx.fillRect(0, h - overlayH, w, overlayH);

                        const fontSize = Math.max(14, Math.round(w * 0.025));
                        ctx.fillStyle = 'white';
                        ctx.font = `bold ${fontSize}px Inter, sans-serif`;
                        ctx.shadowColor = 'rgba(0,0,0,0.6)';
                        ctx.shadowBlur = 4;
                        ctx.shadowOffsetX = 2;
                        ctx.shadowOffsetY = 2;

                        const now = new Date();
                        const dateStr = now.toLocaleDateString('id-ID', { year: 'numeric', month: '2-digit', day: '2-digit' });
                        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                        const lines = [
                            `📦 SJ: ${data.surat_jalan ? data.surat_jalan.trim() : '-'}`,
                            `🏁 DARI: ${data.origin ? data.origin.trim() : '-'}`,
                            `🏁 TUJUAN: ${data.destination ? data.destination.trim() : '-'}`,
                            `👤 ADMIN: ${data.admin ? data.admin.trim() : '-'}`,
                            `📅 ${dateStr} | 🕒 ${timeStr}`
                        ];

                        let padding = fontSize * 1.2;
                        lines.reverse().forEach((line, i) => {
                            ctx.fillText(line, padding, h - (padding + (i * fontSize * 1.4)));
                        });

                        canvas.toBlob((blob) => {
                            resolve(blob);
                        }, 'image/webp', 0.82);
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        async function handleImagePreview(input, containerId) {
            if (!input.files || input.files.length === 0) return;

            const files = Array.from(input.files);

            for (const file of files) {
                // Store raw original file
                selectedFiles[containerId].push(file);
            }

            input.value = '';
            renderPreviews(containerId);
        }

        function renderPreviews(containerId) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';

            selectedFiles[containerId].forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="preview-remove" onclick="removeImage('${containerId}', ${index})">
                            <span class="material-symbols-outlined" style="font-size:16px;">close</span>
                        </button>
                    `;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }

        function removeImage(containerId, index) {
            selectedFiles[containerId].splice(index, 1);
            renderPreviews(containerId);
        }

        function syncFilesToInput(containerId, inputId) {
            const dt = new DataTransfer();
            selectedFiles[containerId].forEach(file => dt.items.add(file));
            document.getElementById(inputId).files = dt.files;
        }

        function handleEditFileChange(input, containerId) {
            if (!input.files || input.files.length === 0) return;
            const files = Array.from(input.files);
            for (const file of files) {
                editSelectedFiles[containerId].push(file);
            }
            input.value = '';
            const type = containerId === 'editSjPrev' ? 'sj' : (containerId === 'editGoodsPrev' ? 'goods' : 'proof');
            renderEditContainer(type);
        }

        function renderEditContainer(type) {
            const containerId = type === 'sj' ? 'editSjPrev' : (type === 'goods' ? 'editGoodsPrev' : 'editProofPrev');
            const hiddenId = type === 'sj' ? 'editSjExisting' : (type === 'goods' ? 'editGoodsExisting' : 'editProofExisting');
            const container = document.getElementById(containerId);
            const hidden = document.getElementById(hiddenId);
            
            container.innerHTML = '';
            
            // 1. Render existing files from hidden input
            let existingFiles = [];
            if (hidden.value) {
                try {
                    const parsed = JSON.parse(hidden.value);
                    existingFiles = Array.isArray(parsed) ? parsed : [parsed];
                } catch(e) {
                    existingFiles = [hidden.value];
                }
            }
            
            existingFiles.forEach(f => {
                if (f) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                        <img src="uploads/${f}" onclick="window.showImagePopup('uploads/${f}')" style="cursor:pointer;" title="Klik untuk memperbesar">
                        <button type="button" class="preview-remove" onclick="deleteExistingFile('${f}', '${type}', this)" style="background:#ef4444;" title="Hapus foto ini">
                            <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                        </button>
                    `;
                    container.appendChild(div);
                }
            });
            
            // 2. Render newly selected files
            editSelectedFiles[containerId].forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="preview-remove" onclick="removeEditImage('${containerId}', ${index})">
                            <span class="material-symbols-outlined" style="font-size:16px;">close</span>
                        </button>
                    `;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }

        function removeEditImage(containerId, index) {
            editSelectedFiles[containerId].splice(index, 1);
            const type = containerId === 'editSjPrev' ? 'sj' : (containerId === 'editGoodsPrev' ? 'goods' : 'proof');
            renderEditContainer(type);
        }

        function deleteExistingFile(filename, type, btn) {
            if (confirm('Hapus foto ini dari tugas?')) {
                const hiddenId = type === 'sj' ? 'editSjExisting' : (type === 'goods' ? 'editGoodsExisting' : 'editProofExisting');
                const hidden = document.getElementById(hiddenId);
                let files = [];
                if (hidden.value) {
                    try {
                        const parsed = JSON.parse(hidden.value);
                        files = Array.isArray(parsed) ? parsed : [parsed];
                    } catch(e) {
                        files = [hidden.value];
                    }
                }
                files = files.filter(f => f !== filename);
                hidden.value = files.length > 0 ? JSON.stringify(files) : '';
                
                // Re-render
                renderEditContainer(type);
            }
        }

        function updateEditDestDetails(sel) {
            const loc = locationData.find(l => l.id == sel.value);
            if (loc) {
                document.getElementById('edit_dest_name').value = loc.name;
                document.getElementById('edit_dest_lat').value = loc.lat;
                document.getElementById('edit_dest_lng').value = loc.lng;
            }
        }

        // ===== LOAD COMBINED HISTORY TABLE =====
        async function loadHistory() {
            const tbody = document.querySelector('#historyTable tbody');
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="13" style="text-align:center; padding:3rem;">
                    <div style="display:flex; flex-direction:column; align-items:center; gap:1rem;">
                        <span class="material-symbols-outlined rotating" style="font-size:2.5rem; color:var(--primary);">autorenew</span>
                        <div style="font-weight:600; color:var(--text-sub);">Memuat data penugasan...</div>
                    </div>
                </td></tr>`;
            }

            const dateF = document.getElementById('date_from').value;
            const dateT = document.getElementById('date_to').value;
            const driverId = document.getElementById('driverFilter').value;
            const status = document.getElementById('statusFilter').value;
            const search = document.getElementById('searchInput').value;

            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000); // 10s timeout

                // Prepare fetch promises
                const deliveriesPromise = fetch(`${API_URL}?action=get_deliveries&date_from=${dateF}&date_to=${dateT}&driver_id=${driverId}&status=${status}&search=${search}`, { signal: controller.signal })
                    .then(r => r.json());

                let pickupPromise = Promise.resolve([]);
                if (!driverId && (!status || status === 'pending')) {
                    pickupPromise = fetch(`${API_URL}?action=get_pickup_requests&status=pending`, { signal: controller.signal })
                        .then(r => r.json());
                }

                const [deliveries, pData] = await Promise.all([deliveriesPromise, pickupPromise]);
                clearTimeout(timeoutId);

                if (deliveries.error) throw new Error(deliveries.error);
                if (!Array.isArray(deliveries)) throw new Error('Data pengiriman bermasalah');

                let pendingPickups = [];
                if (Array.isArray(pData)) {
                    // Only show pickup requests that HAVEN'T been assigned to any driver yet
                    pendingPickups = pData
                        .filter(p => !p.assigned_drivers)
                        .map(p => ({ ...p, is_request: true, status: 'request' }));
                }

                // Merge and sort
                const combined = [...pendingPickups, ...deliveries];
                historyData = combined;
                applySorting();

                currentPage = 1;
                renderHistoryTable();
            } catch (err) {
                console.error(err);
                const tbody = document.querySelector('#historyTable tbody');
                if (tbody) tbody.innerHTML = `<tr><td colspan="13" style="text-align:center; padding:3rem; color:var(--danger);">${err.message}</td></tr>`;
            }
        }

        function renderHistoryTable() {
            try {
                updateSortIndicators();
                const combined = historyData;
                const tbody = document.querySelector('#historyTable tbody');
                const exportBtn = document.getElementById('exportBtn');
                const countBadge = document.getElementById('rowCountBadge');
                const countNum = document.getElementById('rowCountNum');

                const total = combined.length;
                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                const pagedData = combined.slice(start, end);

                // Update Pagination Buttons
                document.getElementById('btnPrev').disabled = currentPage <= 1;
                document.getElementById('btnNext').disabled = end >= total;

                if (!pagedData.length && combined.length > 0 && currentPage > 1) {
                    currentPage = 1;
                    renderHistoryTable();
                    return;
                }

                if (!combined.length) {
                    tbody.innerHTML = `<tr><td colspan="13">
                    <div class="empty-state">
                        <span class="material-symbols-outlined">search_off</span>
                        <p>Belum ada data penugasan untuk filter ini.</p>
                    </div>
                </td></tr>`;
                    countBadge.style.display = 'none';
                    document.querySelector('.pagination-bar').style.display = 'none';
                    return;
                }

                document.querySelector('.pagination-bar').style.display = 'flex';
                countNum.textContent = combined.length;
                countBadge.style.display = 'inline-flex';
                exportBtn.style.display = 'inline-flex';

                tbody.innerHTML = pagedData.map(item => {

                    const displayTime = item.request_created_at || item.created_at;
                    const dt = new Date(displayTime);
                    const dateStr = dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                    const timeStr = dt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                    if (item.is_request) {
                        const req = item;
                        return `
                            <tr class="request-row">
                                <td>${CAN_ASSIGN ? `<input type="checkbox" class="req-checkbox" value="${req.id}">` : ''}</td>
                                <td style="color:var(--text-sub); font-size:0.8rem;">-</td>
                                <td>
                                    <div style="font-weight:700; font-size:0.85rem;">${dateStr}</div>
                                    <div style="font-size:0.7rem; color:var(--text-sub); margin-top:4px;">
                                        Start: - <br> End: -
                                    </div>
                                    <div style="font-weight:700; color:#ef4444; font-size:0.75rem; margin-top:4px;">BELUM ASSIGN</div>
                                </td>
                                <td>
                                    <div style="font-size:0.75rem; color:var(--text-sub);">${req.origin_name}</div>
                                    <div style="font-weight:700; color:var(--text); font-size:0.85rem;">&raquo; ${req.destination_name}</div>
                                </td>
                                <td style="font-size:0.8rem;">
                                    <strong>${req.surat_jalan || '-'}</strong><br>
                                    <span style="color:var(--text-muted);">${req.total_koli || 0} Koli</span>
                                </td>
                                <td style="font-size:0.8rem;">${req.receiver_name || '-'}</td>
                                <td><span class="badge badge-pending">REQ PICKUP</span></td>
                                <td><span class="badge badge-pending">BUTUH PROSES</span></td>
                                <td style="font-size:0.75rem; color:var(--text-muted);">-</td>
                                <td style="text-align:right;">
                                    <div style="display:flex; justify-content:flex-end; gap:5px;">
                                        ${CAN_ASSIGN ? `
                                        <button class="btn-icon" onclick="openAssignModal(${req.id})" title="Assign Driver">
                                            <span class="material-symbols-outlined">person_add</span>
                                        </button>
                                        ` : ''}
                                    </div>
                                </td>
                            </tr>`;
                    } else {
                        const del = item;
                        const cls = del.status === 'pending' ? 'badge-pending' : (del.status === 'in_transit' ? 'badge-transit' : (del.status === 'canceled' ? 'badge-danger' : 'badge-completed'));
                        const typeBadge = del.task_type === 'antar' ? 'badge-antar' : 'badge-kirim';

                        // Inline Edit Logic
                        const isEditable = del.status === 'pending' && CAN_EDIT;
                        const editableClass = isEditable ? 'editable-cell' : 'disabled';
                        const editIcon = isEditable ? '<div class="inline-edit-btn"><span class="material-symbols-outlined" style="font-size:18px;">edit</span></div>' : '';
                        const editClick = isEditable ? `onclick="openEdit(${del.id})"` : '';

                        let requestDateStr = '-';
                        if (del.request_created_at) {
                            const rd = new Date(del.request_created_at);
                            requestDateStr = rd.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                        }

                        let assignDateStr = '-';
                        if (del.created_at) {
                            const ad = new Date(del.created_at);
                            assignDateStr = ad.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                        }

                        let targetDateStr = '-';
                        if (del.target_date) {
                            // Replace '-' with '/' for broad cross-browser compatibility (e.g. iOS Safari)
                            const normalizedDate = del.target_date.replace(/-/g, '/');
                            const td = new Date(normalizedDate);
                            if (!isNaN(td.getTime())) {
                                targetDateStr = td.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                            } else {
                                targetDateStr = assignDateStr;
                            }
                        } else {
                            targetDateStr = assignDateStr;
                        }

                        const mapsBtn = del.destination_lat ? `<a href="https://www.google.com/maps?q=${del.destination_lat},${del.destination_lng}" target="_blank" class="maps-link"><span class="material-symbols-outlined">map</span> Maps</a>` : '';

                        return `
                            <tr>
                                <td></td>
                                <td style="font-size:0.75rem; line-height: 1.4;">
                                    <div style="font-weight:700; color:var(--text);">${assignDateStr}</div>
                                    <div style="color:var(--text-sub);">${del.created_at ? new Date(del.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB' : '-'}</div>
                                    <div style="font-weight:600; color:var(--text); margin-top:2px;">
                                        <span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle; color:var(--primary);">person_edit</span>
                                        ${del.creator_name || 'Admin'}
                                    </div>
                                </td>
                                <td class="${editableClass}" ${isEditable ? `onclick="openQuickEdit(${del.id}, 'driver')"` : ''}>
                                    ${editIcon}
                                    <div style="font-weight:700; font-size:0.85rem;">${targetDateStr}</div>
                                    <div style="font-size:0.7rem; color:var(--text-sub); margin-top:4px;">
                                        Start: ${del.start_time ? new Date(del.start_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '-'} <br>
                                        End: ${del.end_time ? new Date(del.end_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '-'}
                                    </div>
                                    <div style="font-weight:800; color:var(--text); font-size:0.8rem; margin-top:4px;">${del.driver_name}</div>
                                </td>
                                <td class="${editableClass}" ${isEditable ? `onclick="openQuickEdit(${del.id}, 'route')"` : ''}>
                                    ${editIcon}
                                    <div style="font-size:0.75rem; color:var(--text-sub);">${del.origin_name}</div>
                                    <div style="font-weight:700; color:var(--text); font-size:0.85rem;">&raquo; ${del.destination_name} ${mapsBtn}</div>
                                </td>
                                <td style="font-size:0.8rem;" class="${editableClass}" ${isEditable ? `onclick="openQuickEdit(${del.id}, 'sj')"` : ''}>
                                    ${editIcon}
                                    <strong>${del.surat_jalan || '-'}</strong><br>
                                    <span style="color:var(--text-muted);">${del.total_koli || 0} Koli</span>
                                </td>
                                <td style="font-size:0.8rem;">${del.receiver_name || '-'}</td>
                                <td><span class="badge ${typeBadge}">${del.task_type === 'antar' ? 'DELIVERY' : ((del.pickup_id || del.request_created_at) ? 'REQ PICKUP' : 'PICKUP')}</span></td>
                                <td>
                                    <span class="badge ${cls}">${(del.status || 'pending').replace('_', ' ').toUpperCase()}</span>
                                </td>
                                <td style="font-size:0.75rem; color:var(--text-sub);" title="${del.driver_notes || del.late_reason || ''}">
                                    <div style="word-wrap: break-word; min-width: 120px;">
                                        ${del.driver_notes || del.late_reason || '-'}
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:flex; justify-content:flex-end; gap:5px;">
                                        <button class="btn-icon" onclick="openDetail(${del.id})" title="Detail"><span class="material-symbols-outlined">visibility</span></button>
                                        ${((CAN_EDIT && del.status === 'pending' && USER_ROLE !== 'admin') || (USER_ROLE === 'superadmin' || USER_ROLE === 'controller')) ? `
                                        <button class="btn-icon" onclick="openEdit(${del.id})" title="Edit"><span class="material-symbols-outlined">edit</span></button>
                                        ` : ''}
                                        ${(del.status === 'canceled' && CAN_ASSIGN && !parseInt(del.has_reassigned_task)) ? `
                                        <button class="btn-icon" style="color:var(--primary);" onclick="openQuickEdit(${del.id}, 'reassign')" title="Reassign Driver">
                                            <span class="material-symbols-outlined">person_add</span>
                                        </button>
                                        ` : ''}
                                        ${CAN_WRITE ? `
                                        <button class="btn-icon" onclick="deleteDelivery(${del.id})" title="Hapus"><span class="material-symbols-outlined">delete</span></button>
                                        ` : ''}
                                    </div>
                                </td>
                            </tr>`;
                    }
                }).join('');
            } catch (err) {
                console.error('History Load Error:', err);
                const tbody = document.querySelector('#historyTable tbody');
                if (tbody) {
                    let errMsg = err.message;
                    if (err.name === 'AbortError') errMsg = 'Koneksi lambat (Request Timeout). Silakan Refresh.';

                    tbody.innerHTML = `<tr><td colspan="13">
                        <div class="empty-state" style="color: #ef4444; padding: 3rem;">
                            <span class="material-symbols-outlined" style="font-size:3rem;">error</span>
                            <p style="font-weight:600; margin-top:1rem;">Gagal Memuat Data</p>
                            <p style="font-size:0.85rem; opacity:0.8;">${errMsg}</p>
                            <button onclick="loadHistory()" class="btn btn-primary btn-sm" style="margin-top:1.5rem;">
                                <span class="material-symbols-outlined">refresh</span> Coba Lagi
                            </button>
                        </div>
                    </td></tr>`;
                }
            }
        }

        // ===== SORTING FUNCTIONS =====
        function applySorting() {
            const isAsc = sortDirection === 'asc' ? 1 : -1;
            
            historyData.sort((a, b) => {
                let valA, valB;
                
                switch(sortColumn) {
                    case 'assign_by':
                        valA = (a.creator_name || 'Admin').toLowerCase();
                        valB = (b.creator_name || 'Admin').toLowerCase();
                        break;
                    case 'date':
                        valA = new Date(a.target_date || a.request_created_at || a.created_at || 0).getTime();
                        valB = new Date(b.target_date || b.request_created_at || b.created_at || 0).getTime();
                        break;
                    case 'route':
                        valA = ((a.origin_name || '') + ' ' + (a.destination_name || '')).toLowerCase();
                        valB = ((b.origin_name || '') + ' ' + (b.destination_name || '')).toLowerCase();
                        break;
                    case 'sj':
                        valA = (a.surat_jalan || '').toLowerCase();
                        valB = (b.surat_jalan || '').toLowerCase();
                        break;
                    case 'receiver':
                        valA = (a.receiver_name || '').toLowerCase();
                        valB = (b.receiver_name || '').toLowerCase();
                        break;
                    case 'type':
                        const typeA = a.is_request ? 'req pickup' : (a.task_type || '');
                        const typeB = b.is_request ? 'req pickup' : (b.task_type || '');
                        valA = typeA.toLowerCase();
                        valB = typeB.toLowerCase();
                        break;
                    case 'status':
                        valA = (a.status || '').toLowerCase();
                        valB = (b.status || '').toLowerCase();
                        break;
                    case 'notes':
                        valA = (a.driver_notes || a.late_reason || '').toLowerCase();
                        valB = (b.driver_notes || b.late_reason || '').toLowerCase();
                        break;
                    default:
                        valA = 0;
                        valB = 0;
                }
                
                if (valA < valB) return -1 * isAsc;
                if (valA > valB) return 1 * isAsc;
                
                // Fallback secondary sort by creation date (newest first)
                const dateA = new Date(a.request_created_at || a.created_at || 0).getTime();
                const dateB = new Date(b.request_created_at || b.created_at || 0).getTime();
                return dateB - dateA;
            });
        }

        function handleSort(column) {
            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = column;
                sortDirection = (column === 'date') ? 'desc' : 'asc';
            }
            applySorting();
            currentPage = 1;
            renderHistoryTable();
        }

        function updateSortIndicators() {
            const columns = ['assign_by', 'date', 'route', 'sj', 'receiver', 'type', 'status', 'notes'];
            columns.forEach(col => {
                const indicator = document.getElementById(`sort-${col}`);
                if (!indicator) return;
                const th = indicator.closest('th');
                if (sortColumn === col) {
                    indicator.innerText = sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward';
                    if (th) th.classList.add('active-sort');
                } else {
                    indicator.innerText = 'swap_vert';
                    if (th) th.classList.remove('active-sort');
                }
            });
        }

        // ===== SUBMIT ADD TASK =====
        document.getElementById('taskForm').onsubmit = async (e) => {
            e.preventDefault();
            const msg = document.getElementById('formMsg');

            // Validation for custom searchable selects
            if (!document.getElementById('originSelect').value || !document.getElementById('destSelect').value) {
                msg.innerHTML = '<span class="error-msg"><span class="material-symbols-outlined" style="font-size:18px;">warning</span> Silakan pilih lokasi asal dan tujuan.</span>';
                return;
            }

            // Manual validation for incremental files
            if (selectedFiles['addPreview'].length === 0 || selectedFiles['addGoodsPreview'].length === 0) {
                msg.innerHTML = '<span class="error-msg"><span class="material-symbols-outlined" style="font-size:18px;">warning</span> Silakan upload Foto Surat Jalan & Foto Barang.</span>';
                return;
            }

            msg.innerHTML = '<span style="color:var(--text-muted);"><span class="material-symbols-outlined rotating" style="font-size:18px; vertical-align:middle;">autorenew</span> Memberi watermark foto...</span>';
            try {
                const sj = document.getElementById('taskFormSuratJalan').value;
                const origin = document.getElementById('origin_name').value;
                const dest = document.getElementById('dest_name').value;
                const admin = ADMIN_NAME;

                // Process addPreview (Foto Surat Jalan)
                const watermarkedAddPreview = [];
                for (const file of selectedFiles['addPreview']) {
                    const blob = await watermarkFile(file, {
                        surat_jalan: sj,
                        origin: origin,
                        destination: dest,
                        admin: admin
                    });
                    const webpName = file.name.substring(0, file.name.lastIndexOf('.')) + '.webp';
                    watermarkedAddPreview.push(new File([blob], webpName, { type: 'image/webp' }));
                }

                // Process addGoodsPreview (Foto Barang)
                const watermarkedAddGoodsPreview = [];
                for (const file of selectedFiles['addGoodsPreview']) {
                    const blob = await watermarkFile(file, {
                        surat_jalan: sj,
                        origin: origin,
                        destination: dest,
                        admin: admin
                    });
                    const webpName = file.name.substring(0, file.name.lastIndexOf('.')) + '.webp';
                    watermarkedAddGoodsPreview.push(new File([blob], webpName, { type: 'image/webp' }));
                }

                // Sync watermarked files back to the file inputs
                const dt1 = new DataTransfer();
                watermarkedAddPreview.forEach(file => dt1.items.add(file));
                document.getElementById('taskFormFile').files = dt1.files;

                const dt2 = new DataTransfer();
                watermarkedAddGoodsPreview.forEach(file => dt2.items.add(file));
                document.getElementById('taskFormGoodsFile').files = dt2.files;

                msg.innerHTML = '<span style="color:var(--text-muted);">Mengunggah data...</span>';

                const res = await fetch(`${API_URL}?action=assign_task`, { method: 'POST', body: new FormData(e.target) });
                const data = await res.json();
                if (data.success) {
                    msg.innerHTML = '<span class="success-msg"><span class="material-symbols-outlined" style="font-size:18px;">check_circle</span> Tugas berhasil diberikan!</span>';
                    // Clear stored files
                    selectedFiles['addPreview'] = [];
                    selectedFiles['addGoodsPreview'] = [];
                    loadHistory();
                    setTimeout(() => {
                        closeAdd();
                        msg.innerHTML = '';
                    }, 1500);
                } else {
                    msg.innerHTML = `<span class="error-msg"><span class="material-symbols-outlined" style="font-size:18px;">error</span> Gagal memberikan tugas: ${data.error || 'Terjadi kesalahan'}</span>`;
                }
            } catch (err) {
                console.error(err);
                msg.innerHTML = '<span class="error-msg"><span class="material-symbols-outlined" style="font-size:18px;">wifi_off</span> Error koneksi.</span>';
            }
        };

        // ===== SUBMIT EDIT TASK =====
        document.getElementById('editForm').onsubmit = async (e) => {
            e.preventDefault();
            const msg = document.getElementById('editFormMsg');

            // Validation for custom searchable selects
            if (!document.getElementById('editOriginSelect').value || !document.getElementById('editDestSelect').value) {
                msg.innerHTML = '<span class="error-msg"><span class="material-symbols-outlined" style="font-size:18px;">warning</span> Silakan pilih lokasi asal dan tujuan.</span>';
                return;
            }

            msg.innerHTML = '<span style="color:var(--text-muted);">Memproses gambar & menyimpan...</span>';
            try {
                // Gather watermark context
                const sj = document.getElementById('editSuratJalan').value || '-';
                const origin = document.getElementById('edit_origin_name').value || '-';
                const dest = document.getElementById('edit_dest_name').value || '-';
                const admin = '<?= $_SESSION["fullname"] ?? $_SESSION["username"] ?? "Admin" ?>';

                // Watermark & compress new SJ files
                const wmSj = [];
                for (const file of editSelectedFiles['editSjPrev']) {
                    const blob = await watermarkFile(file, { surat_jalan: sj, origin, destination: dest, admin });
                    const webpName = file.name.substring(0, file.name.lastIndexOf('.')) + '.webp';
                    wmSj.push(new File([blob], webpName, { type: 'image/webp' }));
                }
                // Watermark & compress new Goods files
                const wmGoods = [];
                for (const file of editSelectedFiles['editGoodsPrev']) {
                    const blob = await watermarkFile(file, { surat_jalan: sj, origin, destination: dest, admin });
                    const webpName = file.name.substring(0, file.name.lastIndexOf('.')) + '.webp';
                    wmGoods.push(new File([blob], webpName, { type: 'image/webp' }));
                }
                // Watermark & compress new Proof files
                const wmProof = [];
                for (const file of editSelectedFiles['editProofPrev']) {
                    const blob = await watermarkFile(file, { surat_jalan: sj, origin, destination: dest, admin });
                    const webpName = file.name.substring(0, file.name.lastIndexOf('.')) + '.webp';
                    wmProof.push(new File([blob], webpName, { type: 'image/webp' }));
                }

                // Sync watermarked files back to file inputs using DataTransfer
                const dtSj = new DataTransfer();
                wmSj.forEach(f => dtSj.items.add(f));
                document.getElementById('editSjInput').files = dtSj.files;

                const dtGoods = new DataTransfer();
                wmGoods.forEach(f => dtGoods.items.add(f));
                document.getElementById('editGoodsInput').files = dtGoods.files;

                const dtProof = new DataTransfer();
                wmProof.forEach(f => dtProof.items.add(f));
                document.getElementById('editProofInput').files = dtProof.files;

                msg.innerHTML = '<span style="color:var(--text-muted);">Mengunggah data...</span>';

                const res = await fetch(`${API_URL}?action=edit_delivery`, { method: 'POST', body: new FormData(e.target) });
                const data = await res.json();
                if (data.success) {
                    msg.innerHTML = '<span class="success-msg"><span class="material-symbols-outlined" style="font-size:18px;">check_circle</span> Perubahan disimpan!</span>';
                    editSelectedFiles = { 'editSjPrev': [], 'editGoodsPrev': [], 'editProofPrev': [] };
                    loadHistory();
                    setTimeout(() => closeEdit(), 1200);
                } else {
                    msg.innerHTML = `<span class="error-msg"><span class="material-symbols-outlined" style="font-size:18px;">error</span> Gagal: ${data.error || 'Terjadi kesalahan'}</span>`;
                }
            } catch (err) {
                console.error('Edit submit error:', err);
                msg.innerHTML = '<span class="error-msg"><span class="material-symbols-outlined" style="font-size:18px;">wifi_off</span> Error koneksi.</span>';
            }
        };

        // ===== DELETE =====
        async function deleteDelivery(id) {
            if (!confirm('Hapus penugasan ini? Tindakan ini tidak bisa dibatalkan.')) return;
            const res = await fetch(`${API_URL}?action=delete_delivery`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            });
            const data = await res.json();
            if (data.success) loadHistory();
        }

        // ===== EXPORT TO EXCEL =====
        function exportToExcel() {
            if (!historyData.length) { alert('Tidak ada data untuk diekspor.'); return; }

            const dateF = document.getElementById('date_from').value || 'all';
            const dateT = document.getElementById('date_to').value || 'all';

            const rows = historyData.map((del, i) => {
                const dt = del.created_at ? new Date(del.created_at) : null;
                const dateStr = dt ? dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                const timeStr = dt ? dt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '';

                let targetDateStr = '-';
                if (del.target_date) {
                    const [y, m, d] = del.target_date.split('-');
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    targetDateStr = `${d} ${months[parseInt(m) - 1]} ${y}`;
                }

                let sts = del.status === 'pending' ? 'Menunggu' : (del.status === 'in_transit' ? 'Dalam Perjalanan' : 'Selesai');
                let tp = del.task_type === 'antar' ? 'Delivery' : 'Pickup';

                const endDt = del.end_time ? new Date(del.end_time) : null;
                const endDateStr = endDt ? endDt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                const endTimeStr = endDt ? endDt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '';

                return {
                    'No': i + 1,
                    'Tipe': tp,
                    'Waktu Dibuat': `${dateStr} ${timeStr}`,
                    'Tanggal Jadwal': targetDateStr,
                    'Driver': del.driver_name,
                    'Kendaraan': del.vehicle_plate || '-',
                    'No Surat Jalan': del.surat_jalan || '-',
                    'Total Koli': del.total_koli || 0,
                    'Asal': del.origin_name,
                    'Tujuan': del.destination_name,
                    'Penerima': del.receiver_name || '-',
                    'Waktu Selesai': endDt ? `${endDateStr} ${endTimeStr}` : '-',
                    'Keterangan Driver': del.driver_notes || '-',
                    'Status': sts
                };
            });

            const ws = XLSX.utils.json_to_sheet(rows);
            ws['!cols'] = [{ wch: 5 }, { wch: 10 }, { wch: 18 }, { wch: 16 }, { wch: 20 }, { wch: 12 }, { wch: 24 }, { wch: 24 }, { wch: 18 }];
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Riwayat Penugasan');
            XLSX.writeFile(wb, `riwayat_penugasan_${dateF}_sd_${dateT}.xlsx`);
        }

        // INIT Default Date
        (function setDefaultDates() {
            const df = document.getElementById('date_from');
            const dt = document.getElementById('date_to');
            if (df && !df.value) df.value = todayStr();
            if (dt && !dt.value) dt.value = todayStr();
        })();

        function showDriverSelect(id) {
            document.getElementById(`assign-container-${id}`).style.display = 'none';
            document.getElementById(`driver-select-container-${id}`).style.display = 'block';
        }

        function cancelQuickAssign(id) {
            document.getElementById(`assign-container-${id}`).style.display = 'block';
            document.getElementById(`driver-select-container-${id}`).style.display = 'none';
        }

        async function quickAssign(id, driverId) {
            if (!driverId) return;

            const req = historyData.find(x => x.id === id && x.is_request);
            if (!req) return;

            if (!confirm(`Tugaskan driver untuk Surat Jalan ${req.surat_jalan}?`)) {
                document.querySelector(`#driver-select-container-${id} select`).value = '';
                return;
            }

            const formData = new FormData();
            formData.append('driver_id', driverId);
            formData.append('pickup_id', id);
            formData.append('origin_name', req.origin_name);
            formData.append('dest_name', req.destination_name);
            formData.append('dest_lat', 0); // Default or try to find in locationData
            formData.append('dest_lng', 0);
            formData.append('task_type', 'kirim');
            formData.append('surat_jalan', req.surat_jalan);
            formData.append('total_koli', req.total_koli);
            formData.append('notes', req.notes);
            formData.append('target_date', req.scheduled_date ? req.scheduled_date.replace(' ', 'T') : todayDateTimeStr());

            // Try to find lat/lng from locationData
            if (locationData) {
                const loc = locationData.find(l => l.name === req.destination_name);
                if (loc) {
                    formData.append('dest_id', loc.id);
                    formData.append('dest_lat', loc.lat);
                    formData.append('dest_lng', loc.lng);
                }
            }

            try {
                const res = await fetch(`${API_URL}?action=assign_task`, { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    loadHistory();
                    showToast('Tugas berhasil diperbarui');
                    if (window.updateSidebarBadge) updateSidebarBadge();
                } else {
                    showToast('Gagal: ' + (data.error || 'Terjadi kesalahan'), 'error');
                }
            } catch (err) {
                showToast('Error koneksi', 'error');
            }
        }

        function assignFromRequest(req) {
            openAddModal();
            // Pre-fill fields
            document.getElementById('taskType').value = 'kirim'; // Pickup
            document.getElementById('taskFormSuratJalan').value = req.surat_jalan || '';
            document.getElementById('taskFormKoli').value = req.total_koli || 1;
            document.getElementById('taskFormNotes').value = req.notes || '';
            if (req.scheduled_date) {
                let val = req.scheduled_date.replace(' ', 'T');
                if (val.length === 10) val += 'T00:00'; // Append midnight if date only
                document.getElementById('taskFormTargetDate').value = val.slice(0, 16);
            }

            // Try to match origin and destination in selects
            const originSel = document.getElementById('originSelect');
            const destSel = document.getElementById('destSelect');

            // Match by name if ID is not available in pickup_request (since it stores names)
            for (let i = 0; i < originSel.options.length; i++) {
                if (originSel.options[i].getAttribute('data-name') === req.origin_name) {
                    originSel.selectedIndex = i;
                    updateOriginName(originSel);
                    break;
                }
            }

            for (let i = 0; i < destSel.options.length; i++) {
                if (destSel.options[i].getAttribute('data-name') === req.destination_name) {
                    destSel.selectedIndex = i;
                    updateDestDetails(destSel);
                    break;
                }
            }

            // Store reference to pickup request ID so we can update its status after assigning
            const form = document.getElementById('taskForm');
            let hiddenReqId = document.getElementById('link_pickup_id');
            if (!hiddenReqId) {
                hiddenReqId = document.createElement('input');
                hiddenReqId.type = 'hidden';
                hiddenReqId.name = 'pickup_id';
                hiddenReqId.id = 'link_pickup_id';
                form.appendChild(hiddenReqId);
            }
            hiddenReqId.value = req.id;
        }

        // ===== QUICK EDIT LOGIC =====
        async function openQuickEdit(id, field) {
            const modal = document.getElementById('quickEditModal');
            const content = document.getElementById('qe_content');
            const title = document.getElementById('quickEditTitle');
            const icon = document.getElementById('qeIcon');
            const qeId = document.getElementById('qe_id');
            const qeField = document.getElementById('qe_field');
            const msg = document.getElementById('qeMsg');

            msg.innerHTML = '';
            qeId.value = id;
            qeField.value = field;

            content.innerHTML = '<div style="text-align:center; padding:1rem;"><span class="material-symbols-outlined rotating">autorenew</span> Memuat...</div>';
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);

            try {
                const res = await fetch(`${API_URL}?action=get_delivery&id=${id}`);
                const d = await res.json();

                if (field !== 'reassign' && d.status !== 'pending') {
                    showToast('Hanya tugas dengan status PENDING yang dapat di-edit.', 'error');
                    closeQuickEdit();
                    return;
                }

                if (field === 'driver') {
                    title.innerText = 'Edit Driver & Jadwal';
                    icon.innerText = 'person_edit';
                    content.innerHTML = `
                        <div class="form-group">
                            <label style="font-size:0.75rem; font-weight:700; color:var(--text-sub); text-transform:uppercase; margin-bottom:0.5rem; display:block;">Pilih Driver</label>
                            <select name="driver_id" id="qe_driver_select" style="width:100%; padding:0.75rem; border:1.5px solid var(--border); border-radius:0.75rem;">
                                ${allDrivers.map(drv => `<option value="${drv.user_id}" ${drv.user_id == d.driver_id ? 'selected' : ''}>${drv.driver_name}</option>`).join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label style="font-size:0.75rem; font-weight:700; color:var(--text-sub); text-transform:uppercase; margin-bottom:0.5rem; display:block;">Tanggal & Jam</label>
                            <input type="datetime-local" name="target_date" value="${d.target_date ? d.target_date.replace(' ', 'T').slice(0, 16) : todayDateTimeStr()}" style="width:100%; padding:0.75rem; border:1.5px solid var(--border); border-radius:0.75rem;">
                        </div>
                    `;
                } else if (field === 'route') {
                    title.innerText = 'Edit Rute Asal & Tujuan';
                    icon.innerText = 'route';
                    content.innerHTML = `
                        <div class="form-group searchable-group">
                            <label style="font-size:0.75rem; font-weight:700; color:var(--text-sub); text-transform:uppercase; margin-bottom:0.5rem; display:block;">Lokasi Asal</label>
                            <input type="text" class="searchable-input" id="qe_origin_input" value="${d.origin_name}" placeholder="Cari asal..." readonly onclick="toggleOptions('qe_origin_list')">
                            <div id="qe_origin_list" class="options-list"></div>
                            <input type="hidden" name="origin_id" id="qe_origin_id" value="${d.origin_id}">
                            <input type="hidden" name="origin_name" id="qe_origin_name" value="${d.origin_name}">
                        </div>
                        <div class="form-group searchable-group">
                            <label style="font-size:0.75rem; font-weight:700; color:var(--text-sub); text-transform:uppercase; margin-bottom:0.5rem; display:block;">Lokasi Tujuan</label>
                            <input type="text" class="searchable-input" id="qe_dest_input" value="${d.destination_name}" placeholder="Cari tujuan..." readonly onclick="toggleOptions('qe_dest_list')">
                            <div id="qe_dest_list" class="options-list"></div>
                            <input type="hidden" name="dest_id" id="qe_dest_id" value="${d.destination_id}">
                            <input type="hidden" name="dest_name" id="qe_dest_name" value="${d.destination_name}">
                            <input type="hidden" name="dest_lat" id="qe_dest_lat" value="${d.destination_lat}">
                            <input type="hidden" name="dest_lng" id="qe_dest_lng" value="${d.destination_lng}">
                        </div>
                    `;
                    // Render location options
                    renderLocationOptions('qe_origin_list', 'qe_origin_id', 'qe_origin_name', false);
                    renderLocationOptions('qe_dest_list', 'qe_dest_id', 'qe_dest_name', true);
                } else if (field === 'sj') {
                    title.innerText = 'Edit SJ & Koli';
                    icon.innerText = 'description';
                    content.innerHTML = `
                        <div class="form-group">
                            <label style="font-size:0.75rem; font-weight:700; color:var(--text-sub); text-transform:uppercase; margin-bottom:0.5rem; display:block;">No Surat Jalan</label>
                            <input type="text" name="surat_jalan" value="${d.surat_jalan || ''}" required oninput="this.value=this.value.toUpperCase()" style="text-transform:uppercase; width:100%; padding:0.75rem; border:1.5px solid var(--border); border-radius:0.75rem;">
                        </div>
                        <div class="form-group">
                            <label style="font-size:0.75rem; font-weight:700; color:var(--text-sub); text-transform:uppercase; margin-bottom:0.5rem; display:block;">Total Koli</label>
                            <input type="number" name="total_koli" value="${d.total_koli || 0}" min="0" required style="width:100%; padding:0.75rem; border:1.5px solid var(--border); border-radius:0.75rem;">
                        </div>
                    `;
                } else if (field === 'reassign') {
                    title.innerText = 'Reassign Tugas';
                    icon.innerText = 'person_add';
                    content.innerHTML = `
                        <input type="hidden" name="status" value="pending">
                        <div class="form-group">
                            <label style="font-size:0.75rem; font-weight:700; color:var(--text-sub); text-transform:uppercase; margin-bottom:0.5rem; display:block;">Pilih Driver Baru</label>
                            <select name="driver_id" id="qe_driver_select" style="width:100%; padding:0.75rem; border:1.5px solid var(--border); border-radius:0.75rem;">
                                ${allDrivers.map(drv => `<option value="${drv.user_id}" ${drv.user_id == d.driver_id ? 'selected' : ''}>${drv.driver_name}</option>`).join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label style="font-size:0.75rem; font-weight:700; color:var(--text-sub); text-transform:uppercase; margin-bottom:0.5rem; display:block;">Tanggal & Jam</label>
                            <input type="datetime-local" name="target_date" value="${d.target_date ? d.target_date.replace(' ', 'T').slice(0, 16) : todayDateTimeStr()}" style="width:100%; padding:0.75rem; border:1.5px solid var(--border); border-radius:0.75rem;">
                        </div>
                    `;
                }
            } catch (e) {
                content.innerHTML = '<div style="color:var(--danger); padding:1rem;">Gagal memuat data.</div>';
            }
        }

        function closeQuickEdit() {
            const modal = document.getElementById('quickEditModal');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = 'none', 200);
        }

        document.getElementById('quickEditForm').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('qe_submit_btn');
            const msg = document.getElementById('qeMsg');

            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined rotating">autorenew</span>';
            msg.innerHTML = '';

            try {
                const res = await fetch(`${API_URL}?action=quick_edit_delivery`, {
                    method: 'POST',
                    body: new FormData(e.target)
                });
                const data = await res.json();
                if (data.success) {
                    msg.innerHTML = '<span class="success-msg">Berhasil disimpan!</span>';
                    loadHistory();
                    setTimeout(closeQuickEdit, 1000);
                } else {
                    msg.innerHTML = `<span class="error-msg">${data.error || 'Gagal menyimpan.'}</span>`;
                }
            } catch (err) {
                msg.innerHTML = '<span class="error-msg">Error koneksi.</span>';
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined">save</span> Simpan';
            }
        };

        function changePage(delta) {
            currentPage += delta;
            renderHistoryTable();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function changeRowsPerPage() {
            rowsPerPage = parseInt(document.getElementById('rowsPerPage').value);
            currentPage = 1;
            renderHistoryTable();
        }

        loadFormData();
        (function checkUrlParams() {
            const params = new URLSearchParams(window.location.search);
            const search = params.get('search');
            if (search) {
                const searchInp = document.getElementById('searchInput');
                const dateF = document.getElementById('date_from');
                const dateT = document.getElementById('date_to');
                if (searchInp) searchInp.value = search;
                if (dateF) dateF.value = '';
                if (dateT) dateT.value = '';
            }
        })();
        loadHistory();
    </script>

    <!-- ===== ALL MODALS MOVED HERE FOR STACKING CONTEXT SAFETY ===== -->

    <!-- DETAIL MODAL -->
    <div id="detailModal" class="modal-overlay" onclick="if(event.target===this)closeDetail()"
        style="display: none; z-index: 9000;">
        <div class="modal-box" style="max-width:600px;">
            <div
                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; position: sticky; top: -1.5rem; background: var(--surface); z-index: 10; padding-bottom: 1rem; border-bottom: 1px solid var(--border); margin-left: -1.5rem; margin-right: -1.5rem; padding-left: 1.5rem; padding-right: 1.5rem; margin-top: -0.5rem;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="material-symbols-outlined"
                        style="color:var(--primary); font-size:28px;">monitoring</span>
                    <h2 style="font-size:1.25rem; font-weight:800; margin:0;">Detail Penugasan</h2>
                </div>
                <button class="modal-close" onclick="closeDetail()" style="position:static;">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div id="detailContent"></div>
            <div id="detailMap"></div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-top:1rem;">
                <div class="time-box">
                    <span class="time-label">Waktu Mulai</span>
                    <span id="detailStartTime" class="time-value">-</span>
                </div>
                <div class="time-box">
                    <span class="time-label">Waktu Selesai</span>
                    <span id="detailEndTime" class="time-value">-</span>
                </div>
            </div>
            <button type="button" onclick="closeDetail()" class="btn btn-ghost"
                style="width:100%; justify-content:center; margin-top:1.5rem;">Tutup</button>
        </div>
    </div>

    <div id="imagePopup" class="modal-overlay" onclick="if(event.target===this)closeImagePopup()"
        style="display: none; z-index: 100000;">
        <div
            style="position:relative; max-width:90%; max-height:90%; display:flex; flex-direction:column; align-items:center;">
            <div style="position:absolute; top:-45px; right:0; display:flex; gap:10px;">
                <a id="downloadImageBtn" href="#" download class="btn btn-primary btn-sm"
                    style="height:32px; padding:0 12px; border-radius:6px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                    <span class="material-symbols-outlined" style="font-size:20px;">download</span>
                    Download
                </a>
                <button onclick="closeImagePopup()"
                    style="background:rgba(255,255,255,0.1); border:none; color:white; cursor:pointer; width:32px; height:32px; border-radius:6px; display:flex; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
                    <span class="material-symbols-outlined" style="font-size:24px;">close</span>
                </button>
            </div>
            <img id="popupImg" src=""
                style="max-width:100%; max-height:85vh; border-radius:12px; object-fit:contain; box-shadow: 0 20px 50px rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.1);">
        </div>
    </div>

    <canvas id="watermarkCanvas" style="display:none;"></canvas>
    <div id="toastContainer"></div>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
</body>

</html>