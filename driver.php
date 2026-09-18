<?php
require_once 'auth_check.php';
checkLogin();
checkAccess('tasks');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver App | TMS</title>
    <link rel="icon" type="image/png" href="favicon.png?v=3">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0" />
    <style>
        :root {
            --primary: #007AFF;
            --primary-dark: #0056b3;
            --primary-light: rgba(0, 122, 255, 0.08);
            --primary-glow: rgba(0, 122, 255, 0.15);
            --success: #34C759;
            --warning: #FF9500;
            --danger: #FF3B30;
            --bg: #F2F2F7;
            --card: #FFFFFF;
            --text: #1C1C1E;
            --text-sub: #8E8E93;
            --border: rgba(0, 0, 0, 0.06);
            --ios-shadow: 0 4px 24px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        html {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        ::-webkit-scrollbar {
            display: none;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", sans-serif;
            background-color: #f1f3f9;
            background-image:
                linear-gradient(rgba(99, 102, 241, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.04) 1px, transparent 1px),
                radial-gradient(at 0% 0%, rgba(143, 0, 255, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(0, 122, 255, 0.15) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(255, 45, 85, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(255, 149, 0, 0.1) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(52, 199, 89, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
            background-size: 30px 30px, 30px 30px, cover, cover, cover, cover, cover;
            margin: 0;
            padding: 0;
            color: var(--text);
            min-height: 100vh;
            letter-spacing: -0.015em;
        }

        .app-container {
            max-width: 500px;
            margin: 0 auto;
            background: transparent;
            min-height: 100vh;
            position: relative;
            padding-bottom: 110px;
            /* space for floating bottom tab bar */
        }

        .app-header {
            background: transparent;
            color: var(--text);
            padding: 2.5rem 1.25rem 1rem;
            position: relative;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .header-top h1 {
            font-size: 1.25rem;
            font-weight: 750;
            margin: 0;
            letter-spacing: -0.02em;
        }

        .logout-btn,
        .profile-hdr-btn {
            background: rgba(255, 255, 255, 0.65) !important;
            border: 1px solid rgba(255, 255, 255, 0.5) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text) !important;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .logout-btn span,
        .profile-hdr-btn span {
            color: var(--text) !important;
        }

        .logout-btn:active,
        .profile-hdr-btn:active {
            transform: scale(0.92);
            background: rgba(255, 255, 255, 0.8) !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .metric-box {
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 0.8rem 0.5rem;
            border-radius: 1.1rem;
            text-align: center;
            flex: 1;
            margin: 0 4px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .metric-box:active {
            transform: scale(0.95);
        }

        .metric-val {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.03em;
        }

        .metric-lbl {
            font-size: 0.625rem;
            color: var(--text-sub);
            text-transform: uppercase;
            margin-top: 4px;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .gps-alert-box {
            background: #ffebeb;
            margin: -1.75rem 1.5rem 1.5rem;
            padding: 1rem;
            border-radius: 1.25rem;
            display: none;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 10px 25px rgba(255, 59, 48, 0.12);
            border: 1px solid rgba(255, 59, 48, 0.1);
            animation: shake 0.5s cubic-bezier(.36, .07, .19, .97) both;
        }

        @keyframes shake {

            10%,
            90% {
                transform: translate3d(-1px, 0, 0);
            }

            20%,
            80% {
                transform: translate3d(2px, 0, 0);
            }

            30%,
            50%,
            70% {
                transform: translate3d(-4px, 0, 0);
            }

            40%,
            60% {
                transform: translate3d(4px, 0, 0);
            }
        }

        .gps-alert-icon {
            width: 44px;
            height: 44px;
            background: rgba(255, 59, 48, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--danger);
        }

        .pulse-container {
            position: relative;
            width: 44px;
            height: 44px;
            background: rgba(52, 199, 89, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--success);
        }

        .pulse {
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse-anim 1.5s infinite;
        }

        @keyframes pulse-anim {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            100% {
                transform: scale(3);
                opacity: 0;
            }
        }

        .bottom-nav {
            position: fixed;
            bottom: 24px;
            left: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            display: flex;
            justify-content: space-around;
            padding: 0.6rem 0.4rem;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
            z-index: 1000;
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 28px;
            max-width: 460px;
            margin: 0 auto;
        }

        .nav-item {
            flex: 1;
            border: none;
            background: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            color: #8E8E93;
            font-size: 0.72rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .nav-item.active {
            color: var(--primary);
        }

        .nav-pill {
            width: 44px;
            height: 28px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            margin-bottom: 2px;
            position: relative;
        }

        .nav-item.active .nav-pill {
            background: rgba(0, 122, 255, 0.1);
        }

        .nav-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 15px;
            height: 15px;
            padding: 0 3px;
            background: var(--danger);
            color: white;
            font-size: 9px;
            font-weight: 600;
            display: none;
            align-items: center;
            justify-content: center;
            border-radius: 7.5px;
            border: 1.5px solid white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 10;
            pointer-events: none;
            line-height: 1;
        }

        .nav-badge span {
            display: inline-block;
            line-height: 1;
            margin-top: -0.5px;
        }

        .nav-item .material-symbols-outlined {
            font-size: 22px;
        }

        .task-container {
            padding: 0.75rem 1.25rem 6.5rem;
        }

        /* === Accordion Task Card === */
        .task-card {
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 1.25rem;
            margin-bottom: 0.875rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
        }

        .task-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background: var(--card-accent, var(--primary));
            opacity: 1;
            transition: width 0.2s ease;
        }

        .task-card.expanded::before {
            width: 7px;
        }

        .task-card.expanded {
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.06);
            border-color: rgba(0, 122, 255, 0.25);
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.82);
        }

        .task-card:active {
            transform: scale(0.985);
        }

        .task-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem 1.25rem;
            cursor: pointer;
            user-select: none;
            -webkit-user-select: none;
            gap: 0.75rem;
        }

        .task-card-header:active {
            background: rgba(0, 0, 0, 0.01);
        }

        .task-header-left {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            flex: 1;
            min-width: 0;
        }

        .task-header-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.2s;
        }

        .task-header-icon.pending {
            background: rgba(255, 149, 0, 0.1);
            color: var(--warning);
        }

        .task-header-icon.transit {
            background: rgba(52, 199, 89, 0.1);
            color: var(--success);
        }

        .task-header-icon.completed {
            background: rgba(0, 122, 255, 0.1);
            color: var(--primary);
        }

        .task-header-meta {
            display: flex;
            flex-direction: column;
            gap: 3px;
            flex: 1;
            min-width: 0;
        }

        .task-header-meta .order-num {
            font-size: 0.7rem;
            color: var(--text-sub);
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .dest-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1c1c1e;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }

        .task-header-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .status-badge {
            padding: 0.3rem 0.6rem;
            border-radius: 100px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            white-space: nowrap;
            line-height: 1.2;
        }

        .badge-pending {
            background: rgba(255, 149, 0, 0.12);
            color: #c97500;
        }

        .badge-transit {
            background: rgba(52, 199, 89, 0.12);
            color: #248a3e;
        }

        .badge-completed {
            background: rgba(0, 122, 255, 0.12);
            color: #0056b3;
        }

        .badge-danger {
            background: rgba(255, 59, 48, 0.12);
            color: #c9221b;
        }

        .accordion-arrow {
            font-size: 20px;
            color: #c7c7cc;
            transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .task-card.expanded .accordion-arrow {
            transform: rotate(180deg);
        }

        /* Accordion Body */
        .task-card-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .task-card.expanded .task-card-body {
            max-height: 1500px;
        }

        .task-card-body-inner {
            padding: 0 1.25rem 1.25rem 1.25rem;
        }

        .task-divider {
            height: 1px;
            background: rgba(0, 0, 0, 0.04);
            margin-bottom: 1rem;
        }

        .task-row {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            margin-bottom: 0.85rem;
        }

        .task-row span.mat-icon {
            color: var(--primary);
            font-size: 20px;
            margin-top: 1px;
        }

        .task-row .info {
            font-size: 0.72rem;
            color: var(--text-sub);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .task-row .value {
            font-size: 0.925rem;
            font-weight: 600;
            color: #1c1c1e;
            line-height: 1.4;
            word-break: break-word;
        }

        .task-type-badge {
            font-size: 0.65rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .task-meta-row {
            display: flex;
            gap: 1rem;
            padding: 0.875rem 0;
            border-top: 1px solid rgba(0, 0, 0, 0.04);
            margin-bottom: 1rem;
        }

        .task-meta-item {
            flex: 1;
        }

        .task-meta-item .info {
            font-size: 0.72rem;
            color: var(--text-sub);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 2px;
        }

        .task-meta-item .value {
            font-size: 0.9rem;
            font-weight: 800;
            color: var(--text);
        }

        .btn-action {
            flex: 1;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: inherit;
            transition: all 0.2s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .btn-action:active {
            transform: scale(0.95);
        }

        .btn-checkin {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 16px rgba(0, 122, 255, 0.2);
        }

        .btn-checkin:active {
            background: var(--primary-dark);
        }

        .btn-nav {
            background: var(--primary-light);
            color: var(--primary);
            border: 1px solid rgba(0, 122, 255, 0.1);
        }

        .task-section-title {
            font-size: 1.125rem;
            font-weight: 700;
            margin: 1.5rem 0 1rem;
        }

        /* Float button animation */
        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-5px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .floating-status {
            animation: float 3s ease-in-out infinite;
        }

        /* Vehicle Modal Styling */
        #vehicleModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            z-index: 2000;
            justify-content: center;
            align-items: flex-end;
            /* slide up sheet on mobile */
        }

        @media (min-width: 500px) {
            #vehicleModal {
                align-items: center;
                padding: 2rem;
            }
        }

        .vehicle-modal-content {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-top-left-radius: 2rem;
            border-top-right-radius: 2rem;
            width: 100%;
            max-width: 500px;
            padding: 2.2rem 1.5rem 2.5rem;
            text-align: center;
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.08);
            position: relative;
            animation: slideUp 0.35s cubic-bezier(0.19, 1, 0.22, 1);
        }

        @media (min-width: 500px) {
            .vehicle-modal-content {
                border-radius: 2rem;
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
                max-width: 420px;
                padding: 2.5rem 2rem 2rem;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }

            to {
                transform: translateY(0);
            }
        }

        .modal-close-btn {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            background: #f1f5f9;
            border: none;
            color: #64748b;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.2s;
        }

        .modal-close-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .vehicle-option {
            padding: 1.1rem 1.25rem;
            border: 1px solid rgba(0, 0, 0, 0.04);
            border-radius: 1.2rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .vehicle-option:active {
            transform: scale(0.98);
            background: rgba(255, 255, 255, 0.8);
        }

        .vehicle-option:hover {
            border-color: var(--primary);
            background: rgba(0, 122, 255, 0.04);
        }

        .vehicle-option.selected {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        /* Clickable destination row */
        .task-row.nav-tap {
            cursor: pointer;
            border-radius: 0.75rem;
            padding: 0.5rem;
            margin: 0 -0.5rem 0.25rem;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .task-row.nav-tap:active {
            background: rgba(0, 122, 255, 0.06);
            transform: scale(0.985);
        }

        /* Horizontal Calendar Strip */
        .calendar-strip {
            display: flex;
            overflow-x: auto;
            gap: 0.5rem;
            padding: 1.25rem 1.25rem 0.5rem;
            margin: 0;
            scrollbar-width: none;
            scroll-behavior: smooth;
        }

        .calendar-strip::-webkit-scrollbar {
            display: none;
        }

        .cal-day-cell {
            flex: 0 0 auto;
            width: 52px;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.6rem 0;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            color: #8E8E93;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .cal-day-cell:active {
            transform: scale(0.92);
        }

        .cal-day-cell.active {
            background: var(--primary) !important;
            color: white !important;
            box-shadow: 0 8px 20px rgba(0, 122, 255, 0.3) !important;
            border-color: var(--primary) !important;
        }

        .cal-day-cell.active .cal-day-num {
            color: white !important;
        }

        .cal-day-name {
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .cal-day-num {
            font-size: 1.1rem;
            font-weight: 800;
            color: #1c1c1e;
        }

        .cal-dots {
            display: flex;
            gap: 3px;
            margin-top: 4px;
            height: 5px;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .cal-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
        }

        .cal-dot.pending {
            background: var(--danger);
        }

        .cal-dot.completed {
            background: var(--success);
        }

        .cal-day-cell.active .cal-dot.pending {
            background: white;
        }

        .cal-day-cell.active .cal-dot.completed {
            background: rgba(255, 255, 255, 0.6);
        }

        .cal-day-cell.has-task {
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(0, 122, 255, 0.2);
        }

        .cal-day-cell.has-task .cal-day-num {
            color: var(--primary);
        }

        /* Custom Confirm Modal & Toast */
        .confirm-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.25);
            z-index: 5000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 1.5rem;
            transition: opacity 0.3s ease;
        }

        .confirm-modal-box {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            padding: 1.85rem;
            border-radius: 24px;
            width: 100%;
            max-width: 340px;
            text-align: center;
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.5);
            animation: iosModalZoom 0.28s cubic-bezier(0.19, 1, 0.22, 1);
        }

        @keyframes iosModalZoom {
            from {
                transform: scale(0.85);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Form styling inside modals */
        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }

        .form-group label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #3a3a3c;
            display: block;
            margin-bottom: 0.4rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(0, 0, 0, 0.08);
            background: rgba(0, 0, 0, 0.02);
            border-radius: 12px;
            font-size: 0.925rem;
            color: #1c1c1e;
            font-family: inherit;
            outline: none;
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.15);
        }

        .toast {
            visibility: hidden;
            min-width: 250px;
            background-color: rgba(28, 28, 30, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #fff;
            text-align: center;
            border-radius: 9999px;
            padding: 0.75rem 1.5rem;
            position: fixed;
            z-index: 6000;
            left: 50%;
            bottom: 30px;
            transform: translateX(-50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            font-size: 0.875rem;
            font-weight: 600;
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.19, 1, 0.22, 1);
        }

        .toast.show {
            visibility: visible;
            opacity: 1;
            bottom: 100px;
        }

        .toast.success {
            background-color: rgba(52, 199, 89, 0.95);
        }

        .toast.warning {
            background-color: rgba(255, 149, 0, 0.95);
        }

        .toast.danger {
            background-color: rgba(255, 59, 48, 0.95);
        }

        .active-vehicle-card {
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 1.25rem;
            padding: 1rem 1.25rem;
            margin: 1rem 0;
            display: none;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
        }

        .av-icon {
            background: rgba(0, 122, 255, 0.15);
            color: #30a2ff;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .av-details {
            flex: 1;
            min-width: 0;
        }

        .av-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-sub);
            margin-bottom: 2px;
        }

        .av-name {
            font-weight: 800;
            font-size: 0.95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
            color: var(--text);
        }

        .av-plate {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-sub);
        }

        .release-btn {
            background: rgba(255, 255, 255, 0.5);
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.4);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .release-btn:active {
            background: rgba(255, 255, 255, 0.8);
            transform: scale(0.92);
        }

        .active-vehicle-card.empty {
            background: rgba(255, 255, 255, 0.45);
            border: 1px dashed rgba(255, 255, 255, 0.4);
            box-shadow: none;
        }

        .active-vehicle-card.empty .av-icon {
            background: rgba(0, 0, 0, 0.03);
            color: rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body>
    <div class="app-container">
        <div class="app-header">
            <div class="header-top">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <a href="mobile_home.php" class="profile-hdr-btn" style="text-decoration:none;">
                        <span class="material-symbols-outlined" style="font-size:20px;">arrow_back</span>
                    </a>
                    <h1 style="font-size:1.1rem;">Halo, <?php echo explode(' ', trim($_SESSION['name']))[0]; ?>! 👋</h1>
                </div>
                <div style="display:flex; gap:0.5rem; align-items:center;">
                    <button class="profile-hdr-btn" onclick="openProfileModal()" title="Profil Saya">
                        <span class="material-symbols-outlined">account_circle</span>
                    </button>
                    <a href="logout.php" class="logout-btn">
                        <span class="material-symbols-outlined">logout</span>
                    </a>
                </div>
            </div>

            <div id="activeVehicleCard" class="active-vehicle-card" style="display: flex;">
                <div class="av-icon" id="vStatusIcon">
                    <span class="material-symbols-outlined">local_shipping</span>
                </div>
                <div class="av-details">
                    <div class="av-label" id="vStatusLabel">Kendaraan Aktif</div>
                    <div id="avName" class="av-name">-</div>
                    <div id="avPlate" class="av-plate">-</div>
                </div>
                <button id="vActionBtn" onclick="handleVehicleAction()" class="release-btn"
                    title="Pilih / Lepas Kendaraan">
                    <span class="material-symbols-outlined" id="vActionIcon" style="font-size: 22px;">warehouse</span>
                </button>
            </div>

            <div class="metrics-container" style="display:flex; justify-content:space-between; margin-top:0.5rem;">
                <div class="metric-box">
                    <div id="countPending" class="metric-val">0</div>
                    <div class="metric-lbl">Tugas Baru</div>
                </div>
                <div class="metric-box">
                    <div id="countInTransit" class="metric-val">0</div>
                    <div class="metric-lbl">Pengiriman</div>
                </div>
                <div class="metric-box">
                    <div id="countCompleted" class="metric-val">0</div>
                    <div class="metric-lbl">Selesai</div>
                </div>
            </div>
        </div>

        <div class="gps-alert-box" id="gpsAlertBox">
            <div class="gps-alert-icon">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">warning</span>
            </div>
            <div>
                <div style="font-weight: 800; font-size: 0.95rem; color: #991b1b;">GPS TERPUTUS!</div>
                <div style="font-size: 0.75rem; color: #ef4444; font-weight: 600;">Aktifkan GPS & Izin Lokasi untuk
                    tetap bertugas</div>
            </div>
        </div>

        <!-- Horizontal Calendar Strip -->
        <div id="calendarStrip" class="calendar-strip"></div>

        <!-- Late Reason Modal -->
        <div id="lateReasonModal" class="confirm-modal-overlay" style="z-index: 5500;">
            <div class="confirm-modal-box">
                <div
                    style="background:#fefce8; color:#eab308; width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem;">
                    <span class="material-symbols-outlined" style="font-size:32px;">history_toggle_off</span>
                </div>
                <h3 style="font-size:1.2rem; font-weight:800; color:#1e293b; margin-bottom:0.5rem;">Penyelesaian
                    Tertunda</h3>
                <p style="color:#64748b; font-size:0.85rem; margin-bottom:1rem; line-height:1.4;">Tugas ini dari tanggal
                    lampau. Mohon tulis alasan mengapa baru diselesaikan sekarang.</p>
                <textarea id="lateReasonInput" rows="3" placeholder="Contoh: Lupa tekan selesai, hapenya lowbet..."
                    style="box-sizing:border-box; width:100%; border:1px solid #cbd5e1; border-radius:0.75rem; padding:0.75rem; font-family:inherit; font-size:0.9rem; margin-bottom:1.25rem; resize:none;"
                    required></textarea>
                <div style="display:flex; gap:0.75rem;">
                    <button onclick="closeLateReasonModal()"
                        style="flex:1; padding:0.875rem; border:none; border-radius:14px; background:rgba(0,0,0,0.05); color:#3a3a3c; font-weight:700; cursor:pointer; transition:0.2s;">Batal</button>
                    <button id="lateReasonSubmitBtn"
                        style="flex:1; padding:0.875rem; border:none; border-radius:14px; background:var(--warning); color:white; font-weight:700; cursor:pointer; transition:0.2s; box-shadow:0 4px 12px rgba(255,149,0,0.2);">Kirim</button>
                </div>
            </div>
        </div>

        <main id="taskList" class="task-container">
            <div style="text-align:center; padding: 2rem;">Memuat...</div>
        </main>

        <footer
            style="text-align: center; margin: 1.5rem 0 2rem; font-size: 0.65rem; color: var(--text-sub); font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.6;">
            Powered By Dhanielo-Marthinz | IMS @ 2026
        </footer>

        <div id="activeTasks" style="display:none;"></div>
        <div id="completedTasks" style="display:none;"></div>

        <div class="bottom-nav">
            <button class="nav-item active" onclick="switchSection('pending', this)">
                <div class="nav-pill">
                    <span class="material-symbols-outlined">pending_actions</span>
                    <span id="badgePending" class="nav-badge">0</span>
                </div>
                Tugas Baru
            </button>
            <button class="nav-item" onclick="switchSection('in_transit', this)">
                <div class="nav-pill">
                    <span class="material-symbols-outlined">local_shipping</span>
                    <span id="badgeTransit" class="nav-badge">0</span>
                </div>
                Perjalanan
            </button>
            <button class="nav-item" onclick="switchSection('completed', this)">
                <div class="nav-pill">
                    <span class="material-symbols-outlined">history</span>
                    <span id="badgeCompleted" class="nav-badge">0</span>
                </div>
                Riwayat
            </button>
        </div>
    </div>

    <div id="vehicleModal">
        <div class="vehicle-modal-content">
            <button class="modal-close-btn" onclick="document.getElementById('vehicleModal').style.display='none'">
                <span class="material-symbols-outlined" style="font-size: 20px;">close</span>
            </button>
            <h2 style="margin-bottom: 1rem;">Pilih Kendaraan</h2>
            <p style="color: var(--text-sub); margin-bottom: 1.5rem;">Pilih mobil yang Anda gunakan hari ini:</p>
            <div id="vehicleOptionsList">
                <!-- Vehicles will be listed here -->
            </div>
        </div>
    </div>

    <audio id="alarmAudio" loop preload="auto">
        <source src="https://actions.google.com/sounds/v1/alarms/doorbell.ogg" type="audio/ogg">
    </audio>

    <div id="alarmModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; padding:1.5rem; backdrop-filter:blur(5px);">
        <div
            style="background:white; border-radius:1.5rem; width:100%; max-width:400px; padding:2.5rem 2rem; text-align:center; animation: zoomIn 0.3s ease-out; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
            <div
                style="background:#fef3c7; color:#d97706; width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
                <span class="material-symbols-outlined"
                    style="font-size:48px; animation: float 2s infinite;">notifications_active</span>
            </div>
            <h2 style="margin-bottom:0.75rem; color:#1e293b; font-size:1.5rem; font-weight:800;">Tugas Baru Masuk!</h2>
            <p style="color:#64748b; margin-bottom:2rem; line-height:1.5;">Anda mendapatkan assignment pengiriman baru
                dari Admin. Silakan cek di tab <strong>Tugas Baru</strong>.</p>
            <button onclick="stopAlarm()"
                style="background:var(--primary); color:white; width:100%; padding:0.875rem; border:none; border-radius:14px; font-size:1rem; font-weight:700; cursor:pointer; box-shadow:0 4px 16px rgba(0, 122, 255, 0.25);">OK,
                SAYA MENGERTI</button>
        </div>
    </div>

    <div id="proofModal" class="confirm-modal-overlay" style="z-index: 6000; display:none;">
        <div class="confirm-modal-box" style="max-width: 440px; padding: 1.5rem; width:90%;">
            <div
                style="width: 56px; height: 56px; background: var(--primary-light); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                <span class="material-symbols-outlined" style="font-size: 32px;">photo_camera</span>
            </div>
            <h3
                style="margin: 0 0 0.5rem; font-size: 1.25rem; font-weight: 800; color: var(--text); text-align:center;">
                Penyelesaian Tugas</h3>
            <p id="proofTargetName"
                style="margin: 0 0 1.25rem; color: var(--text-sub); font-size: 0.9rem; line-height: 1.5; text-align:center;">
            </p>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label id="receiverLabel"
                    style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 0.5rem;">Diterima Oleh (Wajib)</label>
                <input type="text" id="receiverNameInput" onkeyup="validateProof()"
                    placeholder="Siapa yang menerima barang?"
                    style="width: 100%; padding: 0.75rem; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.9rem; font-family:inherit; box-sizing:border-box;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label
                    style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 0.5rem;">Keterangan
                    Driver (Wajib)</label>
                <textarea id="driverNoteInput"
                    placeholder="Contoh: Barang diterima dengan baik / Mobil mogok di jalan..."
                    onkeyup="validateProof()"
                    style="width: 100%; padding: 0.75rem; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.9rem; font-family:inherit; box-sizing:border-box; resize:none;"
                    rows="3"></textarea>
            </div>

            <div id="photoGallery"
                style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-bottom: 1rem;">
            </div>

            <div style="display:flex; gap:0.5rem; margin-bottom:0.5rem;">
                <button onclick="document.getElementById('proofCamera').click()"
                    style="flex:1; padding: 0.875rem 0.5rem; border: 2px dashed #cbd5e1; border-radius: 0.75rem; background: #f8fafc; color: #475569; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 0.5rem; cursor: pointer; transition: 0.2s;">
                    <span class="material-symbols-outlined">add_a_photo</span>
                    <span>Kamera</span>
                </button>
                <button onclick="document.getElementById('proofUpload').click()"
                    style="flex:1; padding: 0.875rem 0.5rem; border: 2px dashed #cbd5e1; border-radius: 0.75rem; background: #f8fafc; color: #475569; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 0.5rem; cursor: pointer; transition: 0.2s;">
                    <span class="material-symbols-outlined">photo_library</span>
                    <span>Upload</span>
                </button>
            </div>
            <div style="text-align:center; font-size:0.75rem; color:#94a3b8; margin-bottom:0.75rem;" id="photoBtnText">
                Tambah Foto Bukti</div>
            <input type="file" id="proofCamera" accept="image/*" capture="environment" style="display: none;"
                onchange="handleProofChange(this)">
            <input type="file" id="proofUpload" accept="image/*" multiple style="display: none;"
                onchange="handleProofChangeMulti(this)">
            <input type="file" id="proofInput" style="display:none;">

            <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                <button onclick="closeProofModal()"
                    style="flex: 1; padding: 0.875rem; border: none; border-radius: 14px; background: rgba(0,0,0,0.05); color: #3a3a3c; font-weight: 700; cursor: pointer;">Batal</button>
                <button id="proofSubmitBtn" onclick="submitProof()" disabled
                    style="flex: 1; padding: 0.875rem; border: none; border-radius: 14px; background: var(--primary); color: white; font-weight: 700; cursor: pointer; opacity: 0.5; box-shadow: 0 4px 12px rgba(0, 122, 255, 0.2);">Selesai</button>
            </div>
            <canvas id="watermarkCanvas" style="display: none;"></canvas>
        </div>
    </div>

    <div id="confirmModal" class="confirm-modal-overlay">
        <div class="confirm-modal-box">
            <div
                style="background:#fef2f2; color:#ef4444; width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem;">
                <span class="material-symbols-outlined" style="font-size:32px;">help</span>
            </div>
            <h3 style="font-size:1.25rem; font-weight:800; color:#1e293b; margin-bottom:0.5rem;" id="confirmTitle">
                Konfirmasi</h3>
            <p style="color:#64748b; font-size:0.9rem; margin-bottom:1.5rem; line-height:1.5;" id="confirmDesc">Apakah
                Anda yakin?</p>
            <div style="display:flex; gap:0.75rem;">
                <button onclick="closeConfirmModal()"
                    style="flex:1; padding:0.875rem; border:none; border-radius:14px; background:rgba(0,0,0,0.05); color:#3a3a3c; font-weight:700; cursor:pointer; transition:0.2s;">Batal</button>
                <button id="confirmYesBtn"
                    style="flex:1; padding:0.875rem; border:none; border-radius:14px; background:var(--primary); color:white; font-weight:700; cursor:pointer; transition:0.2s; box-shadow: 0 4px 12px rgba(0, 122, 255, 0.2);">Ya,
                    Selesai</button>
            </div>
        </div>
    </div>

    <div id="toast" class="toast">
        <span class="material-symbols-outlined" id="toastIcon">check_circle</span>
        <span id="toastMsg">Berhasil!</span>
    </div>

    <script>
        const API_URL = 'api.php';
        let currentPos = { lat: -6.2088, lng: 106.8456 };
        let activeVehicle = null;
        let knownPendingIds = new Set();
        let expandedTaskIds = new Set();
        let isFirstLoad = true;

        async function checkVehicleAssignment() {
            const driver_id = '<?php echo $_SESSION['user_id']; ?>';
            try {
                const res = await fetch(`${API_URL}?action=get_active_vehicle&driver_id=${driver_id}`);
                const data = await res.json();

                const card = document.getElementById('activeVehicleCard');
                const nameEl = document.getElementById('avName');
                const plateEl = document.getElementById('avPlate');
                const labelEl = document.getElementById('vStatusLabel');
                const iconContainer = document.getElementById('vStatusIcon');
                const iconEl = iconContainer.querySelector('.material-symbols-outlined');
                const btnIconEl = document.getElementById('vActionIcon');

                if (data.error) {
                    activeVehicle = null;
                    card.classList.add('empty');
                    nameEl.innerText = 'Belum Ada Mobil';
                    plateEl.innerText = 'Klik tombol untuk pilih';
                    labelEl.innerText = 'Status Driver';
                    iconEl.innerText = 'commute';
                    btnIconEl.innerText = 'add_circle';
                    loadTasks();
                } else {
                    activeVehicle = data;
                    card.classList.remove('empty');
                    nameEl.innerText = data.vehicle_name;
                    plateEl.innerText = data.plate_number;
                    labelEl.innerText = 'Kendaraan Aktif';
                    iconEl.innerText = 'local_shipping';
                    btnIconEl.innerText = 'warehouse';
                    loadTasks();
                }
            } catch (err) {
                console.error("Failed to check vehicle", err);
            }
        }

        function handleVehicleAction() {
            if (activeVehicle) {
                confirmReleaseVehicle();
            } else {
                showVehicleModal();
            }
        }

        async function showVehicleModal() {
            const modal = document.getElementById('vehicleModal');
            const list = document.getElementById('vehicleOptionsList');
            modal.style.display = 'flex';

            try {
                const res = await fetch(`${API_URL}?action=get_vehicles`);
                const vehicles = await res.json();
                list.innerHTML = '';

                vehicles.forEach(v => {
                    const isUsed = v.current_driver_name !== null;
                    const div = document.createElement('div');
                    div.className = 'vehicle-option';

                    if (isUsed) {
                        div.style.opacity = '0.6';
                        div.style.cursor = 'not-allowed';
                        div.innerHTML = `
                            <div>
                                <strong>${v.name}</strong><br>
                                <small>${v.plate_number}</small>
                                <div style="color:#ef4444; font-size:0.75rem; font-weight:600; display:flex; align-items:center; gap:4px; margin-top:6px;">
                                    <span class="material-symbols-outlined" style="font-size:14px;">person_off</span> Dipakai: ${v.current_driver_name}
                                </div>
                            </div>
                            <span class="material-symbols-outlined" style="color:#94a3b8;">lock</span>
                        `;
                    } else {
                        div.innerHTML = `
                            <div>
                                <strong>${v.name}</strong><br>
                                <small>${v.plate_number}</small>
                                <div style="color:#10b981; font-size:0.75rem; font-weight:600; display:flex; align-items:center; gap:4px; margin-top:6px;">
                                    <span class="material-symbols-outlined" style="font-size:14px;">check_circle</span> Tersedia
                                </div>
                            </div>
                            <span class="material-symbols-outlined" style="color:var(--primary);">arrow_forward</span>
                        `;
                        div.onclick = () => selectVehicle(v.id);
                    }
                    list.appendChild(div);
                });
            } catch (err) {
                list.innerHTML = '<p style="color:red">Gagal memuat daftar kendaraan.</p>';
            }
        }

        async function selectVehicle(vehicleId) {
            const driver_id = '<?php echo $_SESSION['user_id']; ?>';
            const formData = new FormData();
            formData.append('driver_id', driver_id);
            formData.append('vehicle_id', vehicleId);

            try {
                const res = await fetch(`${API_URL}?action=assign_vehicle`, {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                if (result.success) {
                    document.getElementById('vehicleModal').style.display = 'none';
                    checkVehicleAssignment();
                }
            } catch (err) {
                alert("Gagal memilih kendaraan");
            }
        }

        let activeFilter = 'pending';
        let activeDate = getTodayStr();
        let calendarSummary = [];

        function getTodayStr(diffDays = 0) {
            const d = new Date();
            if (diffDays !== 0) d.setDate(d.getDate() + Math.round(diffDays));
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        async function fetchCalendarSummary() {
            const driver_id = '<?php echo $_SESSION["user_id"]; ?>';
            const startD = getTodayStr(-7);
            const endD = getTodayStr(14);
            try {
                const res = await fetch(`${API_URL}?action=get_calendar_summary&driver_id=${driver_id}&start_date=${startD}&end_date=${endD}`);
                calendarSummary = await res.json();
                renderCalendar();
            } catch (e) { }
        }

        function renderCalendar() {
            const strip = document.getElementById('calendarStrip');
            if (!strip) return;
            strip.innerHTML = '';
            const todayStr = getTodayStr();
            const daysArr = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

            for (let i = -7; i <= 14; i++) {
                const d = new Date();
                d.setDate(d.getDate() + i);
                const loopDateStr = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

                let dayName = daysArr[d.getDay()];
                if (loopDateStr === todayStr) dayName = 'Hr Ini';

                const summaryLines = calendarSummary.filter(c => c.target_date === loopDateStr);
                let hasPending = false;
                let hasCompleted = false;
                summaryLines.forEach(s => {
                    if (s.status === 'pending' || s.status === 'in_transit') hasPending = true;
                    if (s.status === 'completed') hasCompleted = true;
                });

                let dotsHtml = '';
                if (hasPending) dotsHtml += '<div class="cal-dot pending"></div>';
                if (hasCompleted) dotsHtml += '<div class="cal-dot completed"></div>';

                const cell = document.createElement('div');
                let cellClasses = ['cal-day-cell'];
                if (loopDateStr === activeDate) cellClasses.push('active');
                if (hasPending || hasCompleted) cellClasses.push('has-task');

                cell.className = cellClasses.join(' ');
                if (loopDateStr === todayStr && loopDateStr !== activeDate) cell.style.border = '1px dashed var(--primary)';

                cell.onclick = () => selectDate(loopDateStr, cell);
                cell.innerHTML = `
                    <div class="cal-day-name">${dayName}</div>
                    <div class="cal-day-num">${d.getDate()}</div>
                    <div class="cal-dots">${dotsHtml}</div>
                `;

                strip.appendChild(cell);

                // Auto scroll
                if (loopDateStr === activeDate) {
                    setTimeout(() => {
                        cell.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                    }, 50);
                }
            }
        }

        function selectDate(dateStr) {
            activeDate = dateStr;
            isFirstLoad = true;
            lastDataJson = "";
            renderCalendar();
            loadTasks();
        }

        function switchSection(section, el) {
            document.querySelectorAll('.nav-item').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            activeFilter = section;
            lastDataJson = "";
            loadTasks(section);
        }

        function toggleCard(cardEl, taskId) {
            const isExpanded = cardEl.classList.toggle('expanded');
            if (isExpanded) {
                expandedTaskIds.add(taskId);
            } else {
                expandedTaskIds.delete(taskId);
            }
        }

        function navigateToDestination(lat, lng, address) {
            let url;
            const hasCoords = lat && lng && parseFloat(lat) !== 0 && parseFloat(lng) !== 0;

            if (hasCoords) {
                url = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(lat)},${encodeURIComponent(lng)}&travelmode=driving`;
            } else {
                url = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(address)}&travelmode=driving`;
            }
            window.open(url, '_blank');
        }

        function showMap(address) {
            navigateToDestination('', '', address);
        }

        function updateLocation() {
            const driver_id = '<?php echo $_SESSION['user_id']; ?>';

            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(async (position) => {
                    currentPos.lat = position.coords.latitude;
                    currentPos.lng = position.coords.longitude;

                    document.getElementById('gpsAlertBox').style.display = 'none';

                    try {
                        const formData = new FormData();
                        formData.append('user_id', driver_id);
                        formData.append('lat', currentPos.lat);
                        formData.append('lng', currentPos.lng);

                        await fetch(`${API_URL}?action=update_location`, {
                            method: 'POST',
                            body: formData
                        });
                    } catch (err) {
                        console.error("Gagal mengirim lokasi", err);
                    }
                }, (error) => {
                    console.error("GPS Error:", error);
                    document.getElementById('gpsAlertBox').style.display = 'flex';

                    if (error.code === error.PERMISSION_DENIED) {
                        showToast('Izin Lokasi Ditolak! Harap izinkan di Browser.', 'danger');
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        console.warn("Posisi tidak tersedia (sinyal lemah)");
                    } else if (error.code === error.TIMEOUT) {
                        console.warn("GPS Timeout - retrying later");
                    }
                }, {
                    enableHighAccuracy: false,
                    timeout: 20000,
                    maximumAge: 10000
                });
            } else {
                document.getElementById('gpsAlertBox').style.display = 'flex';
            }
        }

        let lastDataJson = "";

        async function loadTasks(filter = activeFilter) {
            const driver_id = '<?php echo $_SESSION["user_id"]; ?>';
            const container = document.getElementById('taskList');

            try {
                const target_date = activeDate;
                const res = await fetch(`${API_URL}?action=get_deliveries&driver_id=${driver_id}&target_date=${target_date}`);
                const tasks = await res.json();

                if (isFirstLoad && tasks.length === 0) {
                    container.innerHTML = '<div style="text-align:center; padding: 2rem;">Memuat...</div>';
                }

                const currentDataJson = JSON.stringify(tasks) + filter;
                if (currentDataJson === lastDataJson) {
                    return;
                }
                lastDataJson = currentDataJson;

                window.allTasks = tasks;

                let html = '';

                let countPending = 0;
                let countTransit = 0;
                let countCompleted = 0;
                let tempPendingIds = new Set();

                const todayObj = new Date();
                const todayStr = todayObj.getFullYear() + '-' + String(todayObj.getMonth() + 1).padStart(2, '0') + '-' + String(todayObj.getDate()).padStart(2, '0');

                tasks.forEach(task => {
                    const isCompleted = task.status === 'completed' || task.status === 'canceled';
                    const isTransit = task.status === 'in_transit';

                    const taskDateStr = task.target_date ? task.target_date.split(' ')[0] : todayStr;
                    const isActionable = taskDateStr <= todayStr;
                    const isLate = taskDateStr < todayStr;

                    if (task.status === 'pending') {
                        countPending++;
                        tempPendingIds.add(task.id);
                    } else if (isTransit) {
                        countTransit++;
                    } else if (isCompleted || task.status === 'canceled') {
                        countCompleted++;
                    }

                    if (filter === 'pending' && task.status !== 'pending') return;
                    if (filter === 'in_transit' && task.status !== 'in_transit') return;
                    if (filter === 'completed' && (task.status !== 'completed' && task.status !== 'canceled')) return;

                    const statusText = isTransit ? 'PERJALANAN' : (task.status === 'canceled' ? 'CANCEL' : (isCompleted ? 'SELESAI' : 'TUGAS BARU'));
                    const badgeClass = task.status === 'canceled' ? 'badge-danger' : (isCompleted ? 'badge-completed' : (isTransit ? 'badge-transit' : 'badge-pending'));
                    const iconName = task.status === 'canceled' ? 'block' : (isTransit ? 'local_shipping' : (isCompleted ? 'check_circle' : 'pending_actions'));
                    const iconClass = task.status === 'canceled' ? 'pending' : (isTransit ? 'transit' : (isCompleted ? 'completed' : 'pending'));

                    const safeOrigin = (task.origin_name || '').replace(/'/g, "\\'");
                    const safeDest = (task.destination_name || '').replace(/'/g, "\\'");

                    const isExpanded = expandedTaskIds.has(task.id);

                    const taskTime = task.target_date ? task.target_date.split(' ')[1]?.slice(0, 5) : '-';
                    let typeLabel = task.task_type === 'antar' ? 'DELIVERY' : 'PICKUP';
                    if (task.pickup_id && task.task_type !== 'antar') typeLabel = 'PICKUP (REQ)';
                    const typeColor = task.task_type === 'antar' ? '#007AFF' : '#FF9500';

                    html += `
                        <div class="task-card ${isExpanded ? 'expanded' : ''}" id="card-${task.id}" style="--card-accent: ${typeColor};">
                            <!-- HEADER (clickable to toggle) -->
                            <div class="task-card-header" onclick="toggleCard(document.getElementById('card-${task.id}'), ${task.id})">
                                <div class="task-header-left" style="flex:1; min-width:0;">
                                    <div class="task-header-icon ${iconClass}">
                                        <span class="material-symbols-outlined" style="font-size:20px;">${iconName}</span>
                                    </div>
                                    <div class="task-header-meta">
                                        <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                                            <span class="task-type-badge" style="background:${typeColor}20; color:${typeColor}; flex-shrink:0;">${typeLabel}</span>
                                            <span style="font-size:0.7rem; font-weight:700; color:var(--text-sub); white-space:nowrap;">⏰ ${taskTime}</span>
                                        </div>
                                        <div class="dest-name">${task.destination_name || '—'}</div>
                                        ${task.origin_name ? `<div style="font-size:0.68rem; color:var(--text-sub); font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">dari: ${task.origin_name}</div>` : ''}
                                    </div>
                                </div>
                                <div class="task-header-right">
                                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
                                        <span class="status-badge ${badgeClass}">${statusText}</span>
                                    </div>
                                    <span class="material-symbols-outlined accordion-arrow">expand_more</span>
                                </div>
                            </div>

                            <!-- BODY (collapsible) -->
                            <div class="task-card-body">
                                <div class="task-card-body-inner">
                                    <div class="task-divider"></div>

                                    <div class="task-row">
                                        <span class="material-symbols-outlined mat-icon">store</span>
                                        <div style="flex:1;">
                                            <div class="value" style="font-weight:700; font-size:0.9rem;">${task.origin_name}</div>
                                        </div>
                                    </div>

                                    <div class="task-row nav-tap"
                                         onclick="navigateToDestination('${task.destination_lat || ''}', '${task.destination_lng || ''}', '${safeDest}')"
                                         title="Ketuk untuk navigasi ke tujuan">
                                        <span class="material-symbols-outlined mat-icon" style="color:#6366f1;">location_on</span>
                                        <div style="flex:1;">
                                            <div class="value" style="font-weight:700; font-size:0.9rem;">${task.destination_name}</div>
                                        </div>
                                        <span class="material-symbols-outlined" style="font-size:16px; color:#6366f1; opacity:0.6; flex-shrink:0;">open_in_new</span>
                                    </div>
                                    
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin: 1rem 0;">
                                        <button class="btn-action" style="padding: 0.6rem; font-size: 0.75rem; background: #f8fafc; border: 1px solid var(--border); color: var(--text);" 
                                                onclick="event.stopPropagation(); viewSjPhoto(${task.id})">
                                            <span class="material-symbols-outlined" style="font-size:18px;">description</span> Lihat SJ
                                        </button>
                                        <button class="btn-action" style="padding: 0.6rem; font-size: 0.75rem; background: #f8fafc; border: 1px solid var(--border); color: var(--text);"
                                                onclick="event.stopPropagation(); viewGoodsPhoto(${task.id})">
                                            <span class="material-symbols-outlined" style="font-size:18px;">inventory_2</span> Foto Barang
                                        </button>
                                    </div>

                                    <div class="task-meta-row" style="background:var(--primary-light); padding:0.75rem; border-radius:12px; margin-bottom:1rem; border:1px dashed var(--primary);">
                                        <div style="display:flex; flex-wrap:wrap; gap: 10px;">
                                            <div class="task-meta-item" style="flex:1; min-width: 150px;">
                                                <div class="info">ASSIGN BY</div>
                                                <div class="value" style="font-size:0.85rem; display:flex; align-items:center; gap:6px;">
                                                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--primary);">person_edit</span>
                                                    ${task.creator_name || 'Admin'}
                                                </div>
                                            </div>
                                            <div class="task-meta-item" style="text-align:right;">
                                                <button onclick="event.stopPropagation(); openAdminWa('${task.creator_phone || ''}', '${task.creator_name || 'Admin'}')" 
                                                        style="background:#25d366; color:white; border:none; padding:6px 12px; border-radius:8px; font-size:0.75rem; font-weight:800; display:flex; align-items:center; gap:6px; cursor:pointer; box-shadow:0 4px 10px rgba(37,211,102,0.3);">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="display:block;">
                                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.185-.573c.948.517 1.938.808 3.144.809 3.181 0 5.767-2.586 5.768-5.766.001-3.18-2.584-5.766-5.766-5.766zm3.333 7.828c-.144.405-.833.743-1.159.791-.326.048-.739.083-2.133-.49-1.393-.573-2.28-1.937-2.35-2.035-.07-.098-.562-.746-.562-1.435 0-.689.351-1.028.476-1.155.125-.127.272-.159.363-.159.091 0 .181.001.259.005.08.004.185-.03.29.221.105.251.362.881.393.945.031.063.051.137.01.219-.041.082-.061.133-.122.204-.041.082-.061.133-.122.204-.061.072-.128.161-.184.216-.062.062-.127.129-.055.253.072.124.322.532.691.861.475.424.877.556 1.002.618.125.062.198.052.271-.031.073-.083.313-.365.396-.489.083-.125.166-.104.281-.062.114.041.727.343.852.406.124.062.208.094.239.146.031.052.031.297-.113.702zM12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.985-1.308C8.423 21.571 10.134 22 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18c-1.716 0-3.313-.483-4.664-1.314l-.334-.203-2.906.763.777-2.834-.223-.353C3.655 14.731 3 13.13 3 12c0-4.963 4.037-9 9-9s9 4.037 9 9-4.037 9-9 9z"/>
                                                    </svg>
                                                    WhatsApp
                                                </button>
                                            </div>
                                        </div>
                                        
                                        ${task.notes ? `
                                            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed rgba(99, 102, 241, 0.4); width: 100%;">
                                                <div style="font-size:0.65rem; font-weight:800; color:var(--primary); text-transform:uppercase; margin-bottom:4px; display:flex; align-items:center; gap:4px;">
                                                    <span class="material-symbols-outlined" style="font-size:14px;">info</span> Instruksi Admin
                                                </div>
                                                <div style="font-size:0.85rem; color:var(--text); line-height:1.4; font-weight:500; font-style:italic;">"${task.notes}"</div>
                                            </div>
                                        ` : ''}
                                    </div>

                                    ${!isCompleted && isActionable ? `
                                        <div style="display:flex; flex-direction:column; gap:0.6rem;">
                                            <button class="btn-action btn-nav" style="width:100%;" onclick="navigateToDestination('${task.destination_lat || ''}', '${task.destination_lng || ''}', '${safeDest}')">
                                                <span class="material-symbols-outlined">navigation</span> Navigasi Ke Tujuan
                                            </button>
                                            <div style="display:flex; gap:0.6rem;">
                                                ${isTransit ? `
                                                    <button class="btn-action" onclick="updateStatus(${task.id}, 'canceled')" style="flex:1; background:#fee2e2; color:#ef4444; border:1px solid #fecaca;">
                                                        <span class="material-symbols-outlined">block</span> Cancel
                                                    </button>
                                                ` : ''}
                                                <button class="btn-action btn-checkin" style="flex:2; ${(!isTransit && !activeVehicle) ? 'opacity:0.6; cursor:not-allowed;' : ''}" 
                                                        onclick="updateStatus(${task.id}, '${isTransit ? 'completed' : 'in_transit'}', ${isLate})">
                                                    <span class="material-symbols-outlined">${isTransit ? 'check_circle' : 'play_arrow'}</span>
                                                    ${isTransit ? 'Selesai' : 'Mulai Tugas'}
                                                </button>
                                            </div>
                                        </div>
                                    ` : ''}
                                    ${!isCompleted && !isActionable ? `
                                        <div style="text-align:center; padding:0.5rem 0;">
                                            <span class="material-symbols-outlined" style="color:#f59e0b; font-size:36px;">event</span>
                                            <div style="font-size:0.85rem; color:var(--text-sub); margin-top:4px; font-weight:600;">Tugas ini dijadwalkan untuk besok</div>
                                        </div>
                                    ` : ''}
                                    ${isCompleted ? `
                                        <div style="text-align:center; padding:0.5rem 0;">
                                            <div style="display:flex; justify-content:center; gap:0.5rem; margin-bottom:1rem;" id="proof-gallery-${task.id}">
                                                ${(() => {
                                if (!task.proof_file) return '';
                                try {
                                    const proofs = JSON.parse(task.proof_file);
                                    if (Array.isArray(proofs)) {
                                        return proofs.map(f => `
                                                                <img src="uploads/${f}" 
                                                                     onclick="showImagePopup('uploads/${f}')" 
                                                                     style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:2px solid #e2e8f0; cursor:pointer;"
                                                                     alt="Bukti">
                                                            `).join('');
                                    }
                                } catch (e) {
                                    if (typeof task.proof_file === 'string' && !task.proof_file.startsWith('[')) {
                                        return `<img src="uploads/${task.proof_file}" onclick="showImagePopup('uploads/${task.proof_file}')" style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:2px solid #e2e8f0; cursor:pointer;">`;
                                    }
                                }
                                return '';
                            })()}
                                            </div>

                                            <span class="material-symbols-outlined" style="color:${task.status === 'canceled' ? '#ef4444' : '#10b981'}; font-size:32px;">${task.status === 'canceled' ? 'block' : 'task_alt'}</span>
                                            <div style="font-size:0.9rem; color:var(--text-sub); margin-top:4px; font-weight:700;">Tugas ${task.status === 'canceled' ? 'Cancel' : 'Selesai'}</div>
                                            ${task.status !== 'canceled' && task.receiver_name ? `<div style="font-size:0.85rem; color:var(--text); margin-top:6px;"><strong>Diterima Oleh:</strong> ${task.receiver_name}</div>` : ''}
                                            ${task.driver_notes ? `<div style="font-size:0.75rem; color:#64748b; margin-top:6px; background:#f1f5f9; padding:6px 10px; border-radius:6px; display:inline-block; font-style:italic;"><strong>Ket:</strong> ${task.driver_notes}</div>` : ''}
                                            ${task.late_reason ? `<div style="font-size:0.75rem; color:#eab308; margin-top:6px; background:#fffbeb; padding:4px 8px; border-radius:6px; display:inline-block;"><strong>Alasan:</strong> ${task.late_reason}</div>` : ''}
                                            
                                            <div style="margin-top: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                                                <button class="btn-action" onclick="shareTask(${task.id})" style="background: #25d366; color: white; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3); font-size:1rem; padding:0.875rem;">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 8px;">
                                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.185-.573c.948.517 1.938.808 3.144.809 3.181 0 5.767-2.586 5.768-5.766.001-3.18-2.584-5.766-5.766-5.766zm3.333 7.828c-.144.405-.833.743-1.159.791-.326.048-.739.083-2.133-.49-1.393-.573-2.28-1.937-2.35-2.035-.07-.098-.562-.746-.562-1.435 0-.689.351-1.028.476-1.155.125-.127.272-.159.363-.159.091 0 .181.001.259.005.08.004.185-.03.29.221.105.251.362.881.393.945.031.063.051.137.01.219-.041.082-.061.133-.122.204-.041.082-.061.133-.122.204-.061.072-.128.161-.184.216-.062.062-.127.129-.055.253.072.124.322.532.691.861.475.424.877.556 1.002.618.125.062.198.052.271-.031.073-.083.313-.365.396-.489.083-.125.166-.104.281-.062.114.041.727.343.852.406.124.062.208.094.239.146.031.052.031.297-.113.702zM12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.985-1.308C8.423 21.571 10.134 22 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18c-1.716 0-3.313-.483-4.664-1.314l-.334-.203-2.906.763.777-2.834-.223-.353C3.655 14.731 3 13.13 3 12c0-4.963 4.037-9 9-9s9 4.037 9 9-4.037 9-9 9z"/>
                                                    </svg>
                                                    Share Laporan
                                                </button>
                                                ${task.is_shared == 1 ? `
                                                    <div style="display: flex; align-items: center; justify-content: center; gap: 4px; color: #10b981; font-size: 0.8rem; font-weight: 700;">
                                                        <span class="material-symbols-outlined" style="font-size: 18px;">done_all</span> Sudah dibagikan
                                                    </div>
                                                ` : `
                                                    <div style="color: var(--text-sub); font-size: 0.75rem; font-weight: 600;">Belum dibagikan ke siapapun</div>
                                                `}
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });

                document.getElementById('countPending').innerText = countPending;
                document.getElementById('countInTransit').innerText = countTransit;
                document.getElementById('countCompleted').innerText = countCompleted;

                const badgePending = document.getElementById('badgePending');
                if (badgePending) {
                    badgePending.innerHTML = `<span>${countPending}</span>`;
                    badgePending.style.display = countPending > 0 ? 'flex' : 'none';
                }
                const badgeTransit = document.getElementById('badgeTransit');
                if (badgeTransit) {
                    badgeTransit.innerHTML = `<span>${countTransit}</span>`;
                    badgeTransit.style.display = countTransit > 0 ? 'flex' : 'none';
                }
                const badgeCompleted = document.getElementById('badgeCompleted');
                if (badgeCompleted) {
                    badgeCompleted.innerHTML = `<span>${countCompleted}</span>`;
                    badgeCompleted.style.display = countCompleted > 0 ? 'flex' : 'none';
                }

                if (!isFirstLoad) {
                    let hasNewTask = false;
                    for (let id of tempPendingIds) {
                        if (!knownPendingIds.has(id)) {
                            hasNewTask = true;
                            break;
                        }
                    }
                    if (hasNewTask) {
                        playAlarm();
                    }
                }

                knownPendingIds = tempPendingIds;
                isFirstLoad = false;

                container.innerHTML = html || `
                    <div style="text-align:center; padding: 4rem 2rem; color:var(--text-sub);">
                        <span class="material-symbols-outlined" style="font-size:48px; margin-bottom:1rem; display:block; opacity:0.3;">inventory_2</span>
                        <p>Tidak ada tugas untuk kategori ini.</p>
                    </div>
                `;

            } catch (err) {
                console.error(err);
                container.innerHTML = '<p style="color:red; text-align:center;">Gagal memuat tugas.</p>';
            }
        }

        let pendingConfirmCallback = null;

        function showConfirm(title, desc, callback) {
            document.getElementById('confirmTitle').innerText = title;
            document.getElementById('confirmDesc').innerText = desc;
            pendingConfirmCallback = callback;
            document.getElementById('confirmModal').style.display = 'flex';
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
        }

        document.getElementById('confirmYesBtn').addEventListener('click', () => {
            closeConfirmModal();
            if (pendingConfirmCallback) pendingConfirmCallback();
        });

        function showToast(msg, type = 'success', persistent = false) {
            const toast = document.getElementById("toast");
            const icon = document.getElementById("toastIcon");
            document.getElementById("toastMsg").innerText = msg;

            toast.className = "toast " + type;
            icon.innerText = type === 'success' ? 'check_circle' : (type === 'warning' ? 'warning' : 'error');

            toast.classList.add("show");

            if (window.toastTimeout) clearTimeout(window.toastTimeout);

            if (!persistent) {
                window.toastTimeout = setTimeout(() => { toast.classList.remove("show"); }, 3000);
            }
        }

        function hideToast() {
            document.getElementById("toast").classList.remove("show");
        }

        let pendingLateId = null;
        let pendingLateStatus = null;

        function closeLateReasonModal() {
            document.getElementById('lateReasonModal').style.display = 'none';
            document.getElementById('lateReasonInput').value = '';
            pendingLateId = null;
        }

        document.getElementById('lateReasonSubmitBtn').addEventListener('click', () => {
            const reason = document.getElementById('lateReasonInput').value.trim();
            if (!reason) {
                alert('Alasan keterlambatan harus diisi!');
                document.getElementById('lateReasonInput').focus();
                return;
            }
            const id = pendingLateId;
            const st = pendingLateStatus;
            closeLateReasonModal();
            openProofModal(id, st);
        });

        async function updateStatus(id, status, isLate = false) {
            if (status === 'in_transit' && !activeVehicle) {
                showToast('Belum Memilih Mobil!', 'warning');
                showVehicleModal();
                return;
            }

            if (status === 'completed' || status === 'canceled') {
                if (isLate && status === 'completed') {
                    pendingLateId = id;
                    pendingLateStatus = status;
                    document.getElementById('lateReasonModal').style.display = 'flex';
                } else {
                    openProofModal(id, status);
                }
            } else {
                executeUpdateStatus(id, status);
            }
        }

        async function executeUpdateStatus(id, status, late_reason = '', photoBlobs = [], receiverName = '', driver_notes = '') {
            const formData = new FormData();
            formData.append('delivery_id', id);
            formData.append('status', status);
            if (late_reason) formData.append('late_reason', late_reason);
            if (receiverName) formData.append('receiver_name', receiverName);
            if (driver_notes) formData.append('driver_notes', driver_notes);

            if (photoBlobs.length > 0) {
                photoBlobs.forEach((blob, i) => {
                    formData.append('surat_jalan_file[]', blob, `proof_${id}_${i}_${Date.now()}.jpg`);
                });
            }

            try {
                const res = await fetch(`${API_URL}?action=update_status`, {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                if (result.success) {
                    if (status === 'completed') {
                        showToast('Tugas berhasil diselesaikan!', 'success');
                        closeProofModal();
                    } else if (status === 'canceled') {
                        showToast('Laporan cancel dikirim', 'warning');
                        closeProofModal();
                    } else if (status === 'in_transit') {
                        showToast('Status diperbarui: Sedang Dalam Perjalanan', 'success');
                    }
                    checkVehicleAssignment();
                    fetchCalendarSummary(); // update dots
                } else {
                    showToast(result.error || 'Gagal memperbarui status', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Gagal memperbarui status', 'error');
            }
        }

        let currentProofTaskId = null;
        let currentStatusMode = 'completed';
        let rawProofDataUrls = [];
        let currentProofTaskData = null;
        let currentProofTaskPromise = null;

        function openProofModal(id, mode = 'completed') {
            currentProofTaskId = id;
            currentStatusMode = mode;

            let task = allTasks ? allTasks.find(t => t.id == id) : null;
            currentProofTaskData = task || null;

            const titleEl = document.querySelector('#proofModal h3');
            const receiverGroup = document.getElementById('receiverNameInput').closest('.form-group');
            const submitBtn = document.getElementById('proofSubmitBtn');

            if (mode === 'canceled') {
                titleEl.innerText = 'Laporkan Kendala / Cancel';
                receiverGroup.style.display = 'none';
                submitBtn.innerText = 'Kirim Laporan Cancel';
                submitBtn.style.background = '#ef4444';
            } else {
                titleEl.innerText = 'Penyelesaian Tugas';
                receiverGroup.style.display = 'block';
                submitBtn.innerText = 'Selesai';
                submitBtn.style.background = 'var(--primary)';

                const labelEl = document.getElementById('receiverLabel');
                const inputEl = document.getElementById('receiverNameInput');
                if (labelEl && inputEl) {
                    labelEl.innerText = 'Diterima Oleh (Wajib)';
                    inputEl.placeholder = 'Siapa yang menerima barang?';
                }
            }

            if (task) {
                document.getElementById('proofTargetName').innerText = `Tujuan: ${task.destination_name}`;
            } else {
                document.getElementById('proofTargetName').innerText = '';
            }
            document.getElementById('proofModal').style.display = 'flex';
            resetProofForm();

            currentProofTaskPromise = fetch(`${API_URL}?action=get_delivery&id=${id}`)
                .then(res => res.json())
                .then(dbTask => {
                    if (dbTask && !dbTask.error) {
                        currentProofTaskData = dbTask;
                        if (dbTask.destination_name) {
                            document.getElementById('proofTargetName').innerText = `Tujuan: ${dbTask.destination_name}`;
                        }
                        const labelElDb = document.getElementById('receiverLabel');
                        const inputElDb = document.getElementById('receiverNameInput');
                        if (labelElDb && inputElDb && mode !== 'canceled') {
                            labelElDb.innerText = 'Diterima Oleh (Wajib)';
                            inputElDb.placeholder = 'Siapa yang menerima barang?';
                        }
                    }
                    return dbTask;
                })
                .catch(err => {
                    console.error("Error fetching task details:", err);
                    return null;
                });
        }

        function closeProofModal() {
            document.getElementById('proofModal').style.display = 'none';
            resetProofForm();
        }

        function resetProofForm() {
            document.getElementById('proofInput').value = '';
            document.getElementById('proofCamera').value = '';
            document.getElementById('proofUpload').value = '';
            document.getElementById('photoGallery').innerHTML = '';
            document.getElementById('receiverNameInput').value = '';
            document.getElementById('driverNoteInput').value = '';
            document.getElementById('proofSubmitBtn').disabled = true;
            document.getElementById('proofSubmitBtn').style.opacity = '0.5';
            document.getElementById('proofSubmitBtn').innerText = currentStatusMode === 'canceled' ? 'Kirim Laporan Cancel' : 'Selesai';
            document.getElementById('photoBtnText').innerText = 'Tambah Foto Bukti';
            rawProofDataUrls = [];
        }

        async function handleProofChange(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = async (e) => {
                rawProofDataUrls.push(e.target.result);
                renderGallery();
                validateProof();
            };
            reader.readAsDataURL(file);
            input.value = ''; // allow same file
        }

        async function handleProofChangeMulti(input) {
            if (!input.files || input.files.length === 0) return;
            const files = Array.from(input.files);

            if (files.length > 1) showToast(`Memproses ${files.length} foto...`, 'info');

            for (const file of files) {
                await new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onload = async (e) => {
                        rawProofDataUrls.push(e.target.result);
                        resolve();
                    };
                    reader.readAsDataURL(file);
                });
            }
            renderGallery();
            validateProof();
            input.value = '';
        }

        async function watermarkImage(img, receiverName) {
            return new Promise((resolve) => {
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

                const task = currentProofTaskData || (window.allTasks ? window.allTasks.find(t => t.id == currentProofTaskId) : null);
                const now = new Date();
                const dateStr = now.toLocaleDateString('id-ID', { year: 'numeric', month: '2-digit', day: '2-digit' });
                const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const driverName = '<?php echo addslashes($_SESSION["name"] ?? $_SESSION["username"] ?? "Driver"); ?>';

                const lat = currentPos && currentPos.lat ? currentPos.lat.toFixed(6) : '0.000000';
                const lng = currentPos && currentPos.lng ? currentPos.lng.toFixed(6) : '0.000000';

                const receiverLabelStr = 'DITERIMA OLEH';

                const lines = [
                    `📦 SJ: ${(task && task.surat_jalan) ? task.surat_jalan.trim() : '-'}`,
                    `🏁 DARI: ${(task && task.origin_name) ? task.origin_name.trim() : '-'}`,
                    `🏁 TUJUAN: ${(task && task.destination_name) ? task.destination_name.trim() : '-'}`,
                    `👤 DRIVER: ${driverName} | ${receiverLabelStr}: ${receiverName ? receiverName.trim() : '-'}`,
                    `📍 LOKASI: ${(task && task.destination_name) ? task.destination_name.trim() : '-'}`,
                    `📅 ${dateStr} | 🕒 ${timeStr} | 🌍 GPS: ${lat}, ${lng}`
                ];

                let padding = fontSize * 1.2;
                lines.reverse().forEach((line, i) => {
                    ctx.fillText(line, padding, h - (padding + (i * fontSize * 1.4)));
                });

                canvas.toBlob((blob) => {
                    resolve(blob);
                }, 'image/jpeg', 0.82);
            });
        }

        function renderGallery() {
            const gallery = document.getElementById('photoGallery');
            gallery.innerHTML = rawProofDataUrls.map((url, i) => `
                <div style="position:relative; aspect-ratio:1/1;">
                    <img src="${url}" onclick="showImagePopup('${url}')" style="width:100%; height:100%; object-fit:cover; border-radius:8px; border:1px solid var(--border); cursor:pointer;">
                    <button onclick="removePhoto(${i})" style="position:absolute; top:-4px; right:-4px; width:20px; height:20px; border-radius:50%; background:#ef4444; color:white; border:none; display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:10;">
                        <span class="material-symbols-outlined" style="font-size:14px;">close</span>
                    </button>
                </div>
            `).join('');
        }

        function removePhoto(index) {
            rawProofDataUrls.splice(index, 1);
            renderGallery();
            validateProof();
        }

        function validateProof() {
            const btn = document.getElementById('proofSubmitBtn');
            const note = document.getElementById('driverNoteInput').value.trim();
            const receiver = document.getElementById('receiverNameInput').value.trim();
            const hasPhotos = rawProofDataUrls.length > 0;
            const hasNote = note.length >= 3;

            let isValid = false;
            if (currentStatusMode === 'canceled') {
                isValid = hasNote;
            } else {
                isValid = hasPhotos && receiver.length >= 2 && hasNote;
            }

            btn.disabled = !isValid;
            btn.style.opacity = isValid ? '1' : '0.5';
            btn.style.cursor = isValid ? 'pointer' : 'not-allowed';
        }

        async function submitProof() {
            if (currentStatusMode !== 'canceled' && rawProofDataUrls.length === 0) return;
            const id = currentProofTaskId;
            const status = currentStatusMode;
            const receiverName = status === 'canceled' ? '' : document.getElementById('receiverNameInput').value;
            const driverNotes = document.getElementById('driverNoteInput').value;

            const btn = document.getElementById('proofSubmitBtn');
            btn.disabled = true;
            btn.innerText = 'Memproses...';

            if (currentProofTaskPromise) {
                try {
                    await currentProofTaskPromise;
                } catch (e) {
                    console.error("Error waiting for task data fetch:", e);
                }
            }

            let finalBlobs = [];
            for (const dataUrl of rawProofDataUrls) {
                const img = new Image();
                img.src = dataUrl;
                await new Promise(res => img.onload = res);
                const blob = await watermarkImage(img, receiverName);
                finalBlobs.push(blob);
            }

            if (pendingLateId == id && status === 'completed') {
                const reason = document.getElementById('lateReasonInput').value;
                executeUpdateStatus(id, status, reason, finalBlobs, receiverName, driverNotes);
            } else {
                executeUpdateStatus(id, status, '', finalBlobs, receiverName, driverNotes);
            }
        }

        async function shareTask(taskId) {
            const task = allTasks.find(t => t.id == taskId);
            if (!task) return;

            const driverName = '<?php echo addslashes($_SESSION["name"] ?? $_SESSION["username"] ?? "Driver"); ?>';
            const typeText = task.task_type === 'antar' ? 'DELIVERY' : 'PICKUP';
            const statusTitle = task.status === 'canceled' ? 'LAPORAN KENDALA / CANCEL' : (task.task_type === 'antar' ? `BUKTI PENGIRIMAN - ${typeText}` : `BUKTI PENGAMBILAN - ${typeText}`);
            const receiverLabel = 'DITERIMA OLEH';
            const caption = `📌 *${statusTitle}*\n\n` +
                `📦 *SJ:* ${task.surat_jalan}\n` +
                `🏠 *ASAL:* ${task.origin_name}\n` +
                `🏁 *TUJUAN:* ${task.destination_name}\n` +
                (task.status !== 'canceled' ? `👤 *${receiverLabel}:* ${task.receiver_name || '-'}\n` : '') +
                `🕒 *WAKTU:* ${task.end_time || '-'}\n` +
                (task.driver_notes ? `📝 *KET:* ${task.driver_notes}\n` : '') +
                `\n_Dikirim oleh Driver: ${driverName}_`;

            let proofFiles = [];
            if (task.proof_file) {
                try {
                    const proofs = JSON.parse(task.proof_file);
                    if (Array.isArray(proofs)) {
                        proofFiles = proofs.map(p => 'uploads/' + p);
                    }
                } catch (e) {
                    if (typeof task.proof_file === 'string' && !task.proof_file.startsWith('[')) {
                        proofFiles = ['uploads/' + task.proof_file];
                    }
                }
            }

            if (navigator.share) {
                try {
                    const shareData = {
                        title: 'Bukti Pengiriman',
                        text: caption
                    };

                    if (proofFiles.length > 0) {
                        try {
                            const fileObjects = [];
                            for (let i = 0; i < proofFiles.length; i++) {
                                const path = proofFiles[i];
                                try {
                                    const response = await fetch(path);
                                    if (!response.ok) continue;
                                    const blob = await response.blob();
                                    const file = new File([blob], `bukti_${i + 1}.jpg`, { type: 'image/jpeg' });
                                    fileObjects.push(file);
                                } catch (e) {
                                    console.warn(`Failed to fetch photo ${i + 1}:`, e);
                                }
                            }

                            if (fileObjects.length > 0 && navigator.canShare && navigator.canShare({ files: fileObjects })) {
                                shareData.files = fileObjects;
                            }
                        } catch (fetchErr) {
                            console.error('Failed to prepare proof photos for sharing:', fetchErr);
                        }
                    }

                    await navigator.share(shareData);

                    const formData = new FormData();
                    formData.append('delivery_id', taskId);
                    await fetch(`${API_URL}?action=mark_shared`, { method: 'POST', body: formData });

                    showToast('Berhasil di-share!');
                    loadTasks();
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        console.error('Error sharing:', err);
                        openWaFallback(caption, taskId);
                    }
                }
            } else {
                openWaFallback(caption, taskId);
            }
        }

        async function openWaFallback(caption, taskId) {
            const waUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(caption)}`;
            window.open(waUrl, '_blank');

            // Mark as shared in DB
            const formData = new FormData();
            formData.append('delivery_id', taskId);
            await fetch(`${API_URL}?action=mark_shared`, { method: 'POST', body: formData });

            showToast('Membuka WhatsApp...');
            loadTasks();
        }

        function playAlarm() {
            const modal = document.getElementById('alarmModal');
            const audio = document.getElementById('alarmAudio');
            modal.style.display = 'flex';
            audio.play().catch(e => console.log('Autoplay prevented by browser', e));
        }

        function stopAlarm() {
            const modal = document.getElementById('alarmModal');
            const audio = document.getElementById('alarmAudio');
            modal.style.display = 'none';
            audio.pause();
            audio.currentTime = 0; // Reset sound
        }

        // Initialize
        checkVehicleAssignment();
        fetchCalendarSummary();

        setInterval(() => {
            const anyModalOpen = document.querySelectorAll('.modal-overlay[style*="flex"], .confirm-modal-overlay[style*="flex"]').length > 0;
            if (!anyModalOpen) {
                checkVehicleAssignment();
                fetchCalendarSummary();
            }
        }, 20000);

        function openAdminWa(phone, name) {
            if (!phone) {
                showToast('Nomor WA Admin tidak tersedia', 'error');
                return;
            }
            let cleanPhone = phone.replace(/\D/g, '');
            if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.substring(1);
            const msg = `Halo ${name}, saya driver ingin bertanya mengenai tugas saya.`;
            window.open(`https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(msg)}`, '_blank');
        }

        function parseFile(fileStr) {
            if (!fileStr) return null;
            try {
                if (fileStr.startsWith('[') || fileStr.startsWith('{')) {
                    const parsed = JSON.parse(fileStr);
                    return Array.isArray(parsed) ? parsed[0] : (parsed.file || parsed);
                }
            } catch (e) { }
            return fileStr;
        }

        function viewSjPhoto(taskId) {
            const task = allTasks.find(t => t.id == taskId);
            if (!task) { showToast('Data tugas tidak ditemukan', 'error'); return; }

            let files = [];

            if (Array.isArray(task.surat_jalan_file) && task.surat_jalan_file.length > 0) {
                files = task.surat_jalan_file;
            }
            else if (Array.isArray(task.request_sj_file_arr) && task.request_sj_file_arr.length > 0) {
                files = task.request_sj_file_arr;
            }
            else if (task.surat_jalan_file && typeof task.surat_jalan_file === 'string') {
                try {
                    const parsed = JSON.parse(task.surat_jalan_file);
                    files = Array.isArray(parsed) ? parsed : [parsed];
                } catch (e) {
                    files = [task.surat_jalan_file];
                }
            }

            if (files.length === 0) {
                showToast('Tidak ada foto SJ', 'error');
                return;
            }

            if (files.length === 1) {
                showImagePopup('uploads/' + files[0]);
            } else {
                showSjGallery(files);
            }
        }

        function showSjGallery(files) {
            let existing = document.getElementById('sjGalleryModal');
            if (existing) existing.remove();

            const modal = document.createElement('div');
            modal.id = 'sjGalleryModal';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:100001;display:flex;align-items:center;justify-content:center;flex-direction:column;padding:1.5rem;backdrop-filter:blur(5px);';
            modal.onclick = (e) => { if (e.target === modal) modal.remove(); };

            const header = document.createElement('div');
            header.style.cssText = 'display:flex;justify-content:space-between;align-items:center;width:100%;max-width:480px;margin-bottom:1rem;';
            header.innerHTML = `
                <span style="color:white;font-weight:800;font-size:1rem;">📄 Foto Surat Jalan (${files.length})</span>
                <button onclick="document.getElementById('sjGalleryModal').remove()" style="background:rgba(255,255,255,0.2);border:none;color:white;width:36px;height:36px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                </button>`;

            const grid = document.createElement('div');
            grid.style.cssText = 'display:grid;grid-template-columns:repeat(2,1fr);gap:0.75rem;width:100%;max-width:480px;overflow-y:auto;max-height:70vh;';

            files.forEach((f, i) => {
                const wrapper = document.createElement('div');
                wrapper.style.cssText = 'aspect-ratio:1/1;border-radius:12px;overflow:hidden;border:2px solid rgba(255,255,255,0.15);cursor:pointer;';
                wrapper.innerHTML = `<img src="uploads/${f}" style="width:100%;height:100%;object-fit:cover;" alt="Foto SJ ${i + 1}" onerror="this.closest('div').style.display='none'">`;
                wrapper.onclick = () => showImagePopup('uploads/' + f);
                grid.appendChild(wrapper);
            });

            modal.appendChild(header);
            modal.appendChild(grid);
            document.body.appendChild(modal);
        }


        function viewGoodsPhoto(taskId) {
            const task = allTasks.find(t => t.id == taskId);
            if (!task) { showToast('Data tugas tidak ditemukan', 'error'); return; }

            let files = [];

            if (Array.isArray(task.request_goods)) {
                files = files.concat(task.request_goods);
            }

            if (task.goods_file) {
                try {
                    const parsed = JSON.parse(task.goods_file);
                    if (Array.isArray(parsed)) {
                        files = files.concat(parsed);
                    } else {
                        files.push(parsed);
                    }
                } catch (e) {
                    files.push(task.goods_file);
                }
            }

            if (task.photo_file) {
                files.push(task.photo_file);
            }

            files = [...new Set(files)];

            if (files.length === 0) {
                showToast('Tidak ada foto barang', 'error');
                return;
            }

            if (files.length === 1) {
                showImagePopup('uploads/' + files[0]);
            } else {
                showGoodsGallery(files);
            }
        }

        function showGoodsGallery(files) {
            let existing = document.getElementById('goodsGalleryModal');
            if (existing) existing.remove();

            const modal = document.createElement('div');
            modal.id = 'goodsGalleryModal';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:100001;display:flex;align-items:center;justify-content:center;flex-direction:column;padding:1.5rem;backdrop-filter:blur(5px);';
            modal.onclick = (e) => { if (e.target === modal) modal.remove(); };

            const header = document.createElement('div');
            header.style.cssText = 'display:flex;justify-content:space-between;align-items:center;width:100%;max-width:480px;margin-bottom:1rem;';
            header.innerHTML = `
                <span style="color:white;font-weight:800;font-size:1rem;">📦 Foto Barang (${files.length})</span>
                <button onclick="document.getElementById('goodsGalleryModal').remove()" style="background:rgba(255,255,255,0.2);border:none;color:white;width:36px;height:36px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                </button>`;

            const grid = document.createElement('div');
            grid.style.cssText = 'display:grid;grid-template-columns:repeat(2,1fr);gap:0.75rem;width:100%;max-width:480px;overflow-y:auto;max-height:70vh;';

            files.forEach((f, i) => {
                const wrapper = document.createElement('div');
                wrapper.style.cssText = 'aspect-ratio:1/1;border-radius:12px;overflow:hidden;border:2px solid rgba(255,255,255,0.15);cursor:pointer;';
                wrapper.innerHTML = `<img src="uploads/${f}" style="width:100%;height:100%;object-fit:cover;" alt="Foto barang ${i + 1}" onerror="this.closest('div').style.display='none'">`;
                wrapper.onclick = () => showImagePopup('uploads/' + f);
                grid.appendChild(wrapper);
            });

            modal.appendChild(header);
            modal.appendChild(grid);
            document.body.appendChild(modal);
        }


        updateLocation();
        setInterval(updateLocation, 30000); 
    </script>
    <div id="imagePopup" class="modal-overlay" onclick="if(event.target===this)closeImagePopup()"
        style="z-index: 100000; display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); align-items:center; justify-content:center; backdrop-filter:blur(5px);">
        <div
            style="position:relative; max-width:90%; max-height:90%; display:flex; flex-direction:column; align-items:center;">
            <button onclick="closeImagePopup()"
                style="position:absolute; top:-40px; right:0; background:none; border:none; color:white; cursor:pointer;">
                <span class="material-symbols-outlined" style="font-size:32px;">close</span>
            </button>
            <img id="popupImg" src=""
                style="max-width:100%; max-height:80vh; border-radius:12px; box-shadow:0 20px 50px rgba(0,0,0,0.5); object-fit:contain;">
        </div>
    </div>

    <script>
        function showImagePopup(url) {
            const popup = document.getElementById('imagePopup');
            const img = document.getElementById('popupImg');
            if (!url || url.includes('undefined') || url.includes('null')) {
                showToast('Foto tidak tersedia', 'error');
                return;
            }
            img.src = url;
            popup.style.display = 'flex';
        }

        function closeImagePopup() {
            document.getElementById('imagePopup').style.display = 'none';
        }

        function confirmReleaseVehicle() {
            showConfirm(
                'Selesai Pakai Mobil?',
                'Apakah Anda ingin melepas kendaraan ini? Gunakan ini jika Anda sudah kembali ke pool atau salah memilih mobil.',
                releaseVehicle
            );
        }

        async function releaseVehicle() {
            const driver_id = '<?php echo $_SESSION['user_id']; ?>';
            const formData = new FormData();
            formData.append('driver_id', driver_id);

            try {
                const res = await fetch(`${API_URL}?action=release_vehicle`, {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                if (result.success) {
                    showToast('Kendaraan telah dilepaskan', 'success');
                    checkVehicleAssignment();
                }
            } catch (err) {
                showToast('Gagal melepas kendaraan', 'error');
            }
        }
    </script>
    <?php include_once __DIR__ . '/profile_modal.php'; ?>
</body>

</html>