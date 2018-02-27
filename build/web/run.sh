#!/usr/bin/env bash

service php5-fpm start && \
  service nginx start && \
  tail -f /var/log/nginx/error.log
