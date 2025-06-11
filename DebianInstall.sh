#!/bin/bash

# I make this script publicly available under the term "public domain software."
# You can do whatever you want with it because it is truly free—unlike so-called "free" software with conditions, like the GNU licenses and other similar nonsense.
# If you're so eager to talk about freedom, then make it truly free.
# You don't have to accept any terms of use or license to use or modify it, because it comes with no CopyLeft.

# ----------
# NiPeGun's script to install and configure SkillTracker on Debian
#
# Remote execution (may require sudo privileges):
#   curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianInstall.sh | bash
#
# Remote execution as root (for systems without sudo):
#   curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianInstall.sh | sed 's-sudo--g' | bash
#
# Remote execution without cache:
#   curl -sL -H 'Cache-Control: no-cache, no-store' https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianInstall.sh | bash
#
# Remote execution with parameters:
#   curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianInstall.sh | bash -s Parámetro1 Parámetro2
#
# Download and edit the file directly in nano:
#   curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianInstall.sh | nano -
# ----------

# Define color constants in Bash (for terminal output):
  cColorBlue='\033[0;34m'
  cColorBlueLight='\033[1;34m'
  cColorGreen='\033[1;32m'
  cColorRed='\033[1;31m'
  cColorEnd='\033[0m'

# Check if the curl package is installed. If it's not, install it:
  if [[ $(dpkg-query -s curl 2>/dev/null | grep installed) == "" ]]; then
    echo ""
    echo -e "${cColorRed}  The curl package is not installed. Starting installation...${cColorEnd}"
    echo ""
    sudo apt-get -y update
    sudo apt-get -y install curl
    echo ""
  fi

# Determine the Debian version
  if [ -f /etc/os-release ]; then             # For systemd and freedesktop.org.
    . /etc/os-release
    cOSName=$NAME
    cOSVersion=$VERSION_ID
  elif type lsb_release >/dev/null 2>&1; then # For linuxbase.org.
    cOSName=$(lsb_release -si)
    cOSVersion=$(lsb_release -sr)
  elif [ -f /etc/lsb-release ]; then          # For some Debian version without the lsb_release command.
    . /etc/lsb-release
    cOSName=$DISTRIB_ID
    cOSVersion=$DISTRIB_RELEASE
  elif [ -f /etc/debian_version ]; then       # For old versions of Debian.
    cOSName=Debian
    cOSVersion=$(cat /etc/debian_version)
  else                                        # For the old uname (also works for BSD).
    cOSName=$(uname -s)
    cOSVersion=$(uname -r)
  fi

