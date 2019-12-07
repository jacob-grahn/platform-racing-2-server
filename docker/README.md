# Starting up a local server for testing
Using a terminal, navigate to this directory and run `docker-compose up`.

The first time this runs, it will take several minutes to download and build various docker images.

You will know everything is ready when you see a message in the terminal that says `Liquibase: Update has been successful.`

PR2's website will be available at `http://localhost:8080`

Adminer will be available at `http://localhost:8081`. To log in, use the following info:
```
system: MySQL 
server: mysql
username: root
password: root
database: pr2
```
