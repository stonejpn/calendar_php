FROM php:8.0-apache
RUN cp /usr/share/zoneinfo/Asia/Tokyo /etc/timezone
RUN ln -s /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
RUN ln -s /etc/apache2/mods-available/rewrite.load  /etc/apache2/mods-enabled/rewrite.load
RUN mkdir /app
COPY conf/000-default.conf /etc/apache2/sites-available/000-default.conf
ENV APACHE_LOG_DIR=/app/docker/logs
WORKDIR /app