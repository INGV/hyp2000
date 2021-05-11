#!/bin/bash

# Initialize Earthworm variables
. $EW_INSTALL_HOME/$EW_INSTALL_VERSION/environment/ew_linux.bash

echo Your container args are: "$@"

# docker run -v $(pwd)/example:/opt/data hyp2000 italy2000.hyp

DIR_DATA=/opt/data

if [ -d ${DIR_DATA} ]; then
	
	# 
	if [ -d ${DIR_DATA}/output ]; then
		DIR_OUTPUT=${DIR_DATA}/output
		if [ -n "$(ls -A ${DIR_OUTPUT})" ]; then
			rm ${DIR_OUTPUT}/*
		fi
	else 
		mkdir ${DIR_OUTPUT}/
	fi

	#
	if [ -d ${DIR_DATA}/input ]; then
		DIR_INPUT=${DIR_DATA}/input
		if [ -f ${DIR_INPUT}/${1} ]; then
			FILE_INPUT=${1}
			cd ${DIR_DATA}/input
			cat ${FILE_INPUT} | hyp2000
			# Create json from arc by ew2openapi
			# N.B. Clean spurious characters substituting with spaces
			cat ${DIR_OUTPUT}/hypo.arc | tr '\0' ' ' | ew2openapi TYPE_HYP2000ARC - ${DIR_OUTPUT}/hypo.json
		else
			echo " The \"${DIR_INPUT}/${1}\" doesn't exist."
			echo ""
			exit 1
		fi
	else
		echo " the \"${DIR_DATA}/input\" doesn't exist."
		echo ""
		exit 1
	fi
	
else
	echo " the \"${DIR_DATA}\" doesn't exist."
        echo ""
        exit 1
fi

