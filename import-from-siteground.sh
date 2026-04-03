#!/usr/bin/env bash
set -euo pipefail

SSH_HOST="ssh.jnbhealth.com"
SSH_USER="u1050-0yzufsdczrls"
SSH_PORT="18765"
REMOTE_PATH="/home/u1050-0yzufsdczrls/public_html/"
LOCAL_DEST="$(pwd)"

echo "==> Syncing files from SiteGround..."
rsync -avz --progress \
  -e "ssh -p $SSH_PORT -o StrictHostKeyChecking=no" \
  --exclude="wp-content/upgrade/" \
  --exclude="wp-content/backup-db/" \
  --exclude="wp-content/cache/" \
  --exclude="wp-content/w3tc-cache/" \
  --exclude=".git/" \
  --exclude="*.log" \
  "${SSH_USER}@${SSH_HOST}:${REMOTE_PATH}" \
  "$LOCAL_DEST/"
