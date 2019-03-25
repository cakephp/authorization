FROM markstory/cakephp-docs-builder

COPY docs /data/docs

RUN cd /data/docs-builder && \
  # TODO repeat website for each version
  make website SOURCE=/data/docs DEST=/data/website INDEX_PREFIX='authorization-11' && \
  # Move the generated html in place.
  make move-website DEST=/var/www/html/1.1
