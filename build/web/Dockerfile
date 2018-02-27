FROM debian:wheezy
MAINTAINER Benoit Garret

# Install PHP 5.4 & nginx, tools, and cups for ZPL conversion
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y debconf-utils \ 
	nginx php5-fpm php5-common php5-curl php5-mcrypt php5-mysql php5-cli php5-intl php5-memcached php5-gd php-apc \
	texlive-latex-recommended texlive-extra-utils wget unzip nano mysql-client \
	cups 

# Configure php
RUN sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 1024M/' /etc/php5/fpm/php.ini && \
    sed -i 's/post_max_size = 8M/post_max_size = 1024M/' /etc/php5/fpm/php.ini && \
    sed -i 's/memory_limit = 128M/memory_limit = 1024M/' /etc/php5/fpm/php.ini

# Configure nginx
ADD nginx /etc/nginx/sites-available/default
RUN rm /etc/nginx/sites-enabled/default && \
    ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default && \
    mkdir /var/www && \
    chown www-data:www-data /var/www

# Configure cups
RUN update-rc.d cups disable 2 3 4 5 && \
    update-rc.d avahi-daemon disable 2 3 4 5 && \
    update-rc.d saned disable 2 3 4 5

RUN usermod -u 1000 www-data

ADD run.sh /root/run.sh

EXPOSE 80

CMD ["/root/run.sh"]
