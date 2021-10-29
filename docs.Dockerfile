# Generate the HTML output.
FROM markstory/cakephp-docs-builder as builder

COPY docs /data/docs

ENV LANGS="en es fr"

# Build docs with sphinx
RUN cd /data/docs-builder && \
  make website LANGS=$LANGS SOURCE=/data/docs DEST=/data/website

# Build a small nginx container with just the static site in it.
FROM nginx:1.15-alpine

# Configure search index script
ENV LANGS="en es fr"
ENV SEARCH_SOURCE="/data/source/"
ENV SEARCH_URL_PREFIX="/authorization/2"

# Janky but we could extract this into an image we re-use.
RUN apk add --update php

COPY --from=builder /data/website /data/website
COPY --from=builder /data/docs-builder/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=builder /data/docs-builder/scripts/ /etc/nginx/conf.d/default.conf

# Copy the search index script, and source files over.
COPY --from=builder /data/docs-builder/scripts/populate_search_index.php /data/populate_search_index.php
COPY --from=builder /data/docs /data/docs

COPY run.sh /data/run.sh

# Move files into final location
RUN cp -R /data/website/html/* /usr/share/nginx/html \
  && rm -rf /data/website/

RUN ln -s /usr/share/nginx/html /usr/share/nginx/html/2.x

CMD ["/data/run.sh"]
