# TMS Warehouse Operation System

Sistem Manajemen Transportasi dan Operasional Gudang (TMS) berbasis PHP Native & MySQL.

## Fitur CI/CD & Hosting Otomatis

Proyek ini telah dikonfigurasi dengan **GitHub Actions** untuk otomatisasi deploy langsung ke hosting InfinityFree (`tmswhoperation.rf.gd`):
- **Setiap kali melakukan `git push` ke branch `main`**, file yang berubah akan otomatis diunggah ke server InfinityFree via FTP.
- **Folder `uploads/` dilindungi**: File foto surat jalan, tanda terima, dan avatar yang ada di server tidak akan pernah tertimpa atau terhapus saat deployment.
- **Database Lama Tetap Aman**: Sistem migrasi otomatis (`database_migration.php`) menggunakan skema non-destruktif (`CREATE TABLE IF NOT EXISTS` dan penambahan kolom bersyarat). Tidak ada perintah `DROP TABLE` atau penghapusan data.

---

## Konfigurasi GitHub Repository Secrets

Agar workflow GitHub Actions dapat login ke FTP InfinityFree, tambahkan Secret berikut di GitHub:
1. Buka repositori GitHub Anda: `https://github.com/dhanielomarthinz-130/tmswhoperation`
2. Klik tab **Settings** -> **Secrets and variables** -> **Actions**
3. Klik **New repository secret**:
   - `FTP_SERVER`: `ftpupload.net`
   - `FTP_USERNAME`: `if0_38464190`
   - `FTP_PASSWORD`: *(Masukkan password akun hosting / vPanel Anda)*
   - `MIGRATION_TOKEN`: `TMS_SECRET_MIGRATE_2026` *(Opsional)*

---

## Pembaruan Database Aman (Safe Migration)

Karena InfinityFree memblokir akses port database MySQL dari luar hosting (komputer lokal atau runner GitHub), pembaruan struktur database dijalankan dari dalam server:
1. **Otomatis melalui Admin Panel**: Buka menu **Kelola Database** (`manage_database.php`), lalu klik tombol **Sinkronisasi Struktur (Aman)**.
2. **Melalui URL langsung (dengan Token)**:
   `http://tmswhoperation.rf.gd/database_migration.php?token=TMS_SECRET_MIGRATE_2026`
