#!/bin/sh

# Exit on error
set -e

npm install
npm run build-only
exec tail -f /dev/null