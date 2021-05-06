FROM golang:1.15.0-buster

ENV APP_HOME /app

WORKDIR $APP_HOME

RUN mkdir -p $APP_HOME/go-space

ENV GOPATH /app/go-space

CMD cd /app/go-space \
  && cd src/github.com/smartblock/mlm-ng-goapi \
  && pwd \
  && ls -l \
  && echo done!
