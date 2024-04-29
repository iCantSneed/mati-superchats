# MATI Superchats Streamer & Archiver

## Deployment
We assume that the app will be deployed to `/srv/mati`.

### One-Time Requirements
* Create a symlink from the public_html-equivalent to `/srv/mati/html`.
* Create a cronjob which every 10 minutes runs
  ```bash
   php /srv/mati/bin/console mati:stream
   ```

### First Time & During Development
* If `./html/deploy.php` changes, it will need to be manually uploaded to `/srv/mati/html/deploy.php`.
* If prod `DEPLOYKEY` changes, it will need to be updated in repo secrets and the file `/srv/mati/.deploykey` needs to be set to `<?php return "${DEPLOYKEY}";`.
* If `.env.local.prod.template` changes, it will need to be manually uploaded to `/srv/mati/.env.local` and adjusted accordingly.
