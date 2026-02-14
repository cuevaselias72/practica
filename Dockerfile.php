FROM php:8.2-apache

# Habilitar módulos de Apache necesarios
RUN a2enmod rewrite
RUN a2enmod headers

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Permitir .htaccess
RUN sed -i 's/<Directory \/var\/www\/>//' /etc/apache2/sites-enabled/000-default.conf && \
    echo '<Directory /var/www/>' >> /etc/apache2/sites-enabled/000-default.conf && \
    echo '    AllowOverride All' >> /etc/apache2/sites-enabled/000-default.conf && \
    echo '    Require all granted' >> /etc/apache2/sites-enabled/000-default.conf && \
    echo '</Directory>' >> /etc/apache2/sites-enabled/000-default.conf
