#! /bin/bash
set -euo pipefail

if [ ! -f docker/Dockerfile ]; then
  echo "Not found docker/Dockerfile"
  exit 1
fi
cd docker
docker build -t php-app .