# Live Production Deployment Rule

The user explicitly requires: **"Every time work on live production server not local environment."**

Whenever code or database changes are made:
1. Make and verify changes locally to ensure tests pass and assets build cleanly (`npm run build`).
2. **Immediately sync and deploy all changes directly to the live production server**:
   - **Host:** `213.218.240.121`
   - **SSH User:** `bhaissh`
   - **Site Path:** `/home/bhai/htdocs/srv1070026.hstgr.cloud`
   - **Domain:** `https://office.posterit.in`
3. Execute necessary commands on the live server:
   - Run `php artisan migrate --force` if migrations exist.
   - Run `php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache` so live site reflects changes immediately.
4. Verify the live site directly so the user sees results on `office.posterit.in`.
