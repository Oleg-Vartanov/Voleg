#!/bin/sh

# Exit on error
set -eu

# Generate SSL certificates.
if [ ! -f /etc/letsencrypt/live/${DOMAIN}/fullchain.pem ]; then
  echo "Generating SSL certificate for ${DOMAIN}..."

  # Add ACME challenge location to the default config before template is used.
  echo "server {
    listen 80; listen [::]:80; server_name ${DOMAIN} ${DOMAIN_API};
    location /.well-known/acme-challenge/ { root /var/www/certbot; allow all; }
    location / { return 404; }
  }" > /etc/nginx/conf.d/default.conf

  mkdir -p /var/www/certbot
  chown -R nginx:nginx /var/www/certbot
  chmod -R 755 /var/www/certbot

  # Start Nginx in the background
  nginx -g "daemon off;" &

  mkdir -p /var/www/certbot/.well-known/acme-challenge
  touch /var/www/certbot/.well-known/acme-challenge/testfile
  while ! curl -fsS "http://${DOMAIN}/.well-known/acme-challenge/testfile" > /dev/null; do
    echo "Waiting for Nginx to be ready..."
    sleep 2
  done
  rm -f /var/www/certbot/.well-known/acme-challenge/testfile

  certbot certonly --webroot -w /var/www/certbot -d ${DOMAIN} -d ${DOMAIN_API} --email ${CERTBOT_EMAIL} --agree-tos --non-interactive;

  nginx -s stop

  while pgrep -x nginx >/dev/null; do
    echo "Waiting for Nginx to stop..."
    sleep 2
  done
fi;

crond -f & exec /docker-entrypoint.sh "$@"