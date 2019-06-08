FROM php:7.3-apache

ARG SSH_PRV_KEY

# install the PHP extensions we need
RUN set -ex; \
  \
  savedAptMark="$(apt-mark showmanual)"; \
  \
  apt-get update; \
  apt-get install -y --no-install-recommends \
  libjpeg-dev \
  libpng-dev \
  libzip-dev \
  ; \
  \
  docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr; \
  docker-php-ext-install gd mysqli opcache zip; \
  \
  # reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
  apt-mark auto '.*' > /dev/null; \
  apt-mark manual $savedAptMark; \
  ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
  | awk '/=>/ { print $3 }' \
  | sort -u \
  | xargs -r dpkg-query -S \
  | cut -d: -f1 \
  | sort -u \
  | xargs -rt apt-mark manual; \
  \
  apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
  rm -rf /var/lib/apt/lists/*

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
  echo 'opcache.memory_consumption=128'; \
  echo 'opcache.interned_strings_buffer=8'; \
  echo 'opcache.max_accelerated_files=4000'; \
  echo 'opcache.revalidate_freq=2'; \
  echo 'opcache.fast_shutdown=1'; \
  echo 'opcache.enable_cli=1'; \
  } > /usr/local/etc/php/conf.d/opcache-recommended.ini
# https://codex.wordpress.org/Editing_wp-config.php#Configure_Error_Logging
RUN { \
  echo 'error_reporting = 4339'; \
  echo 'display_errors = Off'; \
  echo 'display_startup_errors = Off'; \
  echo 'log_errors = On'; \
  echo 'error_log = /var/www/html/wp-content/logs/error.log'; \
  echo 'log_errors_max_len = 1024'; \
  echo 'ignore_repeated_errors = On'; \
  echo 'ignore_repeated_source = Off'; \
  echo 'html_errors = Off'; \
  } > /usr/local/etc/php/conf.d/error-logging.ini

RUN apt-get update && apt-get install -y git logrotate nano

RUN { \
  echo ''; \
  echo '/var/www/html/wp-content/logs/error.log	{'; \
  echo '    size 20M'; \
  echo '    create 0664 www-data www-data'; \
  echo '    missingok'; \
  echo '    rotate 10'; \
  echo '    compress'; \
  echo '    delaycompress'; \
  echo '}'; \
  } >> /etc/logrotate.conf

RUN mkdir -p /root/.ssh && \
  chmod 0700 /root/.ssh && \
  ssh-keyscan github.com > /root/.ssh/known_hosts

# Add the keys and set permissions
RUN echo "$SSH_PRV_KEY" > /root/.ssh/id_rsa && chmod 600 /root/.ssh/id_rsa
RUN git config --global user.email "appconnect.sw@gmail.com"
RUN git config --global user.name "appconnect-sync"

RUN a2enmod rewrite expires

RUN mkdir -p /var/www/html
RUN chown -R www-data:www-data /var/www/html
COPY . /var/www/html
RUN chmod +x /var/www/html/docker/*.sh

EXPOSE 80
ENTRYPOINT ["/var/www/html/docker/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
