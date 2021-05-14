FROM golang:1.15.0-alpine

ENV APP_HOME /app

WORKDIR $APP_HOME

RUN apk add libreoffice
RUN wget https://github.com/google/fonts/archive/main.tar.gz -O gf.tar.gz
RUN tar -xf gf.tar.gz
RUN mkdir -p /usr/share/fonts/truetype/google-fonts
RUN find $PWD/fonts-main/ -name "*.ttf" -exec install -m644 {} /usr/share/fonts/truetype/google-fonts/ \; || return 1
RUN rm -f gf.tar.gz
RUN fc-cache -f && rm -rf /var/cache/*

ENV GOPATH /app/go-space

RUN apk add --no-cache \
  libc6-compat

COPY ./go-space /app/go-space

CMD cd /app/go-space \
  && bin/mlm-ng-goapi
