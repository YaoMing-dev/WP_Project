#!/bin/sh
cd /var/www/html
wp eval-file /work/wp-run-with-errors.php --allow-root > /tmp/kz-log.txt 2>&1
cat /tmp/kz-log.txt
echo "--- COUNTS ---"
wp eval "echo wp_count_posts('product')->publish . ' products | ' . wp_count_posts('post')->publish . ' posts | ' . wp_count_posts('page')->publish . ' pages';" --allow-root
