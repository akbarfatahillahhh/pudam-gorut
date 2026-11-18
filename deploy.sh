# Jalankan stack pudam_ekas (CodeIgniter 4) di podman rootless.
#
#   ./deploy.sh up      # build image, (re)create pod, install deps, health check
#   ./deploy.sh down    # stop + hapus pod
#   ./deploy.sh logs    # log apache container
#   ./deploy.sh tunnel  # (re)start cloudflared untuk tunnel pudam
#
# DB: memakai container mysql-import yang sudah ada (host port 3307),
# diakses dari dalam container lewat host.containers.internal.

set -euo pipefail

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
POD=pudam-ekas
APP=pudam-ekas-app
IMAGE=localhost/pudam-ekas-app:dev
HOST_PORT=8092
UNIT=pudam-cloudflared.service

composer_install() {
    podman run --rm -v "$DIR":/app:Z -w /app "$IMAGE" \
        composer install --no-interaction --no-progress --prefer-dist
}

up() {
    podman build -t "$IMAGE" -f "$DIR/Containerfile" "$DIR"
    composer_install

    podman pod exists "$POD" && podman pod rm -f "$POD"
    podman play kube "$DIR/pod.yaml" --replace

    echo "menunggu http://localhost:$HOST_PORT ..."
    for _ in $(seq 1 30); do
        code=$(curl -s -o /dev/null -w '%{http_code}' "http://localhost:$HOST_PORT/" || true)
        if [ "$code" != "000" ] && [ -n "$code" ]; then
            echo "OK: HTTP $code"
            break
        fi
        sleep 1
    done

    systemctl --user restart "$UNIT"
    echo "selesai. https://pudam.digiagent.web.id/"
}

down() {
    podman pod rm -f "$POD"
    systemctl --user stop "$UNIT" || true
}

case "${1:-up}" in
    up)     up ;;
    down)   down ;;
    logs)   podman logs --tail 100 -f ${POD}-app ;;
    tunnel) systemctl --user restart "$UNIT" && systemctl --user status "$UNIT" --no-pager -l | head -8 ;;
    *)      echo "usage: $0 [up|down|logs|tunnel]" >&2; exit 1 ;;
esac
