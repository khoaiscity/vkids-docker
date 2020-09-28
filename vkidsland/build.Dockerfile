FROM node:10-alpine

ENV APP_HOME /app
ENV NG_CLI_ANALYTICS ci

RUN mkdir $APP_HOME

WORKDIR $APP_HOME

CMD echo BUILDING... \
  && npm install --no-color --silent --prefix vkidsland \
  && npm run build --progress=false --no-color --prefix vkidsland \
  && cd vkidsland \
  && echo COMPRESSING... \
  && tar -czf $APP_HOME/build/vkidsland.tar.gz * \
  && echo DONE!!!
