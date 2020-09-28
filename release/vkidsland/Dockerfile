FROM node:10-alpine

ENV APP_HOME /app
ENV NG_CLI_ANALYTICS ci

RUN mkdir $APP_HOME

WORKDIR $APP_HOME

COPY ./.env $APP_HOME/
COPY ./vkidsland.tar.gz $APP_HOME/

RUN tar -xzf vkidsland.tar.gz && rm vkidsland.tar.gz

RUN npm install --prefix -y \
  && npm run build --prefix

CMD npm run start