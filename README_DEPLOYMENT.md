# Cloud Deployment Guide

This document describes the deployment architecture and step-by-step process for deploying the OGS Connect Laravel application to AWS EC2 with Docker.

---

## Architecture Overview

* **Full Stack:** Laravel 12 (deployed on AWS EC2)
* **Database:** MariaDB (Docker container on same EC2)
* **Process Manager:** tmux (to keep Laravel running in background)
* **Reverse Proxy:** Nginx (via Laravel Sail/Docker)
* **File Storage:** Amazon S3
* **IAM Security:** IAM User with S3 access policy

---

# System Architecture

```
                         INTERNET
                              │
                             ▼
             ┌─────────────────────┐
             │   AWS EC2 (t3.micro)                   │
             │   (Ubuntu 22.04)                           │
             │                                                      │
             │  ┌───────────────┐         │
             │  │ Laravel Sail                   │         │
             │  │ + Nginx                          │         │
             │  │ + PHP-FPM                   │         │
             │  │ + PHP 8.5                      │         │
             │  └───────────────┘         │
             │                    │                                │
             │  ┌───────────────┐         │
             │  │ MariaDB                        │         │
             │  │ (Docker)                        │         │
             │  └───────────────┘         │
             └─────────────────────┘
                              │
                             ▼
             ┌─────────────────────┐
             │   S3 Bucket                                   │
             │   (File Storage)                              │
             └─────────────────────┘
```

---

# 1. Launching EC2 Instance

## Step 1: Create EC2 Instance

1. Go to AWS Console → EC2 → Launch Instance
2. Configure:
   * **Name:** `ogs-server`
   * **AMI:** Ubuntu Server 22.04 LTS
   * **Instance Type:** `t3.micro` (Free Tier eligible)
   * **Key Pair:** Create new or use existing (save the .pem file)

## Step 2: Configure Security Group

| Type | Port | Source |
|------|------|--------|
| SSH | 22 | Your IP or 0.0.0.0/0|
| HTTP | 80 | 0.0.0.0/0 |
| HTTPS | 443 | 0.0.0.0/0 |

## Step 3: Connect to EC2

```bash
ssh -i your-key.pem ubuntu@<EC2-PUBLIC-IP>
```

---

# 2. Installing Required Software

## Step 1: Update and Install Basics

```bash
sudo apt update
sudo apt upgrade -y
```

## Step 2: Install Docker

```bash
sudo apt install -y docker.io
sudo systemctl start docker
sudo systemctl enable docker
sudo usermod -aG docker ubuntu
```

## Step 3: Install Docker Compose

```bash
sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

## Step 4: Install Git

```bash
sudo apt install -y git
```

## Step 5: Install tmux

```bash
sudo apt install -y tmux
```

---

# 3. Setting Up MariaDB with Docker

## Step 1: Create MariaDB Container

```bash
docker run -d \
  --name mariadb \
  --restart always \
  -e MYSQL_ROOT_PASSWORD=root_password \
  -e MYSQL_DATABASE=ogs \
  -e MYSQL_USER=ogs_user \
  -e MYSQL_PASSWORD=IT20OGS \
  -p 3306:3306 \
  -v mariadb_data:/var/lib/mysql \
  mariadb:latest \
  --character-set-server=utf8mb4 \
  --collation-server=utf8mb4_unicode_ci \
  --bind-address=0.0.0.0
```

## Step 2: Grant Database Access

```bash
docker exec mariadb mysql -u root -proot_password -e "GRANT ALL PRIVILEGES ON ogs.* TO 'ogs_user'@'%'; FLUSH PRIVILEGES;"
```

---

# 4. Deploying Laravel Application

## Step 1: Clone Repository

```bash
cd /home/ubuntu
git clone https://github.com/your-repo/OGS-Connect.git
cd OGS-Connect
```

## Step 2: Create .env File

```bash
cp .env.example .env
nano .env
```

Update with your configuration:
```env
APP_NAME="OGS Connect"
APP_ENV=production
APP_KEY=base64:your-app-key
APP_DEBUG=false
APP_URL=http://16.176.2.113

DB_CONNECTION=mysql
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=ogs
DB_USERNAME=ogs_user
DB_PASSWORD=IT20OGS

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=ap-southeast-2
AWS_BUCKET=it20-ogs-s3-bucket
```

## Step 3: Install Dependencies

```bash
composer install --no-dev
composer require league/flysystem-aws-s3-v3 -W
```

## Step 4: Fix File Permissions

```bash
sudo chown -R www-data:www-data storage
sudo chmod -R 777 storage
```

## Step 5: Update Docker Compose Environment

Edit `compose.yaml` to set correct database:
```yaml
environment:
  WWWGROUP: '1000'
  MYSQL_ROOT_PASSWORD: 'root_password'
  DB_HOST: mariadb
  DB_PORT: '3306'
  DB_DATABASE: ogs
  DB_USERNAME: ogs_user
  DB_PASSWORD: IT20OGS
