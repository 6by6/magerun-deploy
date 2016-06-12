FROM php:5.6

# Install
RUN buildDeps=" \
        libpng12-dev \
        libjpeg-dev \
        libmcrypt-dev \
        libxml2-dev \
        freetype* \
        wget \
    "; \
    set -x \
    && apt-get update \
    && apt-get install -y $buildDeps --no-install-recommends  \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure \
      gd --with-png-dir=/usr --with-jpeg-dir=/usr --with-freetype-dir \
    && docker-php-ext-install \
      gd mbstring mysqli mcrypt mysql pdo_mysql soap zip \
    && apt-get update -qy \
    && apt-get install -qy git-core \
    && cd /tmp/ && git clone https://github.com/derickr/xdebug.git \
    && cd xdebug && phpize && ./configure --enable-xdebug && make \
    && mkdir /usr/lib/php5/ && cp modules/xdebug.so /usr/lib/php5/xdebug.so \
    && touch /usr/local/etc/php/ext-xdebug.ini \
    && rm -r /tmp/xdebug \
    && apt-get purge -y --auto-remove

# Configure PHP
COPY docker/php.ini /usr/local/etc/php/php.ini

# create a workspace
RUN git clone https://github.com/OpenMage/magento-mirror.git ~/workspace

# Install magerun
RUN curl -o magerun https://raw.githubusercontent.com/netz98/n98-magerun/master/n98-magerun.phar && \
    chmod +x ./magerun && \
    cp ./magerun /usr/local/bin/ && \
    rm ./magerun

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


COPY docker/ext-xdebug.ini /usr/local/etc/php/conf.d/ext-xdebug.ini

# install magerun module
#RUN mkdir /root/.n98-magerun/modules/sixbysix-deploy -p
#COPY .  /root/.n98-magerun/modules/sixbysix-deploy/

# Update composer
#WORKDIR /root/.n98-magerun/modules/sixbysix-deploy/
#RUN composer update

COPY docker/tester.sh /root
RUN chmod +x /root/tester.sh




