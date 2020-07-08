FROM node:10-alpine

ENV APP_HOME /app

RUN mkdir $APP_HOME

WORKDIR $APP_HOME

COPY ./.env $APP_HOME/
COPY ./resource.tar.gz $APP_HOME/

RUN tar -xzf resource.tar.gz && rm resource.tar.gz

RUN npm install --prefix resource-server -y

CMD node ./resource-server/server.js