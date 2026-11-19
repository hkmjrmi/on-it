# GitHub App setup

OnIT talks to GitHub through a GitHub App. The App creates a GitHub issue when a Coordinator or Programmer clicks **Send to GitHub**, and GitHub tells OnIT through a webhook when that issue is closed or reopened.

These steps set it up on a personal account for testing. For OnPaySB, repeat them under the organisation's settings and point `GITHUB_REPOSITORY` at the real repo.

## What the webhook changes

| GitHub event | OnIT change |
| --- | --- |
| Issue closed as completed (for example by a merged PR that links it) | Progress → Ready to Release |
| Issue closed as not planned | Status → Won't Fix |
| Issue reopened | Progress → In Development, and Status → Open if it was Won't Fix |

Changes made by the webhook show in the activity log as the **GitHub** user. CGE still closes the ticket after the fix is released.

## 1. Get a public URL for local testing

GitHub has to reach the webhook. With Herd, share the site:

```sh
herd share
```

Note the HTTPS URL it prints, for example `https://abc123.sharedwithexpose.com`. The webhook URL is that URL plus `/api/github/webhook`.

## 2. Create a test repository

Create a repository on your personal account, for example `on-it-sandbox`. It can be private.

## 3. Create the GitHub App

Go to **GitHub → Settings → Developer settings → GitHub Apps → New GitHub App** and fill in:

| Field | Value |
| --- | --- |
| GitHub App name | Any unique name, for example `OnIT Sandbox (yourname)` |
| Homepage URL | Your OnIT URL |
| Webhook → Active | Ticked |
| Webhook URL | `https://<share-url>/api/github/webhook` |
| Webhook secret | A long random string. Keep it for `GITHUB_WEBHOOK_SECRET` |
| Repository permissions → Issues | Read and write |
| Repository permissions → Metadata | Read-only (set automatically) |
| Subscribe to events | Issues |
| Where can this GitHub App be installed? | Only on this account |

Click **Create GitHub App**, then:

1. Note the **App ID** at the top of the App's page.
2. Under **Private keys**, click **Generate a private key**. A `.pem` file downloads.
3. Move it to `storage/app/private/github-app.pem` in this project. That folder is git-ignored.

## 4. Install the App on the test repository

On the App's page, open **Install App**, choose your account, select **Only select repositories**, pick the test repo and install.

The browser ends on a URL like `https://github.com/settings/installations/12345678`. The number at the end is the **installation ID**.

## 5. Configure OnIT

Add to `.env`:

```dotenv
GITHUB_APP_ID=123456
GITHUB_APP_INSTALLATION_ID=12345678
GITHUB_APP_PRIVATE_KEY_PATH=storage/app/private/github-app.pem
GITHUB_REPOSITORY=yourname/on-it-sandbox
GITHUB_WEBHOOK_SECRET=the-secret-from-step-3
```

On a server where a key file is awkward, set `GITHUB_APP_PRIVATE_KEY` to the key's contents instead, with line breaks written as `\n`.

Then clear the config cache if it is cached:

```sh
php artisan config:clear
```

## 6. Try it

1. Log in as a Coordinator or Programmer and open an open ticket.
2. Click **Send to GitHub**. The ticket shows the GitHub issue number under Details.
3. In GitHub, close the issue as completed. The ticket's progress moves to Ready to Release.
4. Reopen it, then close it as not planned. The ticket's status moves to Won't Fix.

If nothing changes, check **Advanced → Recent Deliveries** on the App's page. A `401` means the webhook secret does not match.

## Moving to OnPaySB

Create a new App under **OnPaySB → Settings → Developer settings → GitHub Apps** with the same settings, install it on `onpay-next`, and update the five `.env` values. Tickets already linked to the test repo keep their old links.
