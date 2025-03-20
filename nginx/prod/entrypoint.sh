#!/bin/sh

# Exit on error
set -eu

# Generate SSL certificates.
if [ ! -f /etc/letsencrypt/live/${DOMAIN}/fullchain.pem ]; then
  echo "Generating SSL certificate for ${DOMAIN}..."

  # Add ACME challenge location to the default config before template is used.
  echo "Adding ACME challenge location to default Nginx config..."
  echo "server {
    listen 80; server_name ${DOMAIN} ${DOMAIN_API};
    location /.well-known/acme-challenge/ { root /var/www/certbot; allow all; }
    location / { return 404; }
  }" > /etc/nginx/conf.d/default.conf

  # Start Nginx in the background
  nginx

  # Wait for Nginx to be ready by checking port 80
  until nc -z ${DOMAIN} 80; do
    echo "Waiting for Nginx to be ready..."
    sleep 2
  done

  mkdir -p /var/www/certbot
  chown -R www-data:www-data /var/www/certbot
  certbot certonly --webroot -w /var/www/certbot -d ${DOMAIN} -d ${DOMAIN_API} --email ${CERTBOT_EMAIL} --agree-tos --non-interactive;

  nginx -s stop
fi;

# Starting crone
crond

# Run the original image entrypoint
exec /docker-entrypoint.sh "$@"