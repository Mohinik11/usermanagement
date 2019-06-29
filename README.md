# UserManagement (API using Symfony FOSRestBundle )

## Getting started

### Prerequisites
  - php 7+, mysql (or docker & docker-compose)

#### Run application without docker
  - go to the project root folder
  - run `composer install` to install all dependencies
  - start mysql and create database as per env file params or change env accordingly
  - run `php bin/console server:start` to start the server
  - run `php bin/console doctrine:migrations:migrate` to run migrations
  - create jwt private and public keys as 
    - mkdir config/jwt
    - openssl genrsa -out config/jwt/private.pem -aes256 4096
    - openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
    - modify passphrase in env file


#### Run application with docker
  - go to the project root folder
  - create jwt private and public keys
    - mkdir config/jwt
    - openssl genrsa -out config/jwt/private.pem -aes256 4096
    - openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
    - modify passphrase in env file
  - run `docker-compose up`
  - run `docker-compose exec usermanagement-php composer install `
  - run `docker-compose exec usermanagement-php php bin/console doctrine:schema:create`


Now you can visit "http://127.0.0.1:8000/doc" to see the api doc.
Here you can see the API endpoints details with request and response example data


## Running Test
  - go to the project root folder
  - run `bin/phpunit` to run test cases

## Application structure via UML
  - please go to docs folder to see the diagrams


##Note regarding application: 
  - I have created minimal db structure needed to fulfil the requirement.
  - Also, added jwt authentication.
  - Created integration test for user controller.

admin credentials are used via in_memory user provider and are :

  - admin login credentials:
    user: `superadmin`
    password: `password`