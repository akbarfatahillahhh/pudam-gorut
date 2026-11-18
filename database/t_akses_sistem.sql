-- Hak akses sistem per user: billing vs kas (E-Kas).
--
-- Pengganti t_user_akses (yang menyimpan satu JSON menu per user).
-- Sekarang akses ditentukan per sistem; isi menu sudah tetap per sistem
-- (lihat App\Helpers\sistem_helper.php -> sistem_menu()).
--
-- Modul Data Master TIDAK perlu baris di sini: hanya level admin
-- (users.level = 'admin') dan muncul di dalam sistem yang dibuka.
--
-- User tanpa baris di tabel ini memakai fallback level:
--   admin      -> billing + kas
--   level lain -> billing
--
-- Terapkan dengan:
--   podman exec -i mysql-import-db mysql -updam_user -p'#PDAMWater!' db_pdam \
--       < database/t_akses_sistem.sql

CREATE TABLE IF NOT EXISTS `t_akses_sistem` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `id_session` VARCHAR(100) NOT NULL,
    `sistem`     VARCHAR(20)  NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_user_sistem` (`id_session`, `sistem`),
    KEY `idx_sistem` (`sistem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Pindahkan akses lama: user yang dulu punya menu kas di t_user_akses
-- dianggap boleh masuk E-Kas.
--
-- Hanya dijalankan kalau t_user_akses masih ada.
SET @ada_lama := (
    SELECT COUNT(*) FROM information_schema.tables
    WHERE table_schema = DATABASE() AND table_name = 't_user_akses'
);

SET @sql := IF(
    @ada_lama > 0,
    'INSERT IGNORE INTO `t_akses_sistem` (`id_session`, `sistem`)
     SELECT DISTINCT `id_session`, ''kas''
     FROM `t_user_akses`
     WHERE `menu` LIKE ''%kas_%'' OR `menu` LIKE ''%persediaan%'' OR `menu` LIKE ''%logistik%''',
    'DO 0'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Semua user yang punya akses kas otomatis dapat billing juga
-- (dulu satu menu gabungan, sekarang dua sistem terpisah).
INSERT IGNORE INTO `t_akses_sistem` (`id_session`, `sistem`)
SELECT `id_session`, 'billing' FROM `t_akses_sistem` WHERE `sistem` = 'kas';
