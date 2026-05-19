#!/bin/sh
set -eu

cd /var/www/html

echo "Waiting for WordPress files..."
i=0
while [ ! -f wp-load.php ] && [ "$i" -lt 90 ]; do
  i=$((i + 1))
  sleep 2
done

echo "Installing fresh WordPress if needed..."
i=0
until wp core is-installed --allow-root >/dev/null 2>&1; do
  i=$((i + 1))
  if wp core install \
    --url=http://localhost:8080 \
    --title="Blossom Chic Viet" \
    --admin_user=admin_blossom \
    --admin_password='Admin@Blossom2024' \
    --admin_email=admin@blossom.local \
    --skip-email \
    --allow-root >/dev/null 2>&1; then
    break
  fi
  if [ "$i" -ge 90 ]; then
    echo "WordPress install did not complete in time."
    exit 1
  fi
  sleep 2
done

echo "Installing parent theme and plugins..."
wp theme install blossom-feminine --force --allow-root
wp theme activate blossom-chic --allow-root
wp plugin install wordpress-seo contact-form-7 updraftplus w3-total-cache elementor --activate --force --allow-root

echo "Applying Vietnamese language and demo content..."
wp language core install vi --activate --allow-root || true
wp option update WPLANG vi --allow-root
wp option update permalink_structure '/%postname%/' --allow-root
wp eval-file /work/wp-apply-blossom-chic.php --allow-root
wp rewrite flush --allow-root

chown -R www-data:www-data /var/www/html/wp-content || true
echo "Blossom Chic Vietnamese WordPress bootstrap completed."
