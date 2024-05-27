# MATI Superchats Streamer & Archiver
This web application is used to monitor, display, and archive superchats from the MATI streams in real time.
* Superchats can be viewed in real time on https://mati.x10.mx.
* Superchat archives can be downloaded from the [Releases page](https://github.com/iCantSneed/mati-superchats/releases).

## Development
### One-Time Requirements
* Use the docker container and docker-compose files provided in this repository along with Visual Studio Code's devcontainers.
* Install dependencies
  ```bash
  composer install
  ```
* Set up the database
  ```bash
  php bin/console doctrine:migrations:migrate
  ```
* TODO load dummy data

### During Development

* Monitor for and record new superchats during a livestream by running
  ```bash
  php bin/console mati:stream
  ```
* To launch the web UI, first start apache by running
  ```bash
  apache2ctl start
  ```
  and navigating to the URL that Visual Studio Code provided, most likely http://localhost:8080.

## Deployment
We assume that the app will be deployed to `/srv/mati`.

### One-Time Requirements
* Create a symlink from the public_html-equivalent to `/srv/mati/html`.
* Create a cronjob which every 10 minutes runs
  ```bash
   php /srv/mati/bin/console mati:stream
   ```

### First Time & During Development
* If prod `DEPLOYKEY` changes, it will need to be updated in repo secrets and the file `/srv/mati/.deploykey` needs to be set to `<?php return "${DEPLOYKEY}";`.
* If `.env.local.prod.template` changes, it will need to be manually uploaded to `/srv/mati/.env.local` and adjusted accordingly.
