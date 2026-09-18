<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TMS Warehouse | Enterprise Login</title>
    <link rel="icon" type="image/png" href="favicon.png?v=3">

    <!-- Premium Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --primary-glow: rgba(16, 185, 129, 0.35);
            --secondary: #06b6d4;
            --accent: #6366f1;
            
            --bg-deep: #080d1a;
            --card-bg: rgba(15, 23, 42, 0.72);
            --card-border: rgba(255, 255, 255, 0.1);
            
            --input-bg: rgba(30, 41, 59, 0.65);
            --input-border: rgba(255, 255, 255, 0.1);
            --input-focus-border: #10b981;
            
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --text-muted: #64748b;
        }

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-deep);
            background-image: url('assets/login_bg.png');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: var(--text-main);
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        /* ─── Dark overlay on top of background image ─── */
        .bg-overlay {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background: linear-gradient(
                135deg,
                rgba(8, 13, 26, 0.78) 0%,
                rgba(5, 10, 20, 0.72) 50%,
                rgba(10, 20, 40, 0.80) 100%
            );
        }

        /* Subtle dot grid overlay */
        .bg-grid {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 32px 32px;
            mask-image: radial-gradient(circle at center, black 40%, transparent 90%);
            -webkit-mask-image: radial-gradient(circle at center, black 40%, transparent 90%);
        }

        @keyframes orbFloat {
            0% {
                transform: translate(0, 0) scale(1);
            }
            50% {
                transform: translate(40px, 50px) scale(1.08);
            }
            100% {
                transform: translate(-30px, 30px) scale(0.95);
            }
        }

        /* ─── Modern Glassmorphic Login Card ─── */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            perspective: 1000px;
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid var(--card-border);
            border-radius: 28px;
            padding: 2.75rem 2.25rem 2.25rem;
            box-shadow: 
                0 30px 70px -15px rgba(0, 0, 0, 0.65),
                0 0 40px rgba(16, 185, 129, 0.08),
                inset 0 1px 1px rgba(255, 255, 255, 0.15);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
        }

        /* Top Accent Highlight Bar */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 15%;
            right: 15%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #10b981, #06b6d4, transparent);
            border-radius: 2px;
        }

        /* ─── Header & Branding ─── */
        .brand-header {
            text-align: center;
            margin-bottom: 2.2rem;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.25);
            padding: 0.3rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #34d399;
            letter-spacing: 0.5px;
            margin-bottom: 1.25rem;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.1);
        }

        .badge-dot {
            width: 7px;
            height: 7px;
            background: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 8px #10b981;
            animation: pulseDot 2s ease-in-out infinite;
        }

        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.4); opacity: 0.7; }
        }

        .logo-box {
            position: relative;
            width: 68px;
            height: 68px;
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.95));
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.1rem;
            box-shadow: 
                0 10px 25px -5px rgba(0, 0, 0, 0.4),
                0 0 25px rgba(16, 185, 129, 0.2);
            transition: all 0.3s ease;
        }

        .logo-box:hover {
            transform: translateY(-2px) scale(1.03);
            border-color: rgba(16, 185, 129, 0.4);
            box-shadow: 
                0 14px 30px -5px rgba(0, 0, 0, 0.5),
                0 0 35px rgba(16, 185, 129, 0.35);
        }

        .logo-box img {
            width: 44px;
            height: 44px;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.3));
        }

        .brand-header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.85rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.5px;
            margin-bottom: 0.35rem;
            line-height: 1.2;
        }

        .brand-header h1 .brand-highlight {
            background: linear-gradient(135deg, #34d399 0%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-header p {
            font-size: 0.86rem;
            color: var(--text-sub);
            font-weight: 400;
        }

        /* ─── Alerts & Banners ─── */
        .error-alert {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fca5a5;
            padding: 0.85rem 1rem;
            border-radius: 14px;
            font-size: 0.84rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            display: none;
            align-items: center;
            gap: 0.75rem;
            backdrop-filter: blur(12px);
            animation: form-shake 0.4s ease-in-out;
            line-height: 1.4;
        }

        @keyframes form-shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-6px); }
            40% { transform: translateX(6px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(4px); }
        }

        .error-alert .material-symbols-outlined {
            font-size: 22px;
            color: inherit;
            flex-shrink: 0;
        }

        /* ─── Form Elements ─── */
        .form-group {
            margin-bottom: 1.35rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: 'Outfit', sans-serif;
            font-size: 0.78rem;
            font-weight: 600;
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 0.55rem;
        }

        .input-container {
            position: relative;
            display: flex;
            align-items: center;
            border-radius: 16px;
            border: 1.5px solid var(--input-border);
            background: var(--input-bg);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .input-container:hover {
            border-color: rgba(255, 255, 255, 0.2);
            background: rgba(30, 41, 59, 0.8);
        }

        .input-container.focused {
            border-color: var(--input-focus-border);
            background: rgba(30, 41, 59, 0.95);
            box-shadow: 
                0 0 0 4px rgba(16, 185, 129, 0.2),
                0 6px 20px rgba(16, 185, 129, 0.12);
        }

        .input-icon {
            position: absolute;
            left: 1.05rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 20px;
            transition: all 0.25s ease;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input-container.focused .input-icon {
            color: #34d399;
            transform: translateY(-50%) scale(1.1);
        }

        input {
            width: 100%;
            height: 52px;
            padding: 0 1rem 0 3.1rem;
            background: transparent;
            border: none;
            outline: none !important;
            box-shadow: none !important;
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
            border-radius: 16px;
            -webkit-appearance: none;
            appearance: none;
        }

        input::placeholder {
            color: #64748b;
            font-weight: 400;
        }

        /* Fix Autofill background styles in dark mode */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #172033 inset !important;
            -webkit-text-fill-color: #f8fafc !important;
            transition: background-color 5000s ease-in-out 0s;
            border-radius: 16px;
        }

        input.has-toggle {
            padding-right: 3.2rem;
        }

        .toggle-pwd-btn {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.04);
            border: none;
            color: #64748b;
            cursor: pointer;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            outline: none !important;
        }

        .toggle-pwd-btn:hover {
            color: #f8fafc;
            background: rgba(255, 255, 255, 0.1);
        }

        .toggle-pwd-btn .material-symbols-outlined {
            font-size: 20px;
        }

        /* ─── Submit Button ─── */
        .btn-submit {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #06b6d4 100%);
            color: #ffffff;
            border: none;
            border-radius: 16px;
            font-family: 'Outfit', sans-serif;
            font-size: 1.02rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 0.75rem;
            box-shadow: 
                0 10px 25px -4px rgba(16, 185, 129, 0.45),
                0 0 20px rgba(6, 182, 212, 0.2);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            position: relative;
            overflow: hidden;
        }

        /* Hover Shimmer Effect */
        .btn-submit::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
            transform: skewX(-20deg);
            transition: 0.6s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 
                0 14px 32px -4px rgba(16, 185, 129, 0.55),
                0 0 30px rgba(6, 182, 212, 0.35);
        }

        .btn-submit:hover::after {
            left: 150%;
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit .btn-arrow {
            transition: transform 0.2s ease;
            font-size: 20px;
        }

        .btn-submit:hover .btn-arrow {
            transform: translateX(4px);
        }

        .btn-submit:disabled {
            opacity: 0.75;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        /* ─── Security Badge & Footer ─── */
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            margin-top: 1.75rem;
            font-size: 0.74rem;
            color: #64748b;
            font-weight: 500;
        }

        .security-badge .material-symbols-outlined {
            font-size: 16px;
            color: #34d399;
        }

        .copyright-footer {
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.07);
            text-align: center;
            font-size: 0.75rem;
            color: #64748b;
            line-height: 1.5;
        }

        .copyright-footer .powered-by {
            font-size: 0.71rem;
            color: #475569;
            margin-top: 0.25rem;
        }

        @media (max-width: 480px) {
            body {
                padding: 1rem;
            }

            .login-card {
                padding: 2.25rem 1.5rem 1.75rem;
                border-radius: 22px;
            }

            .brand-header h1 {
                font-size: 1.65rem;
            }

            .mesh-orb-1, .mesh-orb-2 {
                opacity: 0.3;
            }
        }
    </style>
