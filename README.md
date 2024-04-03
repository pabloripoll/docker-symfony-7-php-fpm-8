<div style="width:100%;float:left;clear:both;margin-bottom:50px;">
    <a href="https://github.com/pabloripoll?tab=repositories">
        <img style="width:150px;float:left;" src="https://pabloripoll.com/files/logo-light-100x300.png"/>
    </a>
</div>

<div style="width:100%;float:left;clear:both;margin-bottom:50px;">
    <a href="resources/doc/symfony-7-installation-screenshot.png">
        <img style="width:100%;float:left;" src="resources/doc/symfony-7-installation-screenshot.png"/>
    </a>
</div>

# Docker Symfony 7 with PHP FPM 8+

The objective of this repository is having a CaaS [Containers as a Service](https://www.ibm.com/topics/containers-as-a-service) to provide a start up application with the basic enviroment features to deploy a php service running with Nginx and PHP-FPM in a container for [Symfony](https://symfony.com/) and another container with a MySQL database to follow the best practices on an easy scenario to understand and modify on development requirements.

The connection between container is as [Host Network](https://docs.docker.com/network/drivers/host/) on `eth0`, thus both containers do not share networking or bridge configuration.

As client end user both services can be accessed through `localhost:${PORT}` but the connection between containers is through the `${HOSTNAME}:${PORT}`.

### Symfony Docker Container Service

- [Symfony 7](https://symfony.com/doc/7.0/setup.html)

- [PHP-FPM 8.3](https://www.php.net/releases/8.3/en.php)

- [Nginx 1.24](https://nginx.org/)

- [Alpine Linux 3.19](https://www.alpinelinux.org/)

### Database Container Service

To connect this service to a SQL database, it can be used the following [MariaDB 10.11](https://mariadb.com/kb/en/changes-improvements-in-mariadb-1011/) service:
- [https://github.com/pabloripoll/docker-mariadb-10.11](https://github.com/pabloripoll/docker-mariadb-10.11)

### Project objetives with Docker

* Built on the lightweight and secure Alpine 3.19 [2024 release](https://www.alpinelinux.org/posts/Alpine-3.19.1-released.html) Linux distribution
* Multi-platform, supporting AMD4, ARMv6, ARMv7, ARM64
* Very small Docker image size (+/-40MB)
* Uses PHP 8.3 as default for the best performance, low CPU usage & memory footprint, but also can be downgraded till PHP 8.0
* Optimized for 100 concurrent users
* Optimized to only use resources when there's traffic (by using PHP-FPM's `on-demand` process manager)
* The services Nginx, PHP-FPM and supervisord run under a project-privileged user to make it more secure
* The logs of all the services are redirected to the output of the Docker container (visible with `docker logs -f <container name>`)
* Follows the KISS principle (Keep It Simple, Stupid) to make it easy to understand and adjust the image to your needs
* Services independency to connect the application to other database allocation

#### PHP config

To use a different PHP 8 version the following [Dockerfile](docker/nginx-php/docker/Dockerfile) arguments and variable has to be modified:
```Dockerfile
ARG PHP_VERSION=8.3
ARG PHP_ALPINE=83
...
ENV PHP_V="php83"
```

Also, it has to be informed to [Supervisor Config](docker/nginx-php/docker/config/supervisord.conf) the PHP-FPM version to run.
```bash
...
[program:php-fpm]
command=php-fpm83 -F
...
```

#### Containers on Windows systems

This project has not been tested on Windows OS neither I can use it to test it. So, I cannot bring much support on it.

Anyway, using this repository you will needed to find out your PC IP by login as an `administrator user` to set connection between containers.

```bash
C:\WINDOWS\system32>ipconfig /all

Windows IP Configuration

 Host Name . . . . . . . . . . . . : 191.128.1.41
 Primary Dns Suffix. . . . . . . . : paul.ad.cmu.edu
 Node Type . . . . . . . . . . . . : Peer-Peer
 IP Routing Enabled. . . . . . . . : No
 WINS Proxy Enabled. . . . . . . . : No
 DNS Suffix Search List. . . . . . : scs.ad.cs.cmu.edu
```

Take the first ip listed. Wordpress container will connect with database container using that IP.

#### Containers on Unix based systems

Find out your IP on UNIX systems and take the first IP listed
```bash
$ hostname -I

191.128.1.41 172.17.0.1 172.20.0.1 172.21.0.1
```

## Structure

Directories and main files on a tree architecture description
```
.
│
├── docker
│   └── nginx-php
│       ├── ...
│       ├── .env.example
│       └── docker-compose.yml
│
├── resources
│   ├── database
│   │   ├── symfony-init.sql
│   │   └── symfony-backup.sql
│   │
│   └── symfony
│       └── (any file or directory required for re-building the app...)
│
├── symfony
│   └── (application...)
│
├── .env
├── .env.example
└── Makefile
```

## Automation with Makefile

Makefiles are often used to automate the process of building and compiling software on Unix-based systems as Linux and macOS.

*On Windows - I recommend to use Makefile: \
https://stackoverflow.com/questions/2532234/how-to-run-a-makefile-in-windows*

Makefile recipies
```bash
$ make help
usage: make [target]

targets:
Makefile  help                     shows this Makefile help message
Makefile  hostname                 shows local machine ip
Makefile  fix-permission           sets project directory permission
Makefile  ports-check              shows this project ports availability on local machine
Makefile  symfony-ssh              enters the Symfony container shell
Makefile  symfony-set              sets the Symfony PHP enviroment file to build the container
Makefile  symfony-build            builds the Symfony PHP container from Docker image
Makefile  symfony-start            starts up the Symfony PHP container running
Makefile  symfony-stop             stops the Symfony PHP container but data will not be destroyed
Makefile  symfony-destroy          stops and removes the Symfony PHP container from Docker network destroying its data
Makefile  repo-flush               clears local git repository cache specially to update .gitignore
```

Checkout local machine ports availability
```bash
$ make ports-check

Checking configuration for SYMFONY container:
SYMFONY > port:8888 is free to use.
```

Checkout local machine IP to set connection between containers using the following makefile recipe
```bash
$ make hostname

192.168.1.41
```

## Build the project

```bash
$ make project-build

SYMFONY docker-compose.yml .env file has been set.

[+] Building 31.5s (25/25)                                             docker:default
 => [wordpress internal] load build definition from Dockerfile         0.0s
 => => transferring dockerfile: 2.47kB
...
=> => naming to docker.io/library/symfony-app:php-8.3                  0.0s
[+] Running 1/2
 ⠇ Network symfony-app_default  Created                                0.8s
 ✔ Container symfony-app        Started
```

## Running the project

```bash
$ make project-start

[+] Running 1/0
 ✔ Container symfony-db  Running                       0.0s
[+] Running 1/0
 ✔ Container symfony-app  Running                      0.0s
 ```

Now, Symfony should be available on local machine by visiting [http://localhost:8888/](http://localhost:8888/)

## Docker Info

Docker container
```bash
$ sudo docker ps -a
CONTAINER ID   IMAGE      COMMAND    CREATED      STATUS      PORTS                                             NAMES
ecd27aeae010   symf...   "docker-php-entrypoi…"   3 mins...   9000/tcp, 0.0.0.0:8888->80/tcp, :::8888->80/tcp   symfony-app

```

Docker image
```bash
$ sudo docker images
REPOSITORY   TAG           IMAGE ID       CREATED         SIZE
symfony-app  symf...       373f6967199b   5 minutes ago   200MB
```

Docker stats
```bash
$ sudo docker system df
TYPE            TOTAL     ACTIVE    SIZE      RECLAIMABLE
Images          1         1         532.2MB   0B (0%)
Containers      1         1         25.03kB   0B (0%)
Local Volumes   1         0         117.9MB   117.9MB (100%)
Build Cache     39        0         10.21kB   10.21kB
```

## Check Symfony status

Visiting `http://localhost:8888/` should display Symfony's welcome page.

Use an API platform *(Postman, Firefox RESTClient, etc..)* to check connection with Symfony
```
GET: http://localhost:8888/api/v1/health

{
    "status": true
}
```

## Check Symfony database connection

Check connection to database through a test endpoint. If conenction params are not set already will response as follows
```
GET: http://localhost:8888/api/v1/health/db

{
    "status": false,
    "message": "Connect to database failed - Check connection params.",
    "error": "An exception occurred in the driver: SQLSTATE[HY000] [2002] Connection refused"
}
```

Complete the MySQL database connection params. Use local hostname IP `$ make hostname` to set `IP` param
```
DATABASE_URL="mysql://symfony:123456@192.168.1.41:8889/symfony?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
```

Checking the connection to database once is set correctly will response as follows
```
GET: http://localhost:8888/api/v1/health/db

{
    "status": true
}
```

## Stop Containers

Using the following Makefile recipe stops application and database containers, keeping database persistance and application files binded without any loss
```bash
$ make project-stop

[+] Killing 1/1
 ✔ Container symfony-app  Killed              0.5s
Going to remove symfony-app
[+] Removing 1/0
 ✔ Container symfony-app  Removed             0.0s
```

## Remove Containers

To stop and remove both application and database containers from docker network use the following Makefile recipe
```bash
$ make project-destroy

[+] Killing 1/1
 ✔ Container symfony-app  Killed                   0.4s
Going to remove symfony-app
[+] Removing 1/0
 ✔ Container symfony-app  Removed                  0.0s
[+] Running 1/1
 ✔ Network symfony-app_default  Removed
```

Prune Docker system cache
```bash
$ sudo docker system prune

...
Total reclaimed space: 423.4MB
```

Prune Docker volume cache
```bash
$ sudo docker system prune

...
Total reclaimed space: 50.7MB
```