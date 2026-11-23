-- Hak akses MODUL per user (di dalam sistem billing / kas).
--
-- t_akses_sistem menentukan sistem mana yang boleh dibuka user.
-- Tabel ini mempersempit lebih lanjut: modul mana di dalam sistem itu.
--
-- Aturan:
--   * Tidak ada baris untuk sebuah (user, sistem)  -> user bebas membuka
--     SEMUA modul sistem itu (perilaku lama, tidak ada yang berubah).
--   * Ada minimal satu baris                      -> user hanya boleh
--     membuka modul yang terdaftar.
--   * users.level = 'admin' selalu bebas semua modul.
--   * Modul Data Master / Administrasi tetap terikat level admin; baris
--     di tabel ini TIDAK bisa membukanya untuk non-admin.
--
-- Kode modul didefinisikan di App\Helpers\sistem_helper.php -> sistem_modul().
-- Contoh: 'billing.loket', 'billing.laporan.drd_air', 'kas.keluar'.
--
-- Terapkan dengan:
--   podman exec -i mysql-import-db mysql -updam_user -p'#PDAMWater!' db_pdam \
--       < database/t_akses_modul.sql

CREATE TABLE IF NOT EXISTS `t_akses_modul` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `id_session` VARCHAR(100) NOT NULL,
    `sistem`     VARCHAR(20)  NOT NULL,
    `modul`      VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_user_modul` (`id_session`, `modul`),
    KEY `idx_user_sistem` (`id_session`, `sistem`),
    KEY `idx_sistem` (`sistem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
