#!/bin/bash

# Clone the Github repo
  echo ""
  echo "    Cloning the Github repository..."
  echo ""
  sudo rm -rf /tmp/SkillSelector/
  cd /tmp
  git clone --depth=1 https://github.com/nipegun/SkillSelector

# Erase the previus web files
  rm -rf /var/www/SkillSelector/*

# Copy new files
  sudo cp -vr /tmp/SkillSelector/Web/* /var/www/SkillSelector/
  # Repair permissions
    echo ""
    echo "      Reparando permisos..."
    echo ""
    sudo chown www-data:www-data /var/www/ -Rv

  
