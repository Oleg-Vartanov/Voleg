#!/bin/sh

# Exit on error
set -e

echo "Generating SSL certificate for ${DOMAIN}..."
mkdir -p /etc/nginx/certs
openssl req -x509 -newkey rsa:4096 -sha256 -days 3650 \
  -nodes -keyout /etc/nginx/certs/${DOMAIN}.key -out /etc/nginx/certs/${DOMAIN}.crt \
  -subj "/CN=${DOMAIN}" -addext "subjectAltName=DNS:${DOMAIN},DNS:*.${DOMAIN},IP:10.0.0.1"

# Run the original image entrypoint
exec /docker-entrypoint.sh "$@"