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

# Insert MOD_EW2OPENAPI number
RUN cp /opt/ew_env/params/earthworm.d /opt/ew_env/params/earthworm.d.bck \
  && LINE_N=$(grep -n 'Module   ' /opt/ew_env/params/earthworm.d | tail -1 | sed 's/:.*//') \
  && N_MOD=$(grep -n 'Module   ' /opt/ew_env/params/earthworm.d | tail -1 | awk '{print $4}') \
  && { head -n ${LINE_N} /opt/ew_env/params/earthworm.d && echo " Module   MOD_EW2OPENAPI        $(( ${N_MOD} + 1 ))" && tail -n +$(( ${LINE_N} + 1 )) /opt/ew_env/params/earthworm.d; } > /opt/ew_env/params/earthworm.d.new \
  && mv /opt/ew_env/params/earthworm.d.new /opt/ew_env/params/earthworm.d 

# Run Hyp2000
ENTRYPOINT ["bash", "/opt/entrypoint.sh"]
