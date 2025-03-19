#!/bin/sh

# Exit on error
set -e

# Starting crone
crond

if [ ! -f /etc/letsencrypt/live/${DOMAIN}/fullchain.pem ]; then
  echo "Generating SSL certificate for ${DOMAIN}..."
  certbot certonly --webroot -w /var/www/certbot -d ${DOMAIN} -d ${DOMAIN_API} --email ${CERTBOT_EMAIL} --agree-tos --non-interactive;
fi;

# Run the original image entrypoint
exec /docker-entrypoint.sh "$@"