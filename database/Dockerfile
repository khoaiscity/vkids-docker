FROM mysql:5.7.26

ENV APP_HOME /app

RUN mkdir $APP_HOME

WORKDIR $APP_HOME

ADD db_vkids_test.sql.gz /docker-entrypoint-initdb.d
