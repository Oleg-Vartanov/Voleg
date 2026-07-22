#!/bin/sh

# Exit on error
set -e

npm install
npm run build-only
npm run dev -- --host