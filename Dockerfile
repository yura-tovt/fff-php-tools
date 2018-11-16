FROM alpine:3.7
RUN apk update && \
    apk add bash \
        git \
        curl \
        openssh-client \
        php7 \
        php7-zip \
        php7-openssl \
        php7-json \
        php7-phar \
        php7-iconv \
        php7-zlib \
        php7-tokenizer \
        php7-mbstring \
        php7-xml \
        php7-xmlwriter \
        php7-curl \
        php7-dom \
        php7-xdebug \
        php7-posix \
        php7-ctype && \
    echo zend_extension=xdebug.so > /etc/php7/conf.d/xdebug.ini && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
	php composer-setup.php --install-dir=/usr/bin --filename=composer && \
	php -r "unlink('composer-setup.php');" && \
	composer global require overtrue/phplint && \
	composer global require friendsofphp/php-cs-fixer && \
	composer global require phpunit/phpunit && \
    composer global require laravel/envoy && \
	ln -sf ~/.composer/vendor/bin/phplint /usr/bin/phplint && \
	ln -sf ~/.composer/vendor/bin/php-cs-fixer /usr/bin/php-cs-fixer && \
	ln -sf ~/.composer/vendor/bin/phpunit /usr/bin/phpunit && \
	ln -sf ~/.composer/vendor/bin/envoy /usr/bin/envoy && \
	mkdir build
WORKDIR /build
