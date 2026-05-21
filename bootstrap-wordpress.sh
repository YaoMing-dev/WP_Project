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
    --title="KickZone" \
    --admin_user=admin_kickzone \
    --admin_password='Admin@KZ2024!' \
    --admin_email=admin@kickzone.local \
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

echo "Installing Astra theme..."
wp theme install astra --force --allow-root
wp theme activate kickzone-child --allow-root

echo "Installing plugins..."
wp plugin install woocommerce wordpress-seo contact-form-7 updraftplus w3-total-cache --activate --force --allow-root

echo "Setting language and permalink..."
wp language core install vi --activate --allow-root || true
wp option update WPLANG vi --allow-root
wp option update permalink_structure '/%postname%/' --allow-root
wp option update blogdescription 'Every Step. A Statement.' --allow-root

echo "Applying KickZone demo content..."
wp eval-file /work/wp-run-with-errors.php --allow-root

wp rewrite flush --allow-root
chown -R www-data:www-data /var/www/html/wp-content || true

echo "KickZone WordPress bootstrap completed."
echo "  Site   : http://localhost:8080"
echo "  Admin  : http://localhost:8080/wp-admin"
echo "  User   : admin_kickzone / Admin@KZ2024!"
