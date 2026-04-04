#!/bin/sh

# Exit on error
set -e

CERT_PATH="/etc/nginx/certs/${DOMAIN}.crt"
KEY_PATH="/etc/nginx/certs/${DOMAIN}.key"

if [ ! -f "$CERT_PATH" ] || [ ! -f "$KEY_PATH" ]; then
  echo "Generating SSL certificate for ${DOMAIN}..."
  mkdir -p /etc/nginx/certs
  openssl req -x509 -newkey rsa:4096 -sha256 -days 3650 \
    -nodes -keyout "$KEY_PATH" -out "$CERT_PATH" \
    -subj "/CN=${DOMAIN}" \
    -addext "subjectAltName=DNS:${DOMAIN},DNS:*.${DOMAIN},IP:10.0.0.1"
else
  echo "Using existing certificate for ${DOMAIN}"
fi

# Run the original image entrypoint
exec /docker-entrypoint.sh "$@"