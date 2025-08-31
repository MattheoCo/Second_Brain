#!/usr/bin/env sh
set -eu

# Wait for Postgres if DATABASE_URL is set to postgres
if echo "${DATABASE_URL:-}" | grep -qE '^postgres'; then
  echo "Waiting for Postgres..."
  ATTEMPTS=0
  until php -r 'try{$d=parse_url(getenv("DATABASE_URL"));$dsn=sprintf("pgsql:host=%s;port=%s;dbname=%s",$d["host"],$d["port"]??5432,trim($d["path"],"/"));new PDO($dsn,$d["user"],$d["pass"],[PDO::ATTR_TIMEOUT=>1]);echo "ok\n";exit(0);}catch(Exception $e){exit(1);}'; do
    ATTEMPTS=$((ATTEMPTS+1))
    if [ "$ATTEMPTS" -gt 30 ]; then echo "DB not ready"; exit 1; fi
    sleep 2
  done
fi

# Install deps
if [ ! -d vendor ]; then
  composer install --no-interaction --prefer-dist --no-progress
else
  composer dump-autoload --no-interaction
fi

# Warmup cache (ignore failures in dev)
php bin/console cache:warmup || true

# Apply DB schema
if [ -f bin/console ]; then
  PLATFORM=$(php -r 'echo (new PDO(getenv("DATABASE_URL") ? (function(){ $d=parse_url(getenv("DATABASE_URL")); return sprintf("pgsql:host=%s;port=%s;dbname=%s",$d["host"],$d["port"]??5432,trim($d["path"],"/")); })() : "sqlite::memory:"))->getAttribute(PDO::ATTR_DRIVER_NAME);' 2>/dev/null || true)
  # If using Postgres, the historical migrations are SQLite-specific.
  # Use schema:update to create the schema, then mark migrations as executed.
  if echo "${DATABASE_URL:-}" | grep -qE '^postgres'; then
    php bin/console doctrine:schema:update --force --no-interaction || true
    php bin/console doctrine:migrations:version --add --all --no-interaction || true
  else
    php bin/console doctrine:migrations:migrate --no-interaction || true
  fi
fi

# Compile asset map (safe to run in prod so assets are available under public/assets)
if [ -f bin/console ]; then
  php bin/console asset-map:compile || true
fi

# Dev convenience: expose raw styles under /styles without a build step
if [ -d assets/styles ]; then
  mkdir -p public
  if [ ! -e public/styles ]; then
    ln -s ../assets/styles public/styles || cp -r assets/styles public/styles
  fi
fi

# Start PHP built-in server for simplicity (honor platform PORT if present)
PORT_TO_BIND="${PORT:-8080}"
exec php -S 0.0.0.0:"$PORT_TO_BIND" -t public
