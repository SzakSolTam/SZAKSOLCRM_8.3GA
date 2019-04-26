# Instruction to run Vtiger CRM with Docker

## Requirements

You need some basic knowledge on managing docker. 

You also need to install the following to your computer:

- make
- docker.io (or whatever version of docker that run on your architecture see: http://docker.io)
- docker-compose (https://docs.docker.com/compose/install/)
- bash 
- X code runtime (xcode command line tools: https://www.google.com/search?q=xcode+command+line+tools&source=lnt&tbs=qdr:m&sa=X&ved=0ahUKEwjm_52my-7hAhVGK5oKHdoBAbEQpwUIJA&biw=1538&bih=942)
- sudo (and root access)

## Initial configuration

Run the configure script which set the proper file permissions for Vtiger to be able to write to the current dir files from inside Docker.
```sh
./configure
```

## Install Vtiger

You have two option. Install the latest release (this directory) or install the latest stable release (recommended). 

To install and use the version in the current directory do the following:

```sh
make configure-latest
```

This will create the necessary directory structure for Docker to work.

Otherwise do:

```sh
configure-stable
```

This will pull the latest release from a sourceforge, and uncompress it into the vtigercrm directory inside this current directory... keeping the current code intact.


## Running Vtiger

Simply use the command: 

```sh
make up
```

If you see a similar output as this: 

```log
Creating network "vtigercrm_default" with the default driver
Creating vtigercrm_mysql_1 ... 
Creating vtigercrm_php_1 ... 
Creating vtigercrm_mysql_1
Creating vtigercrm_php_1 ... done
Creating vtigercrm_web-dev_1 ... 
Creating vtigercrm_web-dev_1 ... done
```

Then Vtiger is running and is accessible with at http://127.0.0.1:7280

If you wish to access the mysql server as root user for administration purpose you must do make logs (see Logs below) immediately and search for an entry looking like this:

```logs
mysql_1    | GENERATED ROOT PASSWORD: do6faiCei7iomequaiphaijoo8rod9ju
```

### Logs

To see the vtiger application logs and to control possible software issue 
you can use docker logs

```
make log
```

## Accessing the Vtiger Database Directly

### Access MySQL database with the MySQL Cli

```sh
make mysql-cli
```

### Accessing MySQL with your another client directy

MySQL from Vtiger is mapped to the port 3308 on the host machine.

You simply need to configure your MySQL management UI to connect to 
the ip: 127.0.0.1 with username: vtiger, password: vtiger, database vtiger and port 3308



