clear
echo "=============================="
echo " X-UI Subscription Panel"
echo "=============================="
echo "1) Install Panel"
echo "2) Uninstall Panel"
echo "3) Exit"
echo "=============================="
echo -n "Choose an option: "
read choice

case $choice in
    1)
        echo -e "\n🚀 Installing..."
        apt update -y && apt upgrade -y
        apt install php php-sqlite3 php-pdo apache2 curl -y

        systemctl enable apache2
        systemctl restart apache2

        echo "📥 Downloading script..."
        curl -o /var/www/html/status.php https://raw.githubusercontent.com/Mirzakochak/xui-status-panel/main/status.php

        chown www-data:www-data /var/www/html/status.php
        chmod 644 /var/www/html/status.php

        IP=$(curl -s ifconfig.me)
        echo -e "\n✅ Installation completed!"
        echo -e "🌐 Access your panel: http://$IP/status.php"
        ;;
    2)
        echo "🧹 Uninstalling..."
        rm -f /var/www/html/status.php
        echo "✅ Panel removed."
        ;;
    3)
        echo "❌ Exiting."
        exit 0
        ;;
    *)
        echo "⛔ Invalid option!"
        ;;
esac
