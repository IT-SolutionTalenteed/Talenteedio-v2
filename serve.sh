#!/bin/bash
# Lance artisan serve avec les limites PHP correctes pour l'upload de fichiers volumineux
php -c "$(dirname "$0")/php-dev.ini" artisan serve "$@"
