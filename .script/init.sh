apt-get install -y php8.2-sqlite3

cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/bin --filename=composer


apt install -y curl
curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | sudo -E bash
apt install -y symfony-cli

# pull

chmod +x deploy.sh

sudo apt install keepassxc

composer dump-env prod

