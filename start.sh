#! /bin/bash
docker run --rm --detach --volume "$PWD:/app" --env APACHE_LOG_DIR=/app/docker/logs --publish 8000:80 php-app:latest