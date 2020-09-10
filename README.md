# vkids-docker
Docker for vkids

## Desciption
Our system has 6 services
1. Backend: golang
2. Member site: Angular
3. Admin site: Angular
4. Resource server: Nodejs - express
5. Web server: Nginx
6. Database: MySql

In the context that we want to release to our customers a package that can be used for running servers, but we don’t want to send them the source code. So we’ll split the two types of dockers for backend, admin, member and resource server
- Building: builds the source code to runnable files
- Running: execute files that built by the building docker

### Backend
- Golang version 1.13.0
- Base image for building container: golang:1.13.0-stretch
- Base image for running container: golang:1.13.0-alpine
### Admin site, member site
- Angular4 on NodeJs v10
- Base image for building container: node:10-alpine
- Base image for running container: node:10-alpine
### Resource server
- Express on NodeJs v10
- Base image for building container: node:10-alpine
- Base image for running container: node:10-alpine
### Webserver
- Nginx 1.17
- Base image for running container is nginx:1.17-alpine
### Database
The Database should be run on the real machine. So install the MySql normally.

## Setup
1. Install MySql server then import the database
2. Install Docker Engine https://docs.docker.com/install/linux/docker-ce/ubuntu/
3. Install Docker Compose https://docs.docker.com/compose/install/
4. Verify that Docker Engine and Docker Compose work
5. Clone docker source files https://github.com/duy-iscity/vkids-docker
6. Clone source code:
    - mlm-ng-goapi project to ./backend folder
    - mlm-ng-admin project to ./admin folder
    - mlm-ng project to ./member folder
    - mlm-ng project to ./resource folder

## Building 
Use build.sh shell script to build services
- Build backend:  ```sh build.sh backend```
- Build admin site:  ```sh build.sh admin```
- Build member site:  ```sh build.sh admin```
- Build resource site:  ```sh build.sh admin```
- To build all of services once time:  ```sh build.sh all```     

After building bellow files will appeared in the release folder:
  - ./release/admin/admin.tar.gz
  - ./release/member/member.tar.gz
  - ./release/backend/go-space/bin/mlm-ng-goapi
  - ./release/resource/resource.tar.gz
  
## Deploying
Go to release folder, then use run.sh shell script to deploy the product.   
Syntax: ```sh run.sh <service> <build id>```    
Example:  
- Build backend: ```sh run.sh backend feb14-0930```
- Build admin site: ```sh run.sh admin jan30-1800```
- Build member site: ```sh run.sh member dec31-1000```
- Build resource server: ```sh run.sh resource sep01-2130```
- Deploy nginx webserver: ```sh run.sh webserver mar15-0830```

## Release
After completed build all of services, all runnable packages included in the **release** folder. Use the **release** folder for release
