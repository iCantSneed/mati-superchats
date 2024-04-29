# MATI Superchats Streamer & Archiver

## Deployment
We assume that the app will be deployed to `/srv/mati`.

### One-Time Requirements
* Create a symlink from the public_html-equivalent to `/srv/mati/html`.
* Create a cronjob which every 10 minutes runs
  ```bash
   php /srv/mati/bin/console mati:stream
   ```
* Create the file `/srv/mati/.deploykey` with the contents `<?php return "${DEPLOYKEY}";`, where DEPLOYKEY matches the secret with the same name.
* Copy `.env.local.prod.template` to `.env.local` and edit appropriately.

### During Development
* If `./html/deploy.php` changes, it will need to be manually uploaded to `/srv/mati/html/deploy.php`.
* If prod `APP_SECRET` changes, it will need to be updated in repo secrets and directly in `/srv/mati/.env.local.php`.
