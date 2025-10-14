#!/bin/bash

# Disable apache site
  sudo a2dissite SkillTracker

# Reload apache
  sudo systemctl reload apache2

# Remove /var/www folders
  sudo rm -rf /var/www/SkillTracker/
  sudo rm -rf /var/www/SkillTrackerLogs/

# Remove SkillTracker apache configuration file
  sudo rm -f /etc/apache2/sites-available/SkillTracker.conf

# Remove database
  sudo mysql -u root -pP@ssw0rd -e "DROP DATABASE SkillTracker;"

