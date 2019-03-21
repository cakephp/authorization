FROM debian:jessie

ENV DEBIAN_FRONTEND noninteractive

LABEL Description="Create an image to deploy the authentication plugin docs"

RUN apt-get update && apt-get install -y \
    python-pip \
    # Add texlive-latex-recommended texlive-latex-extra for PDF support
    texlive-fonts-recommended \
    texlive-lang-all \
    latexmk \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

RUN apt-get update \
  && apt-get install -y git nginx curl php5 php5-curl \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /data

RUN git clone https://github.com/cakephp/plugin-docs-builder /data/docs-builder \
 && cd /data/docs-builder \
 && pip install -r requirements.txt

COPY docs /data/docs

RUN cd /data/docs-builder && \
  make website SOURCE=/data/docs

RUN rm -rf /var/www/html/* \
  # TODO add more version support here
  && mkdir -p /var/www/html/1.1/ \
  && cp -a /data/website/. /var/www/html/1.1/ \
  && mv /data/nginx.conf /etc/nginx/sites-enabled/default

# forward request and error logs to docker log collector
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
  && ln -sf /dev/stderr /var/log/nginx/error.log

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
