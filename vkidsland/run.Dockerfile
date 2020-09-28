FROM node:10-alpine

ENV APP_HOME /app
ENV NG_CLI_ANALYTICS ci

RUN mkdir $APP_HOME

WORKDIR $APP_HOME

COPY ./.env $APP_HOME/
COPY ./build/vkidsland.tar.gz $APP_HOME/

RUN tar -xzf vkidsland.tar.gz && rm vkidsland.tar.gz

RUN npm install --no-color --silent --prefix vkidsland \
  && npm run build --prefix vkidsland

CMD npm run start