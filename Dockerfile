# FROM debian:stretch
FROM debian:stretch-slim
# FROM bitnami/minideb:stretch

# Authors: Valentino Lauciani and Matteo Quintiliani

LABEL maintainer="Valentino Lauciani <valentino.lauciani@ingv.it>"

ENV DEBIAN_FRONTEND=noninteractive
ENV INITRD No
ENV FAKE_CHROOT 1

ENV EW_INSTALL_INSTALLATION="INST_INGV"
ENV EW_INSTALL_HOME="/opt"
ENV EW_INSTALL_VERSION="earthworm"
ENV EW_INSTALL_BITS=64
ENV EW_RUN_DIR="$EW_INSTALL_HOME/$EW_INSTALL_VERSION"

# Deploy token for ew2openapi repository
ENV DEPLOY_TOKEN="gitlab+deploy-token-1"
ENV DEPLOY_SECRET="9c5vS1x_YsFC4LLHE4sS"

# Set 'root' pwd
RUN echo root:toor | chpasswd

# Set .bashrc
RUN echo "" >> /root/.bashrc \
     && echo "##################################" >> /root/.bashrc \
     && echo "alias ll='ls -l --color'" >> /root/.bashrc \
     && echo "" >> /root/.bashrc \
     && echo "export LC_ALL=\"C\"" >> /root/.bashrc \
     && echo "" >> /root/.bashrc \
	 && echo "export EW_INSTALL_INSTALLATION=\"$EW_INSTALL_INSTALLATION}\"" >> /root/.bashrc \
	 && echo "export EW_INSTALL_HOME=\"${EW_INSTALL_HOME}\"" >> /root/.bashrc \
	 && echo "export EW_INSTALL_VERSION=\"${EW_INSTALL_VERSION}\"" >> /root/.bashrc \
	 && echo "export EW_INSTALL_BITS=${EW_INSTALL_BITS}" >> /root/.bashrc \
	 && echo "export EW_RUN_DIR=\"$EW_INSTALL_HOME/$EW_INSTALL_VERSION\"" >> /root/.bashrc \
     && echo "" >> /root/.bashrc

WORKDIR /opt

# Download and install glibc
# install necessary packages
# checkout necessary Earthworm repository directory
# checkout and compile ew2openapi
# remove unnecessary packages
RUN apt-get clean \
		&& apt-get update \
		&& apt-get install -y \
		subversion \
		make \
		gfortran \
		cmake \
		git \
		libcurl4-openssl-dev \
		dh-autoreconf \
		autotools-dev \
		&& apt-get clean \
		&& svn checkout --depth empty svn://svn.isti.com/earthworm/trunk  $EW_INSTALL_HOME/$EW_INSTALL_VERSION \
		&& cd $EW_INSTALL_HOME/$EW_INSTALL_VERSION/ \
		&& svn update --set-depth infinity include  \
		&& svn update --set-depth infinity lib \
		&& svn update --set-depth empty bin \
		&& svn update --set-depth infinity environment \
		&& svn update --set-depth infinity params \
		&& svn update --set-depth empty src \
		&& svn update --set-depth infinity src/libsrc \
		&& svn update --set-depth empty src/seismic_processing \
		&& svn update --set-depth infinity src/seismic_processing/hyp2000 \
		&& cp $EW_INSTALL_HOME/$EW_INSTALL_VERSION/environment/earthworm.d $EW_INSTALL_HOME/$EW_INSTALL_VERSION/environment/earthworm_global.d $EW_INSTALL_HOME/$EW_INSTALL_VERSION/params/ \
		&& cd $EW_INSTALL_HOME/$EW_INSTALL_VERSION/src/libsrc \
		&& bash -c '. $EW_INSTALL_HOME/$EW_INSTALL_VERSION/environment/ew_linux.bash && make -f makefile.unix' \
		&& cd $EW_INSTALL_HOME/$EW_INSTALL_VERSION/src/seismic_processing/hyp2000 \
		&& bash -c '. $EW_INSTALL_HOME/$EW_INSTALL_VERSION/environment/ew_linux.bash && make -f makefile.unix' \
		&& cd $EW_RUN_DIR \
		&& git clone https://${DEPLOY_TOKEN}:${DEPLOY_SECRET}@gitlab.rm.ingv.it/earthworm/ew2openapi.git/ \
		&& cd ew2openapi \
		&& git submodule update --init \
		&& cd json-c \
		&& sh autogen.sh \
		&& ./configure --prefix=`pwd`/build CFLAGS="-std=c99" \
		&& make \
		&& make install \
		&& cd - \
		&& mkdir -p rabbitmq-c/build \
		&& cd rabbitmq-c/build \
		&& cmake -DENABLE_SSL_SUPPORT=OFF .. \
		&& cmake --build . \
		&& cd - \
		&& cd liblo \
		&& ./autogen.sh --enable-static \
		&& make \
		&& cd - \
		&& bash -c '. $EW_INSTALL_HOME/$EW_INSTALL_VERSION/environment/ew_linux.bash && make -f makefile.unix static' \
		&& cp $EW_INSTALL_HOME/$EW_INSTALL_VERSION/bin/ew2openapi ./ \
		&& bash -c '. $EW_INSTALL_HOME/$EW_INSTALL_VERSION/environment/ew_linux.bash && mkdir -p $EW_LOG' \
		&& cd $EW_INSTALL_HOME/$EW_INSTALL_VERSION/ \
		&& rm -fr .svn src include lib ew2openapi \
		&& apt-get remove -y \
		subversion \
		make \
		gfortran \
		cmake \
		git \
		libcurl4-openssl-dev \
		dh-autoreconf \
		autotools-dev \
		&& apt-get clean

# Copy file(s)
WORKDIR /opt
COPY entrypoint.sh /opt

# Run Hyp2000
ENTRYPOINT ["bash", "/opt/entrypoint.sh"]
