#!/usr/bin/env bash
set -euo pipefail

echo "== RUTAS CANÓNICAS LARAVEL =="
echo "storage/app/public -> se sirve como /storage tras 'php artisan storage:link'"
echo

# Dirs a auditar
CANON=("storage/app/public/files" "storage/app/public/files_deleted")
LEGACY=("public/files" "public/files_deleted" "public/publics" "public/public")

audit_dir () {
  local d="$1"
  if [ -d "$d" ]; then
    echo "• $d"
    find "$d" -type f | wc -l | awk '{print "  archivos:",$1}'
    du -sh "$d" 2>/dev/null | awk '{print "  tamaño:  ",$1}'
  else
    echo "• $d  (no existe)"
  fi
}

echo "== CANÓNICO =="
for d in "${CANON[@]}"; do audit_dir "$d"; done
echo
echo "== POSIBLES LUGARES ERRÓNEOS =="
for d in "${LEGACY[@]}"; do audit_dir "$d"; done

echo
echo "== LISTA DE ARCHIVOS FUERA DE LUGAR (candidatos a mover a storage/app/public) =="
for d in "${LEGACY[@]}"; do
  if [ -d "$d" ]; then
    find "$d" -type f -printf "%p\n"
  fi
done

echo
echo "== POSIBLES DUPLICADOS POR NOMBRE ENTRE CANÓNICO Y LEGACY =="
if command -v ggrep >/dev/null 2>&1; then GREP=ggrep; else GREP=grep; fi
comm -12 \
  <(find storage/app/public/files -type f -printf "%f\n" 2>/dev/null | sort -u) \
  <( { for d in "${LEGACY[@]}"; do find "$d" -type f -printf "%f\n" 2>/dev/null; done; } | sort -u ) \
  | sed 's/^/  • /' || true

echo
echo "== SUGERENCIA: SYMLINK =="
echo "php artisan storage:link  # para servir /storage -> storage/app/public"
