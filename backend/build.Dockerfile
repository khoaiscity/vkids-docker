FROM golang:1.15.0-buster

ENV APP_HOME /app

WORKDIR $APP_HOME

RUN mkdir -p $APP_HOME/go-space

ENV GOPATH /app/go-space

CMD cd /app/go-space \
  && echo running go mod.. && cd src/github.com/smartblock/mlm-ng-goapi && /usr/local/go/bin/go mod vendor \
  && cd bin \
  && echo running go build... && /usr/local/go/bin/go build mlm-ng-goapi \
  && echo done!
