FROM ubuntu:16.04

MAINTAINER Ferdie De Oliveira

RUN apt-get clean && apt-get update && apt-get install -y locales

RUN locale-gen en_US.UTF-8

ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8

RUN apt-get update \
    && apt-get install -y nginx \
    && mkdir /run/php \ 
    && echo "daemon off;" >> /etc/nginx/nginx.conf \
    && ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

COPY default /etc/nginx/sites-avaliable/default
COPY default /etc/nginx/sites-enabled/default

EXPOSE 80

CMD [ "nginx" ]