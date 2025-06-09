# SkillTracker

SkillTracker es un servicio web que permite a los miembros de una empresa consultar información acerca de qué otros miembros de la misma empresa, o de otra empresa que pertenezca al mismo grupo empresarial, tienen habilidades específicas. De esta forma, ante un nuevo proyecto, los trabajadores pueden elegir correctamente que otros compañeros de trabajo pueden formar parte del mismo, basándose únicamente en sus habilidades.

## Instalación

La forma más fácil y rápida de instalar SkillTracker en una distro Debian o derivada es ejecutar con un usuario del grupo sudo:

```
curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianInstall.sh | bash
```
...o ejecutar como root:

```
curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianInstall.sh | sed 's-sudo--g' | bash
```
