# Temple Website Setup Guide

## Step-by-step guide to gather the details we will need later

This guide is written for a beginner. You do not need to do everything now.
Keep this file and PDF safe. Once the website is ready for final deployment and email setup,
follow these steps one by one and send the collected details back.

## What this guide covers

### What you will eventually need from your side

- Hosting details
- Email details
- Domain and DNS details
- Admin and support decisions

### Hosting details

- Hostinger hPanel login
- Website domain name
- PHP version used on hosting
- Database host, database name, username, and password

### Email details

- Which email service you want to use
- Zoho Mail details if you choose Zoho
- SMTP host, port, username, password, and encryption type
- Which email address should receive admin notifications

### Domain and DNS details

- Where the domain is managed
- Access to DNS records
- MX, SPF, DKIM, and optional DMARC records

### Admin and support decisions

- Main admin email address
- Optional no-reply email address
- Who should receive contact and donation alerts

Since you mentioned you may use Zoho with your domain, this guide is written so that
the future setup works well with Hostinger and Zoho Mail.

## Phase 1: Get access to the basic accounts first

1. Make sure you can log in to your Hostinger account.
2. Open hPanel and confirm the website domain is visible there.
3. Check whether your domain DNS is also managed inside Hostinger or somewhere else.
4. Keep your Hostinger username and login email in one safe place.
5. Do not share passwords in normal chat. When we need them later, use a safe method.

### Collect these basics

- Domain name: your full domain, for example exampletemple.org
- Hostinger access: hPanel login and confirmation you can open the Websites area
- DNS control: whether DNS records are changed in Hostinger or another provider

## Phase 2: Create the email plan before we connect real email features

You do not have to buy or configure everything immediately. First decide which email addresses
you want the website to use.

### Recommended email addresses

- admin@yourdomain.com for admin login, alerts, and password reset
- noreply@yourdomain.com for website-generated emails
- support@yourdomain.com only if you want a separate public contact mailbox

### Decisions to make

- Which one will receive contact form alerts
- Which one will receive donation notice alerts
- Should password reset emails come from noreply or admin

For this project, the most practical setup is usually admin@yourdomain.com for notifications and admin use,
plus noreply@yourdomain.com for automated website emails.

## Phase 3: If you choose Zoho Mail, follow this step-by-step path

1. Create or log in to your Zoho account.
2. Choose the Zoho Mail plan you want to use.
3. Add your domain inside Zoho Mail.
4. Verify domain ownership by adding the record Zoho gives you.
5. Create the mailboxes you want, such as admin@ and noreply@.
6. Add the MX records Zoho gives you so your domain receives mail through Zoho.
7. Add SPF and DKIM records for better delivery.
8. Add DMARC later if you want stronger email protection.
9. Open the Zoho SMTP settings page and keep the SMTP details ready for us.

### Zoho details to save

- SMTP host
- SMTP port
- Encryption type, such as TLS or SSL
- SMTP username, usually the full email address
- SMTP password or app password

If Zoho offers an app password or a safer SMTP-specific password, use that instead of your normal mailbox password.

## Phase 4: DNS records you should expect to add

These records are normally added where your domain DNS is managed. If your DNS is managed in Hostinger,
you will add them there. If the domain points elsewhere, you will add them in that provider instead.

- Verification record: proves to Zoho that you own the domain
- MX records: route incoming email to Zoho
- SPF record: helps prevent spam and spoofing
- DKIM record: signs outgoing mail for trust
- DMARC record: optional but recommended later for extra protection

Take screenshots or copy the exact DNS values when you add them. That makes troubleshooting much easier later.

## Phase 5: Database and hosting details to collect before final deployment

1. Open Hostinger hPanel.
2. Go to the database area for your website.
3. Create or identify the production MySQL database.
4. Save the database host, database name, username, and password.
5. Check which PHP version is active for the domain.
6. Confirm the website root folder where the files will be uploaded.

### Production details to collect

- DB host
- DB name
- DB username
- DB password
- PHP version

## Final checklist: What to send back later when you are ready

- Your domain name
- Hostinger hPanel access confirmation
- Where DNS is managed
- Production database host, name, user, and password
- Admin email address to use for the site
- Optional no-reply email address
- Zoho SMTP host, port, username, password or app password, and encryption type
- Confirmation that MX, SPF, and DKIM were added and verified
- Which email should receive contact, donation, and gallery submission alerts

If something in this guide feels unclear later, that is okay. Just complete whatever you can,
and we will do the remaining setup step by step together.
