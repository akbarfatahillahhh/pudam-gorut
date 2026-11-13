# Deploy pudam_ekas (podman + Cloudflare Tunnel)

## Ringkasan

| Item | Nilai |
|---|---|
| App | CodeIgniter 4.2.7, PHP 8.2 + Apache |
| Image | `localhost/pudam-ekas-app:dev` (build dari `Containerfile`) |
| Pod | `pudam-ekas` (port host **8092** -> container 80) |
| Source | bind-mount `/home/reev/project/pudam_ekas` -> `/var/www/html` |
| Public URL | https://pudam.digiagent.web.id |
| Tunnel | `pudam` = `c00e3775-2a63-4f05-9b6e-7dcf9c555d44` |
| TunneI unit | `systemctl --user pudam-cloudflared` |
| DB | container `mysql-import-db` (MySQL 8), host port 3307, db `db_pdam` |

Deploy lama (`mywater.digiagent.web.id`, pod `pudam`, port 8091, source
`/home/reev/pudam`) tetap jalan dan sekarang dilayani dari connector yang sama
(lihat `cloudflared.yml`).

## Perintah

```bash
./deploy.sh up      # build image + composer install + pod + tunnel + health check
./deploy.sh down    # stop pod & tunnel
./deploy.sh logs    # apache log
./deploy.sh tunnel  # restart cloudflared saja
```

Manual, kalau tidak lewat script:

```bash
podman build -t localhost/pudam-ekas-app:dev -f Containerfile .
podman run --rm -v "$PWD":/app:Z -w /app localhost/pudam-ekas-app:dev \
  composer install --no-interaction --prefer-dist
podman play kube pod.yaml --replace
systemctl --user restart pudam-cloudflared
```

## Detail yang penting

- **DB hostname**: dari dalam container, MySQL host = `host.containers.internal`
  (bukan `localhost`). Sudah diset di `.env`.
- **`app.baseURL`** di `.env` = `https://pudam.digiagent.web.id/`. `CI_ENVIRONMENT`
  = `production` (debug toolbar mati, detail error tidak tampil).
- **Root `.htaccess`** sengaja kosong. Isinya dulu potongan `<VirtualHost>` MAMP
  yang dikomentari HTML; Apache membacanya (AllowOverride All) dan menolak dengan
  500. Jangan diisi lagi — aturan rewrite ada di `public/.htaccess`.
- **Docroot**: Apache diarahkan ke `/var/www/html/public` saat build image.
- **Folder tulis** (di-`chown www-data` oleh `docker-entrypoint.sh`):
  `writable/*`, **semua** `app/**/temp` (`tempDir` mpdf di-hardcode per controller
  sebagai `__DIR__ . '/temp'` — ada 6 folder), `perumda/kinerja`, dan folder upload
  di `public/`.
- **Composer** harus dijalankan dengan image app (bukan image `composer:2` polos),
  karena dependency butuh `ext-intl` dan `ext-gd`.
- **Tunnel ingress**: satu connector per tunnel, jadi `cloudflared.yml` memuat
  hostname pudam **dan** mywater. Rule `http_status:404` selalu paling akhir.
- **DNS**: `cloudflared tunnel route dns <uuid-tunnel> pudam.digiagent.web.id`.
  Pakai UUID, karena nama tunnel `pudam` bisa tertukar dengan tunnel `cockpit`
  (keduanya pernah dipasang di akun yang sama).
- **Tabel `t_user_akses`** (hak akses menu) tidak ada di dump `db_pdam`.
  Skemanya ada di `database/t_user_akses.sql`; sudah diterapkan ke DB:
  `podman exec -i mysql-import-db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" db_pdam' < database/t_user_akses.sql`
- **Reboot**: pod dibuat dengan `restartPolicy: always` + `podman-restart.service`
  enabled; unit cloudflared `WantedBy=default.target` dan user `reev` linger=yes.
