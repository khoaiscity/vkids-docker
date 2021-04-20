FROM node:10-alpine

ENV APP_HOME /app
ENV NG_CLI_ANALYTICS ci

RUN mkdir $APP_HOME

WORKDIR $APP_HOME

CMD echo BUILDING... \
  && npm install --no-color --silent --prefix mlm-ng-admin/client \
  && npm run staging --progress=false --no-color --prefix mlm-ng-admin/client \
  && cd mlm-ng-admin \
  && echo COMPRESSING... \
  && tar -czf $APP_HOME/build/admin.tar.gz server \
  && echo DONE!!!
