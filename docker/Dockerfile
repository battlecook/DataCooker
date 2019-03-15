FROM centos

RUN rpm --import http://mirror.centos.org/centos/7/os/x86_64/RPM-GPG-KEY-CentOS-7
RUN yum install -y epel-release
RUN yum install -y wget git

# install php73
RUN wget http://rpms.remirepo.net/enterprise/remi-release-7.rpm
RUN rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-7.rpm
RUN yum install -y --enablerepo=remi-php73 php php-pecl-xdebug php-pdo php-mysqlnd php-pecl-apcu php-pecl-memcache php-pecl-memcached php-pecl-redis

#composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#phpunit
ENV PHPUNIT_PATH="/usr/local/src/phpunit/phpunit"
RUN mkdir -p /usr/local/src/phpunit
RUN wget -O $PHPUNIT_PATH https://phar.phpunit.de/phpunit-8.phar
RUN chmod +x $PHPUNIT_PATH