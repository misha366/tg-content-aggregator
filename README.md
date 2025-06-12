# Telegram Content Aggregator


> docker compose run --rm artisan parse link

### how to set up locally?
- > git clone 
- create mysql.env & src/.env (redis queue's!!!) files
- > docker compose build
- > docker compose run --rm composer install
- > docker compose run --rm artisan key:generate
- > docker compose run --rm artisan migrate
- parse some images from pinterest
- configure Scheduler in console.php and run `plan` container
- enjoy the application!!!


the parser requires the pyexiv2 package, which won't work on alpine, so for `artisan` I use bookworm

### thanks
thanks to @sean1832 for the parser (https://github.com/sean1832/pinterest-dl), that I used in my project

