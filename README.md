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

