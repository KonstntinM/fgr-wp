FROM php:8.3-apache

# Enable PHP Dev mode
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" && \
    echo 'memory_limit = 512M' > /usr/local/etc/php/conf.d/docker-php-memlimit.ini && \
    echo 'upload_max_filesize = 64M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini && \
    echo 'post_max_size = 64M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

# System Setup (Copied from https://github.com/docker-library/wordpress/blob/c82afd7240879748c5e4a64e5fb04e2d34172686/latest/php8.3/apache/Dockerfile)
# persistent dependencies
RUN set -eux; \
	apt-get update; \
	apt-get install -y --no-install-recommends \
# Ghostscript is required for rendering PDF previews
		ghostscript \
	; \
	rm -rf /var/lib/apt/lists/*

# install the PHP extensions we need (https://make.wordpress.org/hosting/handbook/handbook/server-environment/#php-extensions)
RUN set -ex; \
	\
	savedAptMark="$(apt-mark showmanual)"; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		libavif-dev \
		libfreetype6-dev \
		libicu-dev \
		libjpeg-dev \
		libmagickwand-dev \
		libpng-dev \
		libwebp-dev \
		libzip-dev \
	; \
	\
	docker-php-ext-configure gd \
		--with-avif \
		--with-freetype \
		--with-jpeg \
		--with-webp \
	; \
	docker-php-ext-install -j "$(nproc)" \
		bcmath \
		exif \
		gd \
		intl \
		mysqli \
		zip \
	; \
# https://pecl.php.net/package/imagick
	pecl install imagick-3.8.1; \
	docker-php-ext-enable imagick; \
	rm -r /tmp/pear; \
	\
# some misbehaving extensions end up outputting to stdout 🙈 (https://github.com/docker-library/wordpress/issues/669#issuecomment-993945967)
	out="$(php -r 'exit(0);')"; \
	[ -z "$out" ]; \
	err="$(php -r 'exit(0);' 3>&1 1>&2 2>&3)"; \
	[ -z "$err" ]; \
	\
	extDir="$(php -r 'echo ini_get("extension_dir");')"; \
	[ -d "$extDir" ]; \
# reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
	apt-mark auto '.*' > /dev/null; \
	apt-mark manual $savedAptMark; \
	ldd "$extDir"/*.so \
		| awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 1) { next }; gsub("^/(usr/)?", "", so); printf "*%s\n", so }' \
		| sort -u \
		| xargs -r dpkg-query --search \
		| cut -d: -f1 \
		| sort -u \
		| xargs -rt apt-mark manual; \
	\
	apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
	rm -rf /var/lib/apt/lists/*; \
	\
	! { ldd "$extDir"/*.so | grep 'not found'; }; \
# check for output like "PHP Warning:  PHP Startup: Unable to load dynamic library 'foo' (tried: ...)
	err="$(php --version 3>&1 1>&2 2>&3)"; \
	[ -z "$err" ]

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
# enabled by default in the php images: https://github.com/docker-library/php/pull/1587
RUN set -eux; \
	{ \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=2'; \
	} > "$PHP_INI_DIR/conf.d/opcache-recommended.ini"

# Install WordPress CLI \
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

# Install System Extensions
    RUN apt-get update; \
      apt-get install -y --no-install-recommends ghostscript mariadb-client webp libavif-dev openssl curl;

# Grant access to relevant folders
RUN chown -R www-data:www-data /var/www/html
RUN chmod g+w -R /var/www/html/

# Create a development user and grant access
RUN useradd -m -u 1000 developer && usermod -aG www-data developer

# Allow rewrites
RUN a2enmod rewrite

# Listen on both port 80 and 8080 to enable internal calls
RUN echo "Listen 8080" >> /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost *:80 *:8080>/' /etc/apache2/sites-available/000-default.conf