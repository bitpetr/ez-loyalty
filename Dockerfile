FROM php:8.1-cli-alpine as api-demo
ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/1.5.52/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && install-php-extensions intl gmp mbstring curl json pdo xml iconv @composer
RUN mkdir -p /usr/src/api
VOLUME /usr/src/api/var
COPY . /usr/src/api
WORKDIR /usr/src/api
RUN composer install --optimize-autoloader
RUN php bin/console doctrine:schema:create -e dev
RUN php bin/console hautelook:fixtures:load -n -e dev
WORKDIR /usr/src/api/public
RUN echo "0       *       *       *       *       php /usr/src/api/bin/console app:transaction:release-frozen" >> /var/spool/cron/crontabs/root
COPY ./.docker/docker-php-entrypoint /usr/local/bin/docker-php-entrypoint
RUN chmod +x /usr/local/bin/docker-php-entrypoint
CMD ["-S", "0.0.0.0:8080"]
EXPOSE 8080