# 🚀 Hostinger VPS CloudPanel Deployment Guide — Bhai System

This guide covers deploying **Bhai System** to your active Hostinger VPS with **CloudPanel**.

---

## 🖥️ Server Specifications
- **Server IP:** `213.218.240.121`
- **Hostname:** `srv1070026.hstgr.cloud`
- **Operating System:** Ubuntu 24.04 LTS with CloudPanel
- **CloudPanel URL:** [https://213.218.240.121:8443](https://213.218.240.121:8443)
- **Deployment Archive:** `deploy-hostinger.zip` (already prepared and bundled in this workspace)

---

## 📋 Step-by-Step Deployment Instructions

### Step 1: Open CloudPanel
1. Navigate to **[https://213.218.240.121:8443](https://213.218.240.121:8443)** in your web browser.
2. If your browser shows a self-signed certificate warning, click **Advanced > Proceed to 213.218.240.121**.
3. Sign in with your CloudPanel administrator credentials.

---

### Step 2: Create the Website in CloudPanel
1. In CloudPanel dashboard, click **+ Add Site** in the top right.
2. Select **Create a PHP Site**.
3. Fill in the site details:
   - **Domain Name:** Enter your domain (e.g. `office.yourdomain.com`) or `srv1070026.hstgr.cloud`.
   - **PHP Version:** Select **PHP 8.3** or **PHP 8.4**.
   - **Application:** Select **Laravel**.
   - **Site User:** Choose a user (e.g. `posterit` or `bhai`).
4. Click **Create**.

---

### Step 3: Upload & Extract the Code
1. Inside your site in CloudPanel, click the **File Manager** tab.
2. Navigate to your site directory: `/home/[site-user]/htdocs/[domain]/`.
3. Click **Upload** and select `deploy-hostinger.zip` from your computer:
   - File location: `/Users/samirmete/Desktop/Bhai System/deploy-hostinger.zip`
4. Once uploaded, right-click `deploy-hostinger.zip` and select **Extract**.

---

### Step 4: Configure Environment & Database
Open the **SSH / Terminal** tab inside CloudPanel (or connect via terminal SSH):

```bash
cd /home/[site-user]/htdocs/[domain]

# 1. Create .env configuration
cp .env.example .env

# 2. Generate Application Key
php artisan key:generate

# 3. Create SQLite Database File
touch database/database.sqlite

# 4. Run Migrations
php artisan migrate --force

# 5. Create Storage Symlink
php artisan storage:link

# 6. Optimize Caches for Production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### Step 5: Enable Free SSL Certificate (HTTPS)
1. In CloudPanel, click the **SSL/TLS** tab for your website.
2. Click **New Let's Encrypt Certificate**.
3. Click **Create and Install**.
4. CloudPanel will issue and install a free SSL certificate within seconds with automatic auto-renewal.

---

### 🎉 Your System is Live!
Navigate to `https://[your-domain]` to access **Bhai System** in production!

#### Super Admin Login Credentials:
- **Email / User:** `samir@posterit.com` (or `sam@posterit.com`)
- Use your configured administrator password.
