#!/bin/bash

clear
echo "=============================="
echo " X-UI Subscription Panel"
echo "=============================="
echo "1) نصب پنل"
echo "2) حذف کامل"
echo "3) خروج"
echo "=============================="
echo -n "گزینه را وارد کنید: "
read choice

case $choice in
    1)
        echo -e "\n🚀 نصب در حال انجام است..."
        apt update -y && apt upgrade -y
        apt install php php-sqlite3 php-pdo apache2 curl -y

        systemctl enable apache2
        systemctl restart apache2

        echo "📥 دریافت اسکریپت..."
        curl -o /var/www/html/status.php https://raw.githubusercontent.com/MrAliDev/xui-subscription-panel/main/status.php

        chown www-data:www-data /var/www/html/status.php
        chmod 644 /var/www/html/status.php

        IP=$(curl -s ifconfig.me)
        echo -e "\n✅ نصب کامل شد!"
        echo -e "🌐 آدرس دسترسی: http://$IP/status.php"
        ;;
    2)
        echo "🧹 در حال حذف پنل..."
        rm -f /var/www/html/status.php
        echo "✅ پنل حذف شد."
        ;;
    3)
        echo "❌ خروج از برنامه."
        exit 0
        ;;
    *)
        echo "⛔ گزینه نامعتبر بود!"
        ;;
esac
