FROM node:10-alpine

ENV APP_HOME /app
ENV NG_CLI_ANALYTICS ci

RUN mkdir $APP_HOME

WORKDIR $APP_HOME

COPY ./.env $APP_HOME/
COPY ./build/member.tar.gz $APP_HOME/

RUN tar -xzf member.tar.gz && rm member.tar.gz

RUN npm install --no-color --silent --prefix server \
  && npm run build --prefix server

CMD node ./server/index.js