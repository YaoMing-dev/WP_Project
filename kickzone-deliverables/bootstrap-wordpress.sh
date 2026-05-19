#!/bin/sh
set -eu

cd /var/www/html

echo "Waiting for WordPress files..."
i=0
while [ ! -f wp-load.php ] && [ "$i" -lt 90 ]; do
  i=$((i + 1))
  sleep 2
done

if [ ! -f wp-load.php ]; then
  echo "WordPress files were not created in time."
  exit 1
fi

echo "Waiting for imported KickZone database..."
i=0
while ! wp core is-installed --allow-root >/dev/null 2>&1; do
  i=$((i + 1))
  if [ "$i" -ge 90 ]; then
    echo "Database is not installed. Check MySQL init import for kickzone-local-db.sql."
    exit 1
  fi
  sleep 2
done

echo "Installing theme and plugins..."
wp theme install astra --activate --force --allow-root
wp plugin install woocommerce wordpress-seo contact-form-7 updraftplus w3-total-cache elementor --activate --force --allow-root

echo "Applying Vietnamese language and KickZone options..."
wp language core install vi --activate --allow-root || true
wp option update WPLANG vi --allow-root
wp option update woocommerce_coming_soon no --allow-root
wp option update permalink_structure '/%postname%/' --allow-root
wp rewrite flush --allow-root

echo "Fixing WordPress content permissions..."
chown -R www-data:www-data /var/www/html/wp-content || true

echo "KickZone WordPress clone bootstrap completed."
