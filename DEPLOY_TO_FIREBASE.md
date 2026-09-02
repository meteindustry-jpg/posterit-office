# 🔥 Deploying Laravel to Google Firebase (Free Tier)

Laravel is a dynamic **PHP** backend application. While Firebase Hosting is primarily built for static web files, Google provides an **official integration between Firebase Hosting and Google Cloud Run**:

```
[ User Browser ]
       ↓
[ Firebase Hosting (CDN + Free .web.app Domain) ]
       ↓ (Rewrites dynamic requests)
[ Google Cloud Run (PHP 8.3 + Laravel Backend Container) ]
```

Both Firebase Hosting and Cloud Run are **100% Free** within the Google Cloud Free Tier!

---

## 🚀 Step-by-Step Deployment

### Step 1: Login to Firebase CLI
In your Mac terminal, log into your Google account:
```bash
firebase login
```
*(A browser window will open. Sign in with your Google account and click Allow).*

---

### Step 2: Create a Firebase Project
1. Go to [https://console.firebase.google.com/](https://console.firebase.google.com/).
2. Click **Add project** and name it (e.g. `posterit-office`).
3. Click **Continue** and create the project.

---

### Step 3: Deploy the Laravel Container to Cloud Run
From your project directory in terminal:
```bash
gcloud run deploy posterit-office \
  --source . \
  --region us-central1 \
  --allow-unauthenticated
```
*(If you don't have `gcloud` on your Mac, you can run this command inside the free Google Cloud Shell in your browser at [console.cloud.google.com](https://console.cloud.google.com)).*

---

### Step 4: Connect Firebase Hosting
Once the Cloud Run container is running, deploy Firebase Hosting:
```bash
firebase deploy --only hosting
```

---

### 🎉 Your Live Firebase URLs
Firebase will provide you with two live free HTTPS URLs:
- `https://YOUR-PROJECT-ID.web.app`
- `https://YOUR-PROJECT-ID.firebaseapp.com`

All static CSS/JS is cached globally on Firebase CDN, and all dynamic Laravel pages run through the container!
