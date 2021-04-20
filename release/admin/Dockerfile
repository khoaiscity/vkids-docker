FROM node:10-alpine

ENV APP_HOME /app
ENV NG_CLI_ANALYTICS ci

RUN mkdir $APP_HOME

WORKDIR $APP_HOME

COPY ./.env $APP_HOME/
COPY ./admin.tar.gz $APP_HOME/

RUN tar -xzf admin.tar.gz && rm admin.tar.gz

RUN npm install --prefix server -y \
  && npm run build --prefix server

CMD node ./server/index.js