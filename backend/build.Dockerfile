FROM golang:1.15.0-buster

ENV APP_HOME /app

WORKDIR $APP_HOME

RUN mkdir -p $APP_HOME/go-space

ENV GOPATH /app/go-space

CMD cd /app/go-space \
  && echo running go get... && /usr/local/go/bin/go get -d github.com/smartblock/mlm-ng-goapi \
  && cd bin \
  && echo running go build... && /usr/local/go/bin/go build mlm-ng-goapi \
  && echo done!
