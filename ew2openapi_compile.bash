#!/bin/bash

# Author: Matteo Quintiliani (matteo.quintiliani [at] ingv.it) Istituto Nazionale di Geofisica e Vulcanologia

# Clone ew2openapi by Deploy Token
DEPLOY_TOKEN="gitlab+deploy-token-1"
DEPLOY_SECRET="9c5vS1x_YsFC4LLHE4sS"
git clone https://${DEPLOY_TOKEN}:${DEPLOY_SECRET}@gitlab.rm.ingv.it/earthworm/ew2openapi.git/
cd ew2openapi

# If you want to compile develop branch
# git checkout develop

# Update git submodule json-c and rabbitmq-c
git submodule update --init

# Configure and build json-c
cd json-c
sh autogen.sh
./configure --prefix=`pwd`/build CFLAGS="-std=c99"
make
make install
cd -
# Configure and build rabbitmq-c
mkdir -p rabbitmq-c/build
cd rabbitmq-c/build
cmake -DENABLE_SSL_SUPPORT=OFF ..
cmake --build .
cd -
# Configure and build ew2openapi
make -f makefile.unix static
# Copy binary in the current directory
cp $EW_INSTALL_HOME/$EW_INSTALL_VERSION/bin/ew2openapi ./
# Create not existing directory
mkdir -p $EW_LOG
echo $EW_RUN_DIR
echo $EW_PARAMS
echo $EW_LOG
echo $EW_INSTALL_HOME/$EW_INSTALL_VERSION/

