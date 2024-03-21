# Guestbook symfony test

clone the project

`git clone ...`

`cd my-project/`

run composer install:

`symfony composer install`

displays information about the project:

`symfony console about`

copy .env file, make .env.local file
customize variables :

DATABASE_URL => postgresql

MAILER_DSN => mailtrap

AKISMET_KEY => askimet key

start the local web server :

`symfony server:start -d`

for bus messenger, run :

`symfony console messenger:consume async -vv`

compile asset with asset mapper, run :

`symfony console asset-map:compile`

Minify asset, run :

`symfony console app:minify`

Clean rejected comments, run :

`symfony console app:comment:cleanup`

url API Platform : /api

### Single page application :
directory : spa

`cd spa`

run `npm install`

run web server `symfony server:start -d --passthru=index.html`

to compil asset and connect to API platform data, run :

`API_ENDPOINT=\`symfony var:export SYMFONY_PROJECT_DEFAULT_ROUTE_URL --dir=..\` symfony run -d --watch=webpack.config.js ./node_modules/.bin/encore dev --watch`

