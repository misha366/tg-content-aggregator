# Telegram Content Aggregator

LIVE DEMO - https://t.me/itmemesaggregator

A bot built with Laravel that can automatically parse image content (e.g. from Pinterest) and publish it
to Telegram at random times, with a prewritten caption and premium emoji - imitating human behavior.

Posting is done via the user's own Telegram account. It doesn't interfere with normal usage and requires
absolutely no manual actions for setup. You only need to define the posting details in `src/routes/console.php` and
occasionally review the published content.

## How to set up locally
1. `git clone https://github.com/misha366/tg-content-aggregator`
2. create mysql.env & src/.env (use redis queue's!!!) files
3. `docker compose build` + `docker compose run --rm composer install`
   \+ `docker compose run --rm artisan key:generate` + `docker compose run --rm artisan migrate`
4. Parse some images from pinterest via `docker compose run --rm artisan parse link-to-your pin`
    - example: `docker compose run --rm artisan parse https://www.pinterest.com/pin/100134791709526551/`
    - if you are running this command for the first time, it may take some time to load all the modules.
5. Customize your message template in `src/app/Jobs/PostJob.php` and add premium emoji if you want
   (as per example in code)
6. Run `docker compose run --rm artisan create-madeline-session` before the next step
7. configure autopost settings in src/routes/console.php, run the plan container (`docker compose up plan`)
   and enjoy autoposting
    - Before this step, make sure that in cron/laravel-schedule the line ending is in LF format!!!_

p.s. Although it's not something you need to do often, it's a good idea to occasionally parse images related to your
channel's theme to replenish the storage — about once a month is usually enough.

## Commands

- `docker compose run --rm artisan create-madeline-session`

Creates a session for MadelinProto so that the userbot can function properly. Once launched, 
follow the instructions provided.

- `docker compose run --rm artisan parse your-pinterest-link`

Downloads image and all their recommendations from pinterest in high quality from Pinterest. It uses
perceptual hash (pHash) to detect and skip duplicates, that have already been recorded in the storage.
Unique images are saved into storage, their phashes are stored in the database for future duplicate checks.

- `php artisan post from to peer --per-day=N`

! Since this command uses Redis (to dispatch jobs to the Redis queue), you should not run it
from the artisan container, as it doesn't have the Redis driver installed.
In general, you don't need to run this command manually - it's designed to be triggered daily
by the Laravel Scheduler via `Schedule`. However, if you do want to run it manually, you can:
1. Open a shell in the `plan` container: `docker compose exec plan sh`; and run the command from there
2. Alternatively, install Redis drivers in the `artisan` container and run the command from it.

The command schedules when posts will be published and creates jobs for each post. The post times are 
distributed evenly throughout the day with realistic (human-like) intervals.

It adds the specified number of jobs (via `--per-day=N` arg) to the queue for the current day, which will
then be processed by the worker inside the `plan` container.

Default scheduled execution (in `src/routes/console.php`) is:
```php
Schedule::command('post "6:00" "22:00" "@itmemesaggregator" --per-day=20')->dailyAt('5:59');
```

### how does autoposting work?
When running:
```shell
docker compose up plan
```
The `plan` container starts `supervisord` which manages:
- A **queue worker**, constantly watching Redis for jobs (PostJob)
- A **cron daemon**, running php artisan schedule:run every minute

When scheduled time is reached (e.g. 5:59), Laravel Scheduler triggers the `post`
command. That command dispatches multiple post jobs with random delays to simulate
human-like posting.

The queue worker gradually executes the jobs throughout the day.

### Helpful logs:

- **Important:** `src/storage/logs/worker.log` - here you can track the progress of posting tasks
- **Important:** `src/storage/logs/scheduler.log` - here you can check if the command for scheduling 
    tasks for the day is running - `post`
- `src/supervisord.log` - supervisor log
- `src/storage/logs/crond.log` - cron errors

### Technologies
Main stack:
- PHP 8.2
- Laravel 12
- MariaDB 10.6
- MadelineProto 8
- Redis 8.0.2 (for queues)
- Python 3 (for some modules)

DevTools:
- Laravel Pint

### Environment Note
The parser requires the pyexiv2 package, which won't work on alpine, so for `artisan` container I use bookworm


### Special Thanks
Thanks to [@sean1832](https://github.com/sean1832) for the parser (https://github.com/sean1832/pinterest-dl), that I
used in my project


<i><small>If you have any problems with the launch or you see a controversial point in the code, you can always open an issue or write to me at misham.php@gmail.com</small></i>