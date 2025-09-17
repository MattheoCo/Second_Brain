#!/usr/bin/env sh
set -eu

# Build/normalize DATABASE_URL for PaaS (e.g., Railway) and wait for Postgres

# 1) If DATABASE_URL is empty or not postgres, try to construct it from PG* env vars (Railway provides these)
if [ -z "${DATABASE_URL:-}" ] || ! echo "${DATABASE_URL:-}" | grep -qE '^postgres'; then
  # Gather possible env var names from PaaS providers
  _HOST="${PGHOST:-${POSTGRES_HOST:-${DB_HOST:-}}}"
  _PORT="${PGPORT:-${POSTGRES_PORT:-${DB_PORT:-5432}}}"
  _USER="${PGUSER:-${POSTGRES_USER:-${DB_USER:-}}}"
  _PASS="${PGPASSWORD:-${POSTGRES_PASSWORD:-${DB_PASSWORD:-}}}"
  _DB="${PGDATABASE:-${POSTGRES_DB:-${DB_NAME:-}}}"
  if [ -n "${_HOST}" ] && [ -n "${_USER}" ] && [ -n "${_DB}" ]; then
    if [ -n "${_PASS}" ]; then
      export DATABASE_URL="postgresql://${_USER}:${_PASS}@${_HOST}:${_PORT}/${_DB}"
    else
      export DATABASE_URL="postgresql://${_USER}@${_HOST}:${_PORT}/${_DB}"
    fi
    unset _HOST _PORT _USER _PASS _DB
  fi
fi

# 2) If DATABASE_URL is postgres, ensure required query params exist (serverVersion, sslmode, charset)
if echo "${DATABASE_URL:-}" | grep -qE '^postgres'; then
  # Append serverVersion if missing
  if ! echo "${DATABASE_URL}" | grep -q 'serverVersion='; then
    SV="${POSTGRES_VERSION:-16}"
    SEP='?'; echo "${DATABASE_URL}" | grep -q '?' && SEP='&'
    DATABASE_URL="${DATABASE_URL}${SEP}serverVersion=${SV}"
  fi
  # Append sslmode if missing (most managed DBs require TLS)
  if ! echo "${DATABASE_URL}" | grep -q 'sslmode='; then
    SEP='?'; echo "${DATABASE_URL}" | grep -q '?' && SEP='&'
    DATABASE_URL="${DATABASE_URL}${SEP}sslmode=${SSL_MODE:-require}"
  fi
  # Append charset if missing
  if ! echo "${DATABASE_URL}" | grep -q 'charset='; then
    SEP='?'; echo "${DATABASE_URL}" | grep -q '?' && SEP='&'
    DATABASE_URL="${DATABASE_URL}${SEP}charset=utf8"
  fi
  export DATABASE_URL
fi

# Wait for Postgres if DATABASE_URL is postgres
if echo "${DATABASE_URL:-}" | grep -qE '^postgres'; then
  echo "Waiting for Postgres..."
  ATTEMPTS=0
  until php -r '
    try {
      $url = getenv("DATABASE_URL");
      $d = parse_url($url);
      parse_str(parse_url($url, PHP_URL_QUERY) ?: "", $q);
      $ssl = isset($q["sslmode"]) ? ";sslmode=".$q["sslmode"] : "";
      $dsn = sprintf("pgsql:host=%s;port=%s;dbname=%s%s", $d["host"], $d["port"] ?? 5432, trim($d["path"], "/"), $ssl);
      new PDO($dsn, $d["user"] ?? null, $d["pass"] ?? null, [PDO::ATTR_TIMEOUT => 1]);
      echo "ok\n"; exit(0);
    } catch (Exception $e) { exit(1); }
  ' ; do
    ATTEMPTS=$((ATTEMPTS+1))
    if [ "$ATTEMPTS" -gt 30 ]; then echo "DB not ready"; exit 1; fi
    sleep 2
  done
fi

# In production, enforce Postgres URL to avoid unintended MySQL drivers on PaaS
if [ "${APP_ENV:-prod}" = "prod" ]; then
  if ! echo "${DATABASE_URL:-}" | grep -qE '^postgres'; then
    echo "ERROR: DATABASE_URL must start with postgresql:// and include serverVersion & sslmode (e.g. postgresql://...?...serverVersion=16&sslmode=require&charset=utf8)"
    echo "Current DATABASE_URL: ${DATABASE_URL:-<empty>}"
    exit 1
  fi
fi

# Install deps (avoid auto-scripts in prod to prevent premature cache:clear)
if [ "${APP_ENV:-prod}" = "prod" ]; then
  composer install --no-interaction --prefer-dist --no-progress --no-dev --no-scripts
else
  if [ ! -d vendor ]; then
    composer install --no-interaction --prefer-dist --no-progress
  else
    composer dump-autoload --no-interaction
  fi
fi

# Warmup cache (be strict in prod)
if [ "${APP_ENV:-prod}" = "prod" ]; then
  php bin/console cache:clear --no-warmup || true
  php bin/console cache:warmup
else
  php bin/console cache:warmup || true
fi

# Apply DB schema
if [ -f bin/console ]; then
  PLATFORM=$(php -r 'echo (new PDO(getenv("DATABASE_URL") ? (function(){ $d=parse_url(getenv("DATABASE_URL")); return sprintf("pgsql:host=%s;port=%s;dbname=%s",$d["host"],$d["port"]??5432,trim($d["path"],"/")); })() : "sqlite::memory:"))->getAttribute(PDO::ATTR_DRIVER_NAME);' 2>/dev/null || true)
  # If using Postgres, the historical migrations are SQLite-specific.
  # Use schema:update to create the schema, then mark migrations as executed.
  if echo "${DATABASE_URL:-}" | grep -qE '^postgres'; then
  php bin/console doctrine:schema:update --force --no-interaction || true
  php bin/console doctrine:migrations:sync-metadata-storage --no-interaction || true
  php bin/console doctrine:migrations:version --add --all --no-interaction || true
  else
    php bin/console doctrine:migrations:migrate --no-interaction || true
  fi
fi

# Compile asset map (safe to run in prod so assets are available under public/assets)
if [ -f bin/console ]; then
  php bin/console importmap:install --no-interaction || true
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
# Ensure session path exists so CSRF/session can persist
mkdir -p var/sessions/"${APP_ENV:-prod}"
exec php -S 0.0.0.0:"$PORT_TO_BIND" -t public
