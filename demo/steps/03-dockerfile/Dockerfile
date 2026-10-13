# syntax=ghcr.io/castor-php/twig-dockerfile:0.1
{% extends 'Dockerfile.frankenphp' %}

{% block php_base %}
    {{ parent() }}

RUN apt-get update \
    && apt-get install -y --no-install-recommends figlet \
    && rm -rf /var/lib/apt/lists/*
{% endblock %}
