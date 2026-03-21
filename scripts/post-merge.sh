#!/bin/bash
set -e

# Post-merge setup script
# Runs automatically after each task agent merge.
# Must be non-interactive (stdin is closed).

echo "Running npm install..."
npm install --no-audit --no-fund --prefer-offline 2>&1

echo "Building JS and CSS..."
npm run build 2>&1

echo "Post-merge setup complete."
