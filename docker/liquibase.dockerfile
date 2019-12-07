FROM openjdk:12

ENV LIQUIBASE_VERSION="3.8.2" \
    LIQUIBASE_DRIVER="com.mysql.cj.jdbc.Driver" \
    LIQUIBASE_URL="" \
    LIQUIBASE_USERNAME="" \
    LIQUIBASE_PASSWORD="" \
    LIQUIBASE_CHANGELOG="liquibase.xml" \
    DRIVER_VERSION="8.0.18"

COPY docker/init-liquibase.sh /scripts/init-liquibase.sh
COPY docker/wait-for-it.sh /scripts/wait-for-it.sh

# install liquibase
RUN curl -L -o /tmp/liquibase.tar.gz https://github.com/liquibase/liquibase/releases/download/v${LIQUIBASE_VERSION}/liquibase-${LIQUIBASE_VERSION}.tar.gz \
    && mkdir -p /opt/liquibase \
    && tar -xzf /tmp/liquibase.tar.gz -C /opt/liquibase \
    && chmod +x /opt/liquibase/liquibase \
    && ln -s /opt/liquibase/liquibase /usr/local/bin/

# install mysql java driver
RUN curl -L -o /tmp/mysql-connector-java.tar.gz https://dev.mysql.com/get/Downloads/Connector-J/mysql-connector-java-${DRIVER_VERSION}.tar.gz \
    && tar -xzf /tmp/mysql-connector-java.tar.gz -C /tmp \
    && cp /tmp/mysql-connector-java-${DRIVER_VERSION}/mysql-connector-java-${DRIVER_VERSION}.jar /opt/liquibase/lib/

ENTRYPOINT ["/scripts/init-liquibase.sh"]
