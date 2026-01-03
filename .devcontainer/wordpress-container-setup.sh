echo "✨ Starting WP-Container-Setup Script"

cd /var/www/html || exit

# Global Site Config
SITE_TITLE="Generation-Reports"
PLUGINS="updraftplus"

# Admin User
ADMIN_USER="admin"
ADMIN_PASS="admin"
ADMIN_EMAIL="admin@admin.com"

# Setup WordPress
if [ ! -f wp-config.php ]; then
    echo "🛒 Installing WordPress"
    wp core download --locale=de_DE
    echo "🧪 Configuring WordPress";
    wp config create --dbhost="db" --dbname="wordpress" --dbuser="wp_user" --dbpass="wp_pass";
    echo "- 📖 Configured Database access"
    wp core install --title="$SITE_TITLE" --url="http://localhost:8080" --admin_user="$ADMIN_USER" --admin_email="$ADMIN_EMAIL" --admin_password="$ADMIN_PASS" --skip-email;
    echo "- 🧱 Configured Global Options & Admin User"
    wp plugin install $PLUGINS --activate
    echo "- 🛍️ Installed and activated the following plugins: $PLUGINS"

    echo "✅ Configuration completed."

else
    echo "🍟 WordPress seems to be already configured."
fi

echo "Starting Apache 🏍️"
service apache2 start