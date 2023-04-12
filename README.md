[![License](https://img.shields.io/github/license/INGV/hyp2000.svg)](https://github.com/INGV/hyp2000/blob/master/LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/INGV/hyp2000.svg)](https://github.com/INGV/hyp2000/issues)

[![Docker build](https://img.shields.io/badge/docker%20build-from%20CI-yellow)](https://hub.docker.com/r/ingv/hyp2000)
![Docker Image Size (latest semver)](https://img.shields.io/docker/image-size/ingv/hyp2000?sort=semver)
![Docker Pulls](https://img.shields.io/docker/pulls/ingv/hyp2000)

[![CI](https://github.com/INGV/hyp2000/actions/workflows/docker-image.yml/badge.svg)](https://github.com/INGV/hyp2000/actions)[![GitHub](https://img.shields.io/static/v1?label=GitHub&message=Link%20to%20repository&color=blueviolet)](https://github.com/INGV/hyp2000)

# hyp2000

Docker used to run HYP2000: determines earthquake locations and magnitudes from seismic network data like first-arrival P and S arrival times, amplitudes and coda durations.

More info here:
- https://www.usgs.gov/software/hypoinverse-earthquake-location

## Quickstart
### Clone the repository
First, clone the git repositry:
```
$ git clone https://github.com/INGV/hyp2000.git
$ cd hyp2000
```

### Docker image
To obtain *hyp2000* docker image, you have two options:

#### 1) Get built image from DockerHub (*preferred*)
Get the last built image from DockerHub repository:
```
$ docker pull ingv/hyp2000:latest
```

#### 2) Build by yourself
```
$ docker build --tag ingv/hyp2000 . 
```

in case of errors, try:
```
$ docker build --no-cache --pull --tag ingv/hyp2000 . 
```

### Run docker
Run:
```
$ docker run -v $(pwd)/example:/opt/data ingv/hyp2000 italy2000.hyp 
```


#### Docker CLI
To override the `ENTRYPOINT` directive and enter into the Docker images, run:
```
$ docker run -it --entrypoint=bash ingv/hyp2000
```

To test **hyp2000** by hand:
```
$ docker run -it --entrypoint=bash -v $(pwd)/example:/opt/data ingv/hyp2000
bash-4.4# cd /opt/data/input/
bash-4.4# cat italy2000.hyp | hyp2000
```

# Contribute
Thanks to your contributions!

Here is a list of users who already contributed to this repository: \
<a href="https://github.com/ingv/hyp2000/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=ingv/hyp2000" />
</a>

# Author
(c) 2023 Valentino Lauciani valentino.lauciani[at]ingv.it

(c) 2023 Matteo Quintiliani matteo.quintiliani[at]ingv.it

Istituto Nazionale di Geofisica e Vulcanologia, Italia