</head>

<body>
    <!-- Background overlay layers (pointer-events:none so inputs work) -->
    <div class="bg-overlay"></div>
    <div class="bg-grid"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="brand-header">
                <div class="logo-box">
                    <img src="assets/favicon_d.jpg" alt="D Logo" style="width:52px;height:52px;border-radius:12px;object-fit:cover;">
                </div>

                <h1>TMS <span class="brand-highlight">Warehouse</span></h1>
                <p>Silakan masuk ke akun operasional Anda</p>
            </div>

            <!-- Error / Notification Alert -->
            <div id="errorMessage" class="error-alert">
                <span class="material-symbols-outlined" id="errorIcon">warning</span>
                <span id="errorText">Gagal melakukan verifikasi masuk</span>
            </div>

            <form id="loginForm">
                <div class="form-group">
                    <label class="form-label" for="usernameInput">
                        <span>Username</span>
                    </label>
                    <div class="input-container" id="usernameWrapper">
                        <span class="material-symbols-outlined input-icon">account_circle</span>
                        <input type="text" id="usernameInput" name="username" placeholder="Masukkan username" required
                            autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="passwordInput">
                        <span>Password</span>
                    </label>
                    <div class="input-container" id="passwordWrapper">
                        <span class="material-symbols-outlined input-icon">lock</span>
                        <input type="password" id="passwordInput" class="has-toggle" name="password"
                            placeholder="Masukkan password" required autocomplete="current-password">
                        <button type="button" class="toggle-pwd-btn" onclick="togglePass()"
                            aria-label="Tampilkan atau sembunyikan password">
                            <span class="material-symbols-outlined" id="toggleIcon">visibility</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span>Masuk ke Sistem</span>
                    <span class="material-symbols-outlined btn-arrow">arrow_forward</span>
                </button>
            </form>

            <div class="security-badge">
                <span class="material-symbols-outlined">lock</span>
                <span>Protected by 256-Bit SSL Encryption</span>
            </div>

            <div class="copyright-footer">
                <div>Transportation &amp; Warehouse Management System</div>
                <div class="powered-by">
                    &copy; 2026 Dhanielo-Marthinz IMS &bull; All Rights Reserved
                </div>
            </div>
        </div>
    </div>

    <script>
        // ─── Input Styling Focused State ───
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            const container = input.parentElement;
            input.addEventListener('focus', () => {
                container.classList.add('focused');
            });
            input.addEventListener('blur', () => {
                container.classList.remove('focused');
            });
        });

        // ─── Show/Hide Password Toggle ───
        function togglePass() {
            const pwd = document.getElementById('passwordInput');
            const icon = document.getElementById('toggleIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                pwd.type = 'password';
                icon.textContent = 'visibility';
            }
        }

        // ─── Session Status Checker (URL Parameters) ───
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const errDiv = document.getElementById('errorMessage');
            const errText = document.getElementById('errorText');
            const errIcon = document.getElementById('errorIcon');
            
            if (urlParams.get('timeout')) {
                errText.textContent = 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan login kembali.';
                errDiv.style.display = 'flex';
                errDiv.style.background = 'rgba(245, 158, 11, 0.12)';
                errDiv.style.border = '1px solid rgba(245, 158, 11, 0.4)';
                errDiv.style.color = '#fcd34d';
                errIcon.textContent = 'timer';
            } else if (urlParams.get('inactive')) {
                errText.textContent = 'Akun Anda dinonaktifkan oleh Administrator. Silakan hubungi Admin untuk bantuan.';
                errDiv.style.display = 'flex';
                errDiv.style.background = 'rgba(239, 68, 68, 0.12)';
                errDiv.style.border = '1px solid rgba(239, 68, 68, 0.4)';
                errDiv.style.color = '#fca5a5';
                errIcon.textContent = 'block';
            } else if (urlParams.get('expired')) {
                errText.textContent = 'Akun Anda telah kadaluarsa. Silakan hubungi Management untuk perpanjangan akun.';
                errDiv.style.display = 'flex';
                errDiv.style.background = 'rgba(239, 68, 68, 0.12)';
                errDiv.style.border = '1px solid rgba(239, 68, 68, 0.4)';
                errDiv.style.color = '#fca5a5';
                errIcon.textContent = 'event_busy';
            } else if (urlParams.get('maintenance')) {
                errText.innerHTML = '<strong>Mode Maintenance Aktif:</strong> Sistem sedang dalam pemeliharaan. Hanya akun Teknisi (Daniel Imsula) yang diizinkan masuk.';
                errDiv.style.display = 'flex';
                errDiv.style.background = 'rgba(245, 158, 11, 0.15)';
                errDiv.style.border = '1px solid rgba(245, 158, 11, 0.45)';
                errDiv.style.color = '#fbbf24';
                errIcon.textContent = 'engineering';
            }
        });

        // ─── AJAX Form Login Request ───
        document.getElementById('loginForm').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const errDiv = document.getElementById('errorMessage');
            const errText = document.getElementById('errorText');
            const errIcon = document.getElementById('errorIcon');
            const origHtml = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined" style="animation:spin 1s linear infinite;">progress_activity</span><span>Memverifikasi...</span>';
            errDiv.style.display = 'none';

            try {
                const res = await fetch('./api.php?action=login', { method: 'POST', body: new FormData(e.target) });

                if (!res.ok) throw new Error('HTTP status ' + res.status);
                const data = await res.json();

                if (data.success) {
                    btn.innerHTML = '<span class="material-symbols-outlined">check_circle</span><span>Berhasil! Mengalihkan...</span>';
                    btn.style.background = 'linear-gradient(135deg, #059669 0%, #10b981 100%)';
                    btn.style.boxShadow = '0 0 30px rgba(16, 185, 129, 0.6)';

                    setTimeout(() => {
                        if (data.is_web) {
                            window.location.href = './';
                        } else {
                            window.location.href = 'mobile_home.php';
                        }
                    }, 700);
                } else {
                    errText.textContent = data.message || 'Username atau password salah.';
                    errIcon.textContent = 'warning';
                    errDiv.style.display = 'flex';
                    errDiv.style.background = 'rgba(239, 68, 68, 0.12)';
                    errDiv.style.border = '1px solid rgba(239, 68, 68, 0.35)';
                    errDiv.style.color = '#fca5a5';

                    // Trigger alert shake animation
                    errDiv.style.animation = 'none';
                    errDiv.offsetHeight; /* trigger reflow */
                    errDiv.style.animation = 'form-shake 0.4s ease-in-out';

                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }
            } catch (err) {
                console.error(err);
                errText.textContent = 'Koneksi gagal. Periksa jaringan atau server Anda.';
                errIcon.textContent = 'wifi_off';
                errDiv.style.display = 'flex';
                errDiv.style.background = 'rgba(239, 68, 68, 0.12)';
                errDiv.style.border = '1px solid rgba(239, 68, 68, 0.35)';
                errDiv.style.color = '#fca5a5';

                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        };

        // Helper styles for button loading spinner
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin { 100% { transform: rotate(360deg); } }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>