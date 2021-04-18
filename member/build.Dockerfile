FROM node:10-alpine

ENV APP_HOME /app
ENV NG_CLI_ANALYTICS ci

RUN mkdir $APP_HOME

WORKDIR $APP_HOME

COPY ./environment.prod.ts $APP_HOME/client/src/environments/

CMD echo BUILDING... \ 
  && npm install --no-color --silent --prefix mlm-ng/client \
  && npm run build --progress=false --no-color --prefix mlm-ng/client \
  && cd mlm-ng \
  && echo COMPRESSING... \
  && tar -czf $APP_HOME/build/member.tar.gz server \
  && echo DONE!!!
