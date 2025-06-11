# SkillTracker

SkillTracker is a web service that allows members of a company to look up information about which other members of the same company, or of another company within the same corporate group, possess specific skills. This way, when a new project arises, employees can correctly choose which coworkers can be part of it, based solely on their skills.

## Installation

The easiest and fastest way to install SkillTracker on a Debian-based distribution is to run the following command as a user in the sudo group:

```
curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianInstall.sh | bash
```
...or run as root:

```
curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianInstall.sh | sed 's-sudo--g' | bash
```

## Update

The easiest and fastest way to update an already installed instance of SkillTracker on a Debian-based distribution is to run the following command as a user in the sudo group:

```
curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianUpdate.sh | bash
```
...or run as root:

```
curl -sL https://raw.githubusercontent.com/nipegun/SkillTracker/refs/heads/main/DebianUpdate.sh | sed 's-sudo--g' | bash
```
