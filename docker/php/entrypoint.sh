#!/bin/sh
set -e

# Prevent Laravel from trying to reach a Vite dev server that does not
# exist inside Docker.
rm -f public/hot

# Build front-end assets on first run (or after a clean wipe).
# The bind-mounted volume means the output lands on the host too, so
# subsequent starts skip this step entirely.
if [ ! -d public/build ] || [ -z "$(ls -A public/build 2>/dev/null)" ]; then
    if command -v npm > /dev/null 2>&1; then
        echo "[entrypoint] Building frontend assets..."
        npm run build
    else
        echo "[entrypoint] WARNING: npm not found, skipping frontend build. Run 'npm run build' on the host first."
    fi
fi

exec php-fpm
