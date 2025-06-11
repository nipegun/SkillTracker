#!/bin/bash

# I make this script publicly available under the term "public domain software."
# You can do whatever you want with it because it is truly free—unlike so-called "free" software with conditions, like the GNU licenses and other similar nonsense.
# If you're so eager to talk about freedom, then make it truly free.
# You don't have to accept any terms of use or license to use or modify it, because it comes with no CopyLeft.

# ----------
# NiPeGun's script to install and configure SkillTracker on Debian
#
# Remote execution (may require sudo privileges):
#   curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianUpdate.sh | bash
#
# Remote execution as root (for systems without sudo):
#   curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianUpdate.sh | sed 's-sudo--g' | bash
#
# Download and edit the file directly in nano:
#   curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianUpdate.sh | nano -
# ----------

# Clone the Github repo
  echo ""
  echo "    Cloning the Github repository..."
  echo ""
  sudo rm -rf /tmp/SkillTracker/
  cd /tmp
  git clone --depth=1 https://github.com/nipegun/SkillTracker

# Erase the previus web files
  sudo rm -rf /var/www/SkillTracker/*

# Copy new files
  sudo cp -vr /tmp/SkillTracker/Web/* /var/www/SkillTracker/
  # Repair permissions
    echo ""
    echo "      Repairing permissions..."
    echo ""
    sudo chown www-data:www-data /var/www/ -Rv

  
