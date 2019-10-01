# hyp2000

Docker used to run HYP2000: determines earthquake locations and magnitudes from seismic network data like first-arrival P and S arrival times, amplitudes and coda durations.

More info here:
- https://www.usgs.gov/software/hypoinverse-earthquake-location

## Quickstart
### Build docker
```
$ git clone git@gitlab.rm.ingv.it:docker/docker_hyp2000.git
$ cd docker_hyp2000
$ docker build --tag hyp2000:1.0 . 
```

Based on alpine, smaller docker image
```
$ docker build --tag hyp2000:alpine -f DockerfileAlpine .
```

### Run docker
Run:
```
$ docker run -v $(pwd)/example:/opt/data hyp2000:1.0 italy2000.hyp 
```


#### Docker CLI
To override the `ENTRYPOINT` directive and enter into the Docker images, run:
```
$ docker run -it --entrypoint=bash hyp2000:1.0
```

To test **hyp2000** by hand:
```
$ docker run -it --entrypoint=bash -v $(pwd)/example:/opt/data hyp2000:alpine
bash-4.4# . /opt/earthworm/environment/ew_linux.bash
bash-4.4# cd /opt/data/input/
bash-4.4# cat italy2000.hyp | hyp2000
```

# Contribute
Please, feel free to contribute.

# Author
(c) 2019 Valentino Lauciani valentino.lauciani[at]ingv.it

(c) 2019 Matteo Quintiliani matteo.quintiliani[at]ingv.it

Istituto Nazionale di Geofisica e Vulcanologia, Italia
