# EZ Loyalty

## Intro
A simple standalone e-commerce customer loyalty service built with PHP (Api Platform 2.7 + Symfony 5.4 + EasyAdmin).

![use case](/doc/img/use-case.png)

**Features**:
- loyalty accounts for your customers and users
- cross-site loyalty balance tracking and transactions
- customizable promo campaigns
- automatic inactive accounts tracking, reminders and removal
- secure REST API with docs and playground
- admin dashboard

[![GitHub Actions](https://github.com/rederrik/ez-loyalty/actions/workflows/symfony-test.yml/badge.svg)](https://github.com/rederrik/ez-loyalty/actions?query=workflow%3Asymfony-test)
[![GitHub Actions](https://github.com/rederrik/ez-loyalty/actions/workflows/docker-publish.yml/badge.svg)](https://github.com/rederrik/ez-loyalty/actions?query=workflow%3Adocker-publish)

## Requirements
Either PHP8.1 or Docker.

## Quick start with Docker
+ `docker run -p 8080:8080 --rm ghcr.io/rederrik/ez-loyalty:master`
+ open `http://localhost:8080/admin` in your browser
+ use the default credentials `admin:loyalty` to log in

## Setup
### Configuration
The application may be configured by overriding env vars using the `.env.local`  file. 
Put your values inside before building an image.

You could also want to replace some of the few templates located in the `templates` directory.


### Running using Docker

Build an image:

`docker build -f Dockerfile-demo -t loyalty-demo .`

Run a container:

`docker run [-it --rm] -p 8080:8080 --name loyalty-demo loyalty-demo`

Run tests using:

`docker exec -w /usr/src/api loyalty-demo sh -c "php bin/console d:s:c -e test && php bin/phpunit"`

### Manual setup
+ Run `composer install` to pull the dependencies.
+ Run `php bin/console doctrine:schema:create` to create a database.
+ Add `php bin/console app:transaction:release-frozen` cron task to automatically release frozen coins after some time
+ `cd public` then `php -S 0.0.0.0:8080` to run PHP built-in web server.
+ Or use your own web server setup like caddy + php-fpm!
+ [Optional] Run `php bin/console hautelook:fixtures:load -n -e dev` to insert test data (defined in `/fixtures/` directory) into the database
+ [Optional] Add `php bin/console app:app:account:delete-expired` cron task to automatically delete the expired loyalty accounts
+ [Optional] Add `php bin/console app:account:notify-expiring` cron task to automatically notify the account owner when their account is about to expire

## Usage
### User endpoints
+ http://localhost:8080/admin - admin dashboard
+ http://localhost:8080/api - API doc&playground built with swagger

Dummy data contains a user `admin` with password `loyalty`.

### General workflow
+ On checkout, identify the client's loyalty account using either account ID or email address.
+ If that account has loyalty balance available (internally called coins) you may suggest the client to use some of these coins as a discount.
+ On purchase begin freeze the used coins
+ On purchase fail release any frozen coins
+ On purchase success deduct any frozen coins and award coins if any

If configured, the app may track the expiration date of the loyalty accounts, notify the users wia email and clean-up the data automatically. This is done using the commands from the `app:` namespace.

### Flowchart
![workflow](/doc/img/workflow.png)
### Integration
In order to integrate this service with the e-commerce app or website you already have you'll need to develop a front-end component handling the loyalty data (account, reward, coins usage) on checkout, as well as a back-end component, handling requests from the front-end component, as well as serving as an API client for the loyalty service.


## Limitations
Currently, the service only handles storing, serving and matching the data of its scope - loyalty accounts, transactions and resulting coins balance. It will not validate the validity of the transactions performed and assumes that part is handled by the client (store).