```

## Step 6: Start Docker Containers/Sail for Laravel

```bash
./vendor/bin/sail up -d
```

---

# 5. Database Setup

## Step 1: Import Database

If you have a SQL dump:
```bash
docker exec -i mariadb mysql -u root -proot_password ogs < database/your_dump.sql
```

## Step 2: Create Missing Tables (if needed)

```bash
docker exec -w /var/www/html laravel.test php artisan migrate --force
```

---

# 6. Running with tmux

## Start a tmux session

```bash
tmux new -s laravel
```

## Run Laravel

```bash
cd /home/ubuntu/OGS-Connect
php artisan serve --host=0.0.0.0 --port=80
```

## Detach (Keep Running)

Press `Ctrl + B`, then `D`

## Reattach

```bash
tmux attach -t laravel
```
---

# 7. Configuring Amazon S3

## Step 1: Create S3 Bucket

1. Go to AWS Console → S3 → Create bucket
2. Configure:
   * **Bucket Name:** `it20-ogs-s3-bucket`
   * **Region:** ap-southeast-2
   * **Block Public Access:** Uncheck all

## Step 2: Enable Versioning

Click bucket → Properties → Bucket versioning → Enable

## Step 3: Add CORS Configuration

In bucket → Permissions → CORS:
```json
[
    {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
        "AllowedOrigins": ["*"]
    }
]
```

---

# 8. Creating IAM User for S3

## Step 1: Create IAM User

1. Go to IAM → Users → Add user
2. **User name:** `ogs-s3-user`
3. Programmatic access

## Step 2: Create S3 Policy

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:ListBucket",
                "s3:GetObject",
                "s3:PutObject",
                "s3:DeleteObject"
            ],
            "Resource": [
                "arn:aws:s3:::it20-ogs-s3-bucket",
                "arn:aws:s3:::it20-ogs-s3-bucket/*"
            ]
        }
    ]
}
```

## Step 3: Attach Policy and Save Credentials

---

# 9. Testing S3 Connectivity

## Test via Laravel Tinker

```bash
docker exec -w /var/www/html laravel.test php artisan tinker
>>> Storage::disk('s3')->put('test.txt', 'Hello S3!');
>>> Storage::disk('s3')->get('test.txt');
```

## Test via AWS CLI

```bash
aws s3 ls s3://it20-ogs-s3-bucket/
echo "Test upload" | aws s3 cp - s3://it20-ogs-s3-bucket/test.txt
```
---

# 10. Configuring HTTPS with DuckDNS

## Step 1: Install Certbot and nginx

```bash
sudo apt update
sudo apt install -y nginx certbot python3-certbot-nginx
```

## Step 2: Install acme.sh

```bash
curl https://get.acme.sh | sh -s email=your-email@example.com
```

## Step 3: Get SSL Certificate using DuckDNS

```bash
export DuckDNS_Token='your-duckdns-token'
.acme.sh/acme.sh --issue --dns dns_duckdns -d your-domain.duckdns.org
```

## Step 4: Install SSL Certificate

```bash
sudo mkdir -p /etc/letsencrypt/live/your-domain.duckdns.org
sudo cp ~/.acme.sh/your-domain.duckdns.org_ecc/fullchain.cer /etc/letsencrypt/live/your-domain.duckdns.org/fullchain.pem
sudo cp ~/.acme.sh/your-domain.duckdns.org_ecc/your-domain.duckdns.org.key /etc/letsencrypt/live/your-domain.duckdns.org/privkey.pem
sudo chmod 600 /etc/letsencrypt/live/your-domain.duckdns.org/privkey.pem
```

## Step 5: Configure nginx with SSL

```bash
sudo nano /etc/nginx/sites-available/default
```

Add:
```nginx
server {
    listen 80;
    server_name your-domain.duckdns.org;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.duckdns.org;

    ssl_certificate /etc/letsencrypt/live/your-domain.duckdns.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.duckdns.org/privkey.pem;

    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Step 6: Start Services

```bash
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

## Auto-Renewal Setup

```bash
sudo crontab -e
```

Add:
```
0 0 * * * ~/.acme.sh/acme.sh --renew --dns dns_duckdns -d your-domain.duckdns.org --ecc
```
---

# Summary

This deployment uses AWS EC2 running Laravel with nginx and PHP-FPM. The database (MariaDB) runs in a Docker container. DuckDNS provides the domain name with free SSL certificates from Let's Encrypt. Amazon S3 handles file storage. All services stay running on the EC2 instance 24/7, accessible via HTTPS even when your local machine is turned off.
