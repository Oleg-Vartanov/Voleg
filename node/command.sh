#!/bin/sh

# Exit on error
set -e

echo "Generating SSL certificate for ${DOMAIN}..."
mkdir -p /certificates
openssl req -x509 -newkey rsa:4096 -sha256 -days 3650 \
  -nodes -keyout /certificates/${DOMAIN}.key -out /certificates/${DOMAIN}.crt \
  -subj "/CN=${DOMAIN}" -addext "subjectAltName=DNS:${DOMAIN},DNS:*.${DOMAIN},IP:10.0.0.1"

tail -f /dev/null