FROM solr:8.11.2

# Build arguments with defaults
ARG SOLR_HOST=localhost
ARG SOLR_PORT=8983

USER root

# Install dependencies with error handling and cleanup
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
       sudo \
       vim \
       sed; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /var/cache/apt/archives/*

# Set environment variables
ENV SOLR_INSTALL_DIR=/opt/solr
ENV SOLR_HOME=$SOLR_INSTALL_DIR/server/solr
ENV SOLR_HOST=$SOLR_HOST
ENV SOLR_PORT=$SOLR_PORT

# Copy core directories to configsets
COPY data_dir_setup/solr7/series ${SOLR_HOME}/configsets/series
COPY data_dir_setup/solr7/genealogy ${SOLR_HOME}/configsets/genealogy
COPY data_dir_setup/solr7/grouped_works_v2 ${SOLR_HOME}/configsets/grouped_works_v2
COPY data_dir_setup/solr7/lists ${SOLR_HOME}/configsets/lists
COPY data_dir_setup/solr7/course_reserves ${SOLR_HOME}/configsets/course_reserves
COPY data_dir_setup/solr7/open_archives ${SOLR_HOME}/configsets/open_archives
COPY data_dir_setup/solr7/website_pages ${SOLR_HOME}/configsets/website_pages
COPY data_dir_setup/solr7/events ${SOLR_HOME}/configsets/events

# Copy solr.xml to SOLR_HOME
COPY data_dir_setup/solr7/solr.xml ${SOLR_HOME}/solr.xml
RUN set -eux; \
    test -d "${SOLR_HOME}/configsets" || exit 1; \
    chown -R ${SOLR_USER}:${SOLR_GROUP} ${SOLR_HOME}; \
    chown -R ${SOLR_USER}:${SOLR_GROUP} /var/solr

# Add health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://${SOLR_HOST}:${SOLR_PORT}/solr/series/admin/ping && \
        curl -f http://${SOLR_HOST}:${SOLR_PORT}/solr/genealogy/admin/ping && \
        curl -f http://${SOLR_HOST}:${SOLR_PORT}/solr/grouped_works_v2/admin/ping && \
        curl -f http://${SOLR_HOST}:${SOLR_PORT}/solr/lists/admin/ping && \
        curl -f http://${SOLR_HOST}:${SOLR_PORT}/solr/course_reserves/admin/ping && \
        curl -f http://${SOLR_HOST}:${SOLR_PORT}/solr/open_archives/admin/ping && \
        curl -f http://${SOLR_HOST}:${SOLR_PORT}/solr/website_pages/admin/ping && \
        curl -f http://${SOLR_HOST}:${SOLR_PORT}/solr/events/admin/ping || exit 1

# Switch to non-root user
USER ${SOLR_USER}
WORKDIR ${SOLR_HOME}
