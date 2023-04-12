FROM matteoquintiliani/ew-sandbox:a4bb3e30

# Authors: Valentino Lauciani and Matteo Quintiliani
LABEL maintainer="Valentino Lauciani <valentino.lauciani@ingv.it>"

ENV DEBIAN_FRONTEND=noninteractive
ENV INITRD No
ENV FAKE_CHROOT 1

# Copy file(s)
WORKDIR /opt
COPY entrypoint.sh /opt

# To use ew2openapi - https://gitlab.rm.ingv.it/caravel/apollo/-/issues/2#note_72867
RUN curl -o /opt/ew_env/params/earthworm_global.d "https://gitlab.com/seismic-software/earthworm/-/raw/master/environment/earthworm_global.d"
RUN curl -o /opt/ew_env/params/earthworm.d "https://gitlab.com/seismic-software/earthworm/-/raw/master/environment/earthworm.d"

# Run Hyp2000
ENTRYPOINT ["bash", "/opt/entrypoint.sh"]
