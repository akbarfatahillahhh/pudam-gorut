-- Tabel hak akses menu per user (dipakai App\Controllers\Home::get_sidebar
-- dan App\Controllers\Master\Akun::setting_akses / save_setting_akses).
--
-- Belum ada di dump db_pdam (db_pdam.sql / db_pdam_fixed.sql), jadi dibuat di sini.
-- Struktur mengikuti pemakaian di controller:
--   id_session -> users.id_session (varchar(100))
--   menu       -> JSON array nama menu, mis. ["absensi","data_pegawai"]

CREATE TABLE IF NOT EXISTS `t_user_akses` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `id_session` VARCHAR(100) NOT NULL,
    `menu`       TEXT         NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_id_session` (`id_session`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
