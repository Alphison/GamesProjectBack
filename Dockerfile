# Используем официальный образ PHP с FPM
FROM php:8.2-fpm

# Устанавливаем необходимые расширения PHP
RUN docker-php-ext-install pdo_mysql

# Устанавливаем composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Копируем исходный код приложения в контейнер
COPY . .

# Устанавливаем зависимости
RUN composer install --no-dev --optimize-autoloader

# Устанавливаем права на запись для папок storage и bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache