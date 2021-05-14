FROM golang:1.15.0-buster

ENV APP_HOME /app

WORKDIR $APP_HOME

RUN apt-get update && apt-get -y -q install libreoffice
RUN wget https://github.com/google/fonts/archive/main.tar.gz -O gf.tar.gz
RUN tar -xf gf.tar.gz
RUN mkdir -p /usr/share/fonts/truetype/google-fonts
RUN find $PWD/fonts-main/ -name "*.ttf" -exec install -m644 {} /usr/share/fonts/truetype/google-fonts/ \; || return 1
RUN rm -f gf.tar.gz
RUN fc-cache -f && rm -rf /var/cache/*
RUN mkdir -p $APP_HOME/go-space


ENV GOPATH /app/go-space

CMD cd /app/go-space \
  && echo running go mod.. && cd /app/go-space/src/github.com/smartblock/mlm-ng-goapi && /usr/local/go/bin/go mod vendor \
  && echo running go build.. && cd /app/go-space/bin && /usr/local/go/bin/go build mlm-ng-goapi \
  && echo done!