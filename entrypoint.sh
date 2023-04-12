#!/bin/bash

# Initialize Earthworm variables
. $EW_INSTALL_HOME/$EW_INSTALL_VERSION/environment/ew_linux.bash

echo Your container args are: "$@"

# docker run -v $(pwd)/example:/opt/data hyp2000 italy2000.hyp

if [ -z ${DIR_DATA} ]; then
    DIR_DATA=/opt/data
fi
echo "DIR_DATA=${DIR_DATA}"

if [ -d ${DIR_DATA} ]; then
	
	# 
	DIR_OUTPUT=${DIR_DATA}/output
	if [ -d ${DIR_DATA}/output ]; then
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
			cat ${FILE_INPUT} | hyp2000 > ${DIR_OUTPUT}/output.log 2> ${DIR_OUTPUT}/output.err
			RET=${?}
			echo "RET=${RET}"
			# Create json from arc by ew2openapi
			# N.B. Clean spurious characters substituting with spaces
			cat ${DIR_OUTPUT}/hypo.arc | tr '\0' ' ' | ew2openapi TYPE_HYP2000ARC - ${DIR_OUTPUT}/hypo.json
		else
			echo " The \"${DIR_INPUT}/${1}\" doesn't exist." >&2
			echo ""
			exit 1
		fi
	else
		echo " the \"${DIR_DATA}/input\" doesn't exist." >&2
		echo ""
		exit 1
	fi
	
else
	echo " the \"${DIR_DATA}\" doesn't exist." >&2
        echo ""
        exit 1
fi

