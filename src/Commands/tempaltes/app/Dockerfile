FROM ubuntu:16.04

MAINTAINER Ferdie De Oliveira

RUN apt-get clean && apt-get update && apt-get install -y locales

RUN locale-gen en_US.UTF-8

ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8

RUN apt-get update \
    && apt-get install -y nginx curl zip git software-properties-common \
    && add-apt-repository -y ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y php7.2-fpm php7.2-cli php7.2-gd php7.2-mysql \
    php7.2-pgsql php7.2-imap php-memcached php7.2-mbstring php7.2-xml php7.2-curl php7.2-sqlite3 \
    && php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin --filename=composer \
    && mkdir /run/php