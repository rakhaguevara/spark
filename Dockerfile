FROM php:8.2-apache

# Install system dependencies for GD
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set ServerName to suppress warnings
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy application source
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Create symlinks AFTER copying files
RUN cd /var/www/html/public && \
    ln -sf ../assets assets && \
    ln -sf ../config config && \
    ln -sf ../functions functions && \
    ln -sf ../includes includes && \
    ln -sf ../lib lib && \
    ln -sf ../pages pages && \
    ln -sf ../uploads uploads && \
    ln -sf ../api api && \
    ln -sf ../actions actions && \
    ln -sf ../admin admin && \
    ln -sf ../lang lang

# Expose port 80
EXPOSE 80
