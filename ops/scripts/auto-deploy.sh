#!/bin/bash

set -e

LOG_DIR="/var/log/tamagotchi"
DEPLOY_SCRIPT="/opt/tamagotchi-api/ops/scripts/deploy.sh"

mkdir -p "$LOG_DIR"

echo "[AutoDeploy] $(date) starting..." >> "$LOG_DIR/auto-deploy.log"

if [ ! -x "$DEPLOY_SCRIPT" ]; then
  echo "[AutoDeploy] deploy.sh not executable, fixing perms" >> "$LOG_DIR/auto-deploy.log"
  chmod +x "$DEPLOY_SCRIPT"
fi

bash "$DEPLOY_SCRIPT" >> "$LOG_DIR/deploy.log" 2>&1

echo "[AutoDeploy] $(date) finished" >> "$LOG_DIR/auto-deploy.log"
