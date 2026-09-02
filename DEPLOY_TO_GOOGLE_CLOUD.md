# ☁️ Google Cloud Deployment Guide — Posterit Office

This guide outlines two easiest ways to deploy your **Posterit Office** application on **Google Cloud Platform (GCP)**.

---

## 🌟 Method 1: Google Cloud Run (Recommended — Serverless, Auto SSL, Zero Idle Cost)

Cloud Run runs your application inside a container. It scales to zero when not used, meaning it costs almost **$0/month** for small teams.

### Step 1: Open Google Cloud Console
1. Go to [https://console.cloud.google.com/](https://console.cloud.google.com/) and sign in.
2. Create or select your Google Cloud Project (e.g., `posterit-office`).

### Step 2: Open Google Cloud Shell
1. In the top right corner of the Google Cloud Console, click the **Activate Cloud Shell** icon `>_`.
2. A free Linux terminal will open at the bottom of your browser.

### Step 3: Clone or Upload Your Code
In the Cloud Shell terminal, either clone your Git repository or upload the project ZIP:
```bash
git clone https://github.com/YOUR_USERNAME/YOUR_REPO.git
cd YOUR_REPO
```

### Step 4: Deploy with 1 Command
Run the following command in Cloud Shell:
```bash
gcloud run deploy posterit-office \
  --source . \
  --platform managed \
  --region us-central1 \
  --allow-unauthenticated
```
- When asked to enable Cloud Build or Artifact Registry APIs, type `y` and press **Enter**.
- In 2–3 minutes, Google Cloud will build the container and provide your live HTTPS URL:
  `https://posterit-office-xxxxx-uc.a.run.app`

---

## 🖥️ Method 2: Google Compute Engine (Ubuntu VM — Standard VPS)

If you prefer a traditional Linux server (like a standard VPS where you have full root SSH access):

### Step 1: Create a Virtual Machine
1. Go to **Google Cloud Console > Compute Engine > VM Instances**.
2. Click **Create Instance**.
3. Settings:
   - **Name:** `posterit-server`
   - **Region:** Choose closest to you (e.g. `asia-south1` Mumbai, or `us-central1`).
   - **Machine type:** `e2-small` (2 vCPU, 2 GB memory) or `e2-micro`.
   - **Boot disk:** Click Change -> OS: **Ubuntu**, Version: **Ubuntu 24.04 LTS**.
   - **Firewall:** Check both **Allow HTTP traffic** and **Allow HTTPS traffic**.
4. Click **Create**.

### Step 2: Connect via In-Browser SSH
1. In the VM Instances list, click the **SSH** button next to your new instance.
2. A black terminal window will pop up right in your browser.

### Step 3: Clone Code & Run 1-Click Installer
Inside the SSH window, run:
```bash
git clone https://github.com/YOUR_USERNAME/YOUR_REPO.git /var/www/posterit
cd /var/www/posterit
bash gce-setup.sh
```

The script automatically installs PHP 8.3, Nginx, Composer, SQLite, configures permissions, builds caches, and starts the web server.

Once finished, your site will be live at:
`http://YOUR_VM_EXTERNAL_IP`

---

## 🔒 Free Custom Domain & SSL on Google Cloud

### On Cloud Run:
1. Go to **Cloud Run > Manage Custom Domains**.
2. Click **Add Mapping**, enter your domain name (e.g. `office.yourcompany.com`).
3. Google automatically generates a free Google Managed SSL Certificate.

### On Compute Engine (VM):
Run Certbot for free Let's Encrypt SSL:
```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```
