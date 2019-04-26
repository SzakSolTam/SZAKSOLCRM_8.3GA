AUTOMAKE_OPTIONS = foreign
SHELL:=/bin/bash
SHELL        := $(shell which bash)
.SHELLFLAGS = -c

.SILENT: ;               # no need for @
.ONESHELL: ;             # recipes execute in same shell
.NOTPARALLEL: ;          # wait for this target to finish
.EXPORT_ALL_VARIABLES: ; # send all vars to shell
default: help-default;   # default target
Makefile: ;              # skip prerequisite discovery
help:  ## Show this help message.
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'
configure-stable: ## Pull stable release and untar
	@echo "!!! WARNING !!! This will destroy the current instalation (delete everything)"
	@if make .prompt-yesno message="Continue to destroy the current installation and all it's data (Y/n)?" 2> /dev/null; then \
		echo " ";\
	else
		echo "Operation Cancel!";\
		exit 1;\
	fi
	make clean
	@if [ ! -f $(PWD)/vtigercrm.tgz ];\
        then\
                echo " ";\
                echo " ";\
		echo "File vtigercrm.tgz could not be found. If you wish to install a specific version of Vtiger CRM you should download the version and move the archive in this directory with the name: vtigercrm.tgz and run this command again. ";\
		echo " ";\
		echo "We will attempt to download the latest version";\
		docker run -t --rm -v${PWD}:/vtigercrmtmp alpine sh -c 'cd /vtigercrmtmp && wget "https://downloads.sourceforge.net/project/vtigercrm/vtiger%20CRM%207.1.0/Core%20Product/vtigercrm7.1.0.tar.gz?r=https%3A%2F%2Fsourceforge.net%2Fprojects%2Fvtigercrm%2Ffiles%2Fvtiger%2520CRM%25207.1.0%2FCore%2520Product%2Fvtigercrm7.1.0.tar.gz%2Fdownload&ts=1555754790" -O vtigercrm.tgz';\
                echo " ";\
        fi
	echo "Extracting vtigercrm.tgz in this directory"
	tar -zxof vtigercrm.tgz
	echo "Setting required permission for vtigercrm (777)"
	sudo chmod 777 -fR vtigercrm
	echo "Copying the php-fpm config into vtigercrm installation"
	cp .user.ini vtigercrm/
	touch .configured.lock
configure-latest: ## Use the current dir for vtiger path
	@-sudo rm -fR vtigercrm
	ln -s . vtigercrm
	touch .configured.lock

clean: down ## Remove current vtigercrm installation
	echo "Removing lock file"
	@-rm .configured.lock
	echo "Removing all data directory"
	-@sudo rm -fR vtigercrm
	-@sudo rm -fR varlibmysql
up: is-configured ## Run vtiger
	docker-compose up -d
down: ## Down Vtiger
	docker-compose down
log: ## Docker-compose logs, for specific container add T=name of container
	docker-compose logs -f ${T}
ps: is-configured ## PS vtiger
	docker-compose ps
mysql-cli: is-configured ## PS vtiger
	docker-compose run mysql mysql -h mysql -u vtiger -p vtiger
sh-prod: ## Connect to the prod host with SSH
	ssh vtiger@vtiger.prod
####### Down from here are sub-commands that aren't useful to call manually
is-configured: ## Check if vtigercrm is already configured
	@if [ ! -f $(PWD)/.configured.lock ];\
        then\
                echo " ";\
                echo " ";\
                echo "You are trying to run command that required a configured repo ";\
                echo "Please make configure-stable or configure-latest for vtigercrm developer  first!!";\
                echo " ";\
                exit 1;\
        fi
.prompt-yesno:
	@exec 9<&0 0</dev/tty
	echo "$(message):"
	# Default will be Y after 30 seconds
	read -rs -t30 -n 1 yn;
	exec 0<&9 9<&-
	echo $$yn
	[[ -z $$yn ]] || [[ $$yn == [yY] ]] && echo Y >&2 || (echo N >&2 && exit 1)

