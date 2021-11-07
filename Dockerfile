FROM php:7.4-cli-alpine

ARG cron
ARG tz
ARG command

RUN apk add bash tzdata

ENV TZ="$tz"

COPY . /var/www/html
WORKDIR /var/www/html

RUN touch crontab.tmp \
    && echo " $cron cd /var/www/html; $command" > crontab.tmp \
    && crontab crontab.tmp \
    && rm -rf crontab.tmp

CMD ["/usr/sbin/crond", "-f", "-d", "0"]