#!/bin/sh

# Log output to a file for debugging
echo "$(date): Starting certificate renewal process..." >> /var/log/cron.log

# Renew certificates using Certbot
certbot renew --quiet

# Reload Nginx to apply the new certificates
if [ $? -eq 0 ]; then
  echo "$(date): Certificates renewed successfully. Reloading Nginx..." >> /var/log/cron.log
  nginx -s reload
else
  echo "$(date): Certbot renewal failed." >> /var/log/cron.log
fi