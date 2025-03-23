FROM nginx:stable-alpine

RUN apk --no-cache add \
  certbot \
  certbot-nginx \
  bash \
  cronie \
  curl

## Removes requirement of 'nginx' as the first parameter of CMD.
## Source: https://github.com/JonasAlfredsson/docker-nginx-certbot/blob/672c812fa7e9bc165e88c842cd0a0644bc84c79b/src/Dockerfile#L105
# RUN sed -ri '/^if \[ "\$1" = "nginx" \] \|\| \[ "\$1" = "nginx-debug" \]; then$/,${s//if echo "$1" | grep -q "nginx"; then/;b};$q1' /docker-entrypoint.sh