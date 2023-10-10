# Generate the HTML output.
FROM ghcr.io/cakephp/docs-builder as builder

ENV LANGS="en es fr ja"

WORKDIR /data/docs-builder

COPY docs /data/docs

# Build docs with sphinx
RUN make website LANGS="$LANGS" SOURCE=/data/docs DEST=/data/website

# Build a small nginx container with just the static site in it.
FROM ghcr.io/cakephp/docs-builder:runtime as runtime

# Configure search index script
ENV LANGS="en es fr ja"
ENV SEARCH_SOURCE="/usr/share/nginx/html"
ENV SEARCH_URL_PREFIX="/authorization/2"

COPY --from=builder /data/docs /data/docs
COPY --from=builder /data/website/html/ /usr/share/nginx/html/
COPY --from=builder /data/docs-builder/nginx.conf /etc/nginx/conf.d/default.conf

RUN ln -s /usr/share/nginx/html /usr/share/nginx/html/2.x
