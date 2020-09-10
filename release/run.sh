#!/bin/bash

case $1 in
    "backend")
    docker-compose --project-name=$BUILD_ID-vkids up --no-color --no-start --build $1
    docker stop $(docker ps -f name=vkids_$1 -q)
    docker-compose --project-name=$BUILD_ID-vkids start $1
    ;;
    "admin")
    docker-compose --project-name=$BUILD_ID-vkids up --no-color --no-start --build $1
    docker stop $(docker ps -f name=vkids_$1 -q)
    docker-compose --project-name=$BUILD_ID-vkids start $1
    ;;
    "member")
    docker-compose --project-name=$BUILD_ID-vkids up --no-color --no-start --build $1
    docker stop $(docker ps -f name=vkids_$1 -q)
    docker-compose --project-name=$BUILD_ID-vkids start $1
    ;;
    "resource")
    docker-compose --project-name=$BUILD_ID-vkids up --no-color --no-start --build $1
    docker stop $(docker ps -f name=vkids_$1 -q)
    docker-compose --project-name=$BUILD_ID-vkids start $1
    ;;
    *) echo please provide a service name ;;
esac
