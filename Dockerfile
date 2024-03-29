FROM alpine:latest

ENV FPM_USER      nobody
ENV LOG_FORMAT    main

RUN apk add --no-cache \
  ca-certificates \
  imagemagick \
  nginx \
  php81 \
  php81-curl \
  php81-fpm \
  php81-iconv \
  php81-json \
  php81-mbstring \
  php81-openssl \
  php81-session

RUN mkdir -p /run/nginx

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod 755 /usr/local/bin/docker-entrypoint.sh

COPY ./ng-default.conf /etc/nginx/http.d/default.conf
#COPY ./srv /srv

WORKDIR /srv

EXPOSE 80

ENTRYPOINT [ "docker-entrypoint.sh" ]
CMD [ "/usr/sbin/nginx", "-c", "/etc/nginx/nginx.conf", "-g", "daemon off;" ]
