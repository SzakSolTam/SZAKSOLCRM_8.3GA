# Instruction to run vtigercrm in Docker

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

Run the configure script which does nothing else than setting the proper file permissions for VTiger to be able to write to the current dir files from inside Docker

```sh
./configure
```

## Running VTiger

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

Then VTiger is running and is accessible with at http://127.0.0.1:7280

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



