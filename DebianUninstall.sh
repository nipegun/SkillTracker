#!/bin/bash

# I make this script publicly available under the term "public domain software."
# You can do whatever you want with it because it is truly free—unlike so-called "free" software with conditions, like the GNU licenses and other similar nonsense.
# If you're so eager to talk about freedom, then make it truly free.
# You don't have to accept any terms of use or license to use or modify it, because it comes with no CopyLeft.

# ----------
# NiPeGun's script to install and configure SkillTracker on Debian
#
# Remote execution (may require sudo privileges):
#   curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianUninstall.sh | bash
#
# Remote execution as root (for systems without sudo):
#   curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianUninstall.sh | sed 's-sudo--g' | bash
#
# Download and edit the file directly in nano:
#   curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianUninstall.sh | nano -
# ----------

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

