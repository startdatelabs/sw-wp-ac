#! /bin/sh

docker build --squash --no-cache --compress --build-arg SSH_PRV_KEY="$(cat sync_rsa)" -t sw-wp-ac .

$(aws ecr get-login --no-include-email)

tag=$(git rev-parse HEAD)
docker tag sw-wp-ac:latest 075867688074.dkr.ecr.us-east-1.amazonaws.com/sw-wp-ac:$tag
docker push 075867688074.dkr.ecr.us-east-1.amazonaws.com/sw-wp-ac:$tag

docker tag sw-wp-ac:latest 075867688074.dkr.ecr.us-east-1.amazonaws.com/sw-wp-ac:latest
docker push 075867688074.dkr.ecr.us-east-1.amazonaws.com/sw-wp-ac:latest
