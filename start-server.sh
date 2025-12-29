#!/bin/bash
# Helpdesk System Startup Script

echo "================================"
echo "Starting Helpdesk System..."
echo "================================"

# Start MySQL if not running
if ! pgrep -x mysqld > /dev/null; then
    echo "Starting MySQL..."
    mkdir -p /var/run/mysqld
    chown mysql:mysql /var/run/mysqld
    /usr/sbin/mysqld --user=mysql --datadir=/var/lib/mysql --pid-file=/var/run/mysqld/mysqld.pid --socket=/var/run/mysqld/mysqld.sock &
    sleep 5
    echo "MySQL started"
else
    echo "MySQL is already running"
fi

# Start PHP Development Server
echo "Starting PHP Development Server on port 8080..."
pkill -f "php -S"
cd /home/user/helpdesk
php -S 0.0.0.0:8080 -t /home/user/helpdesk > /tmp/php-server.log 2>&1 &
sleep 2

echo "================================"
echo "✓ Helpdesk System is now running!"
echo "================================"
echo ""
echo "Access the application at:"
echo "  http://localhost:8080/login.html"
echo ""
echo "Test credentials:"
echo "  Email: admin@jpbdselangor.gov.my"
echo "  Password: admin123"
echo ""
echo "Server logs: /tmp/php-server.log"
echo "================================"
