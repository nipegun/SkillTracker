#!/bin/bash

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
    echo "      Reparando permisos..."
    echo ""
    sudo chown www-data:www-data /var/www/ -Rv

  
