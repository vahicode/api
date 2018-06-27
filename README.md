# VaHI backend API

[VaHI](https://vahi.eu/) is a standardized grading system for limbal stem cell deficiency.

> This is the `api` repository which holds [our backend API](https://api.vahi.eu/).

For more information on what VaHI does/is, please check the website: [vahi.eu](https://vahi.eu/).

## About

The **api** respository holds VaHI's backend API.  
It handles things like authentication, storing ratings, users, and eye data.

This API is written in PHP on top of [the Slim framework](https://www.slimframework.com/). 
It uses JSON web tokens with [slim-jwt-auth](https://github.com/tuupola/slim-jwt-auth) as authentication middleware.

## System Requirements
To run your own instance of this API, you'll need:

 - PHP 7 or above
 - composer
 - A database (we use MySql/MariaDb)

## Installation

 - Clone the respository and install through composer:

```
git clone git@github.com:vahicode/api
cd api
composer install
composer dump-autoload -o
```

 - Create a database and [run this script](https://github.com/vahicode/api/blob/master/scripts/sql/structure.sql) to create the database structure.
 - Configure your webserver to serve [the public folder](https://github.com/vahicode/api/tree/master/public) as webroot.
 - Create a directory `i` (or a symlink) under the webroot to store uploaded pictures. Make sure it's writeable by the web server.
 - Configure the following environment variables:
 
| Variable  | Description |
| ------------- | ------------- |
| **DB_HOST**    | Hostname or IP address of your database server. |
| **DB_DB**      | Name of the database to use                     |
| **DB_USER**    | Username to connect to the database             |
| **DB_PASS**    | Password to connect to the database             |
| **JWT_SECRET** | JSON Web Token secret                           |
| **SITE**       | URL of your VaHI frontend                       |
| **ORIGIN**     | The origin to be used in CORS headers           |

## Contribute

Your pull request are welcome here. If you have any questions, please [create an issue](https://github.com/vahicode/api/issues/new).

## License
VaHI is licensed under the MIT license. See [the License file](https://github.com/vahicode/api/blob/master/LICENSE) for more information.
