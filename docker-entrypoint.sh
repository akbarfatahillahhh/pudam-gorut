#!/bin/sh
set -e

# Source di-bind-mount dari host (dimiliki user host), Apache jalan sebagai www-data.
# Root di dalam container rootless bisa chown ke uid/gid yang ter-map (33 = www-data).
if [ -d /var/www/html/writable ]; then
    mkdir -p /var/www/html/writable/cache /var/www/html/writable/logs \
             /var/www/html/writable/session /var/www/html/writable/debugbar \
             /var/www/html/writable/uploads /var/www/html/writable/mpdf_temp
    chown -R www-data:www-data /var/www/html/writable
fi

# mpdf tempDir di-hardcode per controller: __DIR__ . '/temp' (banyak folder).
# Chown semua folder bernama temp di bawah app/ agar www-data bisa menulis.
find /var/www/html/app -type d -name temp -exec chown -R www-data:www-data {} + 2>/dev/null || true

# Folder upload/artefak yang ditulis controller
for upload_dir in \
    /var/www/html/perumda/kinerja \
    /var/www/html/public/foto_meter \
    /var/www/html/public/bukti_ekinerja \
    /var/www/html/public/bukti_terlampir \
    /var/www/html/public/absen \
    /var/www/html/public/assets/user
do
    mkdir -p "$upload_dir"
    chown -R www-data:www-data "$upload_dir"
done

exec "$@"
