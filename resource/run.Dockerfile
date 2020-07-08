FROM node:10-alpine

ENV APP_HOME /app

RUN mkdir $APP_HOME

WORKDIR $APP_HOME

COPY ./mlm-ng/resource-server $APP_HOME/mlm-ng/resource-server
COPY ./.env $APP_HOME/mlm-ng/resource-server

RUN npm install --no-color --silent --prefix mlm-ng/resource-server

CMD cd $APP_HOME/mlm-ng/resource-server \
  && node server.js