# Run commands depending on the detected Debian version:

  if [ $cOSVersion == "13" ]; then

    echo ""
    echo -e "${cColorBlueLight}  Starting the installation script of SkillTracker for Debian 13 (x)...${cColorEnd}"
    echo ""

    echo ""
    echo -e "${cColorRed}    Commands for Debian 13 are not yet prepared. Try running this on another Debian version.${cColorEnd}"
    echo ""

  elif [ $cOSVersion == "12" ]; then

    echo ""
    echo -e "${cColorBlueLight}  Starting the installation script of SkillTracker for Debian 12 (Bookworm)...${cColorEnd}"
    echo ""

    # Definir fecha de ejecución del script
      cFechaDeEjec=$(date +a%Ym%md%d@%T)

    # Crear el menú
      # Comprobar si el paquete dialog está instalado. Si no lo está, instalarlo.
        if [[ $(dpkg-query -s dialog 2>/dev/null | grep installed) == "" ]]; then
          echo ""
          echo -e "${cColorRojo}    The dialog package is not installed. Starting installation...${cFinColor}"
          echo ""
          sudo apt-get -y update
          sudo apt-get -y install dialog
          echo ""
        fi
      menu=(dialog --checklist "Mark your options with the space bar and then press Enter:" 22 96 16)
        opciones=(
          1 "Install" on
          2 "Re-Install (Deleting previopus installation)" off
        )
      choices=$("${menu[@]}" "${opciones[@]}" 2>&1 >/dev/tty)
      #clear

      for choice in $choices
        do
          case $choice in

            1)

              echo ""
              echo "  Installing SkillTracker on Debian..."
              echo ""

              # Instalar los paquetes necesarios para que el script se ejecute correctamente
                echo ""
                echo "    Installing all the required packages for the script to execute without errors..."
                echo ""
                sudo apt-get -y update
                sudo apt-get -y install git
                sudo apt-get -y install apache2
                sudo apt-get -y install php
                sudo apt-get -y install mariadb-server
                sudo apt-get -y install php-mysql

              # Clonar el repo
                echo ""
                echo "    Cloning the Github repository..."
                echo ""
                sudo rm -rf /tmp/SkillTracker/
                cd /tmp
                git clone --depth=1 https://github.com/nipegun/SkillTracker

              # Cambiar la contraseña del usuario root de MariaDB
                sudo mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'P@ssw0rd'; FLUSH PRIVILEGES;"

              # Erase the previous database
                echo ""
                echo "    Erasing the previous database..."
                echo ""
                sudo mysql -u root -pP@ssw0rd -e "DROP DATABASE SkillTracker;"

              # Create the user and the database
                echo ""
                echo "    Creating the database & user..."
                echo ""
                mysql -uroot -pP@ssw0rd -e "CREATE DATABASE SkillTracker; CREATE USER 'SkillTracker'@'localhost' IDENTIFIED BY 'P@ssw0rd'; GRANT ALL PRIVILEGES ON SkillTracker.* TO 'SkillTracker'@'localhost'; FLUSH PRIVILEGES;"

              # Create the new database
                echo ""
                echo "    Importing the new database..."
                echo ""
                cd /tmp/SkillTracker/DB/
                sudo chmod +x /tmp/SkillTracker/DB/ImportDB.sh
                ./ImportDB.sh

              # Deshabilitar el sitio por defecto
                echo ""
                echo "    Deshabilitando el sitio por defecto de Apache2..."
                echo ""
                sudo a2dissite 000-default
                sudo systemctl reload apache2

              # Configurar el servidor web
                echo ""
                echo "    Configurando el servidor web..."
                echo ""
                echo "<VirtualHost *:80>"                                           | sudo tee    /etc/apache2/sites-available/SkillTracker.conf
                echo "  ServerAdmin webmaster@localhost"                            | sudo tee -a /etc/apache2/sites-available/SkillTracker.conf
                echo "  DocumentRoot /var/www/SkillTracker"                         | sudo tee -a /etc/apache2/sites-available/SkillTracker.conf
                echo "  ErrorLog     /var/www/SkillTrackerLogs/error.log"           | sudo tee -a /etc/apache2/sites-available/SkillTracker.conf
                echo "  CustomLog    /var/www/SkillTrackerLogs/access.log combined" | sudo tee -a /etc/apache2/sites-available/SkillTracker.conf
                echo "</VirtualHost>"                                               | sudo tee -a /etc/apache2/sites-available/SkillTracker.conf

              # Copiando archivos de la web
                echo ""
                echo "    Copiando archivos de la web..."
                echo ""
                sudo rm -rf   /var/www/SkillTracker/
                sudo rm -rf   /var/www/SkillTrackerLogs/
                sudo mkdir -p /var/www/SkillTracker/
                sudo mkdir -p /var/www/SkillTrackerLogs/
                sudo cp -vr /tmp/SkillTracker/Web/* /var/www/SkillTracker/
                # Reparar permisos
                  echo ""
                  echo "      Reparando permisos..."
                  echo ""
                  sudo chown www-data:www-data /var/www/ -Rv

              # Activar la web
                echo ""
                echo "  Activating SkillTracker web on apache2..."
                echo ""
                sudo a2ensite SkillTracker
                sudo systemctl reload apache2

              # Notificar fin de ejecución del script
                echo ""
                echo "    Instalation script, ended."
                echo ""

            ;;

            2)

              echo ""
              echo "  Re-Installing SkillTracker on Debian (Deleting previopus installation)..."
              echo ""

            ;;

        esac

    done

  elif [ $cOSVersion == "11" ]; then

    echo ""
    echo -e "${cColorBlueLight}  Starting the installation script of SkillTracker for Debian 11 (Bullseye)...${cColorEnd}"
    echo ""

    echo ""
    echo -e "${cColorRed}    Commands for Debian 11 are not yet prepared. Try running this on another Debian version.${cColorEnd}"
    echo ""

  elif [ $cOSVersion == "10" ]; then

    echo ""
    echo -e "${cColorBlueLight}  Starting the installation script of SkillTracker for Debian 10 (Buster)...${cColorEnd}"
    echo ""

    echo ""
    echo -e "${cColorRed}    Commands for Debian 10 are not yet prepared. Try running this on another Debian version.${cColorEnd}"
    echo ""

  elif [ $cOSVersion == "9" ]; then

    echo ""
    echo -e "${cColorBlueLight}  Starting the installation script of SkillTracker for Debian 9 (Stretch)...${cColorEnd}"
    echo ""

    echo ""
    echo -e "${cColorRed}    Commands for Debian 9 are not yet prepared. Try running this on another Debian version..${cColorEnd}"
    echo ""

  elif [ $cOSVersion == "8" ]; then

    echo ""
    echo -e "${cColorBlueLight}  Starting the installation script of SkillTracker for Debian 8 (Jessie)...${cColorEnd}"
    echo ""

    echo ""
    echo -e "${cColorRed}    Commands for Debian 8 are not yet prepared. Try running this on another Debian version.${cColorEnd}"
    echo ""

  elif [ $cOSVersion == "7" ]; then

    echo ""
    echo -e "${cColorBlueLight}  Starting the installation script of SkillTracker for Debian 7 (Wheezy)...${cColorEnd}"
    echo ""

    echo ""
    echo -e "${cColorRed}    Commands for Debian 7 are not yet prepared. Try running this on another Debian version.${cColorEnd}"
    echo ""

  fi

