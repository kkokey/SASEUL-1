# saseul-origin

[![Build Status](https://travis-ci.com/Artifriends-inc/saseul-origin.svg?token=bx7563DCtvTV3uXjTfZN&branch=master)](https://travis-ci.com/Artifriends-inc/saseul-origin)
[![Maintainability](https://api.codeclimate.com/v1/badges/75a04e3a006d346bf7f0/maintainability)](https://codeclimate.com/repos/5c3453fb31623b7f870002d8/maintainability)

## Environment
- Anywhere to available to run Docker.

## How to install
### Install Docker 
#### - For Mac
```bash
$ brew cask install docker
```
OR

[[Docker DownLoad Link](https://hub.docker.com/editions/community/docker-ce-desktop-mac)]

#### - For Window
[[Docker Download Link](https://hub.docker.com/editions/community/docker-ce-desktop-windows)]

--- 

### Download SASEUL Origin
```bash
$ git clone git@github.com:Artifriends-inc/saseul-origin.git
```

### Create a configuration file for creating a new network node.
```bash
# run command at saseul-origin folder.
echo -e '
# Genesis Block
GENESIS_COIN_VALUE=1000000000000000
GENESIS_DEPOSIT_VALUE=200000000000000
GENESIS_ADDRESS=0x6f1b0f1ae759165a92d2e7d0b4cae328a1403aa5e35a85

# Account
NODE_PRIVATE_KEY=a745fbb3860f243293a66a5fcadf70efc1fa5fa5f0254b3100057e753ef0d9bb
NODE_PUBLIC_KEY=52017bcb4caca8911b3830c281d10f79359ceb3fbe061c990e043ccb589fccc3
NODE_ADDRESS=0x6f1b0f1ae759165a92d2e7d0b4cae328a1403aa5e35a85

# Tracker
GENESIS_HOST=web
NODE_HOST=web

# Chunk interval
MICRO_INTERVAL_CHUNK=1000000

# Fee rate
FEE_RATE=0.00015
FEE_RATE_MIN=0.0001

# Logs
LOG_PATH=/var/log/saseul-origin
LOG_LEVEL=INFO
' > .env
```

### Create a configuration file for creating a new network master node.
- <b>You have to change <MY_IP></b>
```bash
echo -e '
# Genesis Block
GENESIS_COIN_VALUE=1000000000000000
GENESIS_DEPOSIT_VALUE=200000000000000
GENESIS_ADDRESS=<MY_ACCOUNT_ADDRESS>

# Account
NODE_PRIVATE_KEY=<MY_ACCOUNT_PRIVATE_KEY>
NODE_PUBLIC_KEY=<MY_ACCOUNT_PUBLIC_KEY>
NODE_ADDRESS=<MY_ACCOUNT_ADDRESS>

# Tracker
GENESIS_HOST=<MY_IP>
NODE_HOST=<MY_IP>

# Chunk interval
MICRO_INTERVAL_CHUNK=1000000

# Fee rate
FEE_RATE=0.00015
FEE_RATE_MIN=0.0001

# Logs
LOG_PATH=/var/log/saseul-origin
LOG_LEVEL=INFO
' > .env
```

### Create a configuration file for connect another node.
- <b>You have to change <SASEUL_ORIGIN_GENESIS_HOST_IP> and <MY_IP></b>
    - <b><SASEUL_ORIGIN_GENESIS_HOST_IP></b> : The master node IP has a genesis block.
    - <b><MY_IP></b> : My IP
```bash
echo -e '
# Genesis Block
GENESIS_COIN_VALUE=1000000000000000
GENESIS_DEPOSIT_VALUE=200000000000000
GENESIS_ADDRESS=<SASEUL_ORIGIN_GENESIS_HOST_ACCOUNT_ADDRESS>

# Account
NODE_PRIVATE_KEY=<MY_ACCOUNT_PRIVATE_KEY>
NODE_PUBLIC_KEY=<MY_ACCOUNT_PUBLIC_KEY>
NODE_ADDRESS=<MY_ACCOUNT_ADDRESS>

# Tracker
GENESIS_HOST=<SASEUL_ORIGIN_GENESIS_HOST_IP>
NODE_HOST=<MY_IP>

# Chunk interval
MICRO_INTERVAL_CHUNK=1000000

# Fee rate
FEE_RATE=0.00015
FEE_RATE_MIN=0.0001

# Logs
LOG_PATH=/var/log/saseul-origin
LOG_LEVEL=INFO
' > .env
```

### Run using Docker for creating SASEUL network.
```bash
# Build image
$ ./dev.sh build

# Install PHP packages and set autoload.
$ ./dev.sh install

# Create genesis block
$ ./dev.sh genesis

# Check the genesis block
$ docker-compose run --rm api bash -c 'cd script; ./saseul_script GetTransaction'

# Start SASEUL Origin node
$ ./dev.sh up
```


### usage dev.sh
```bash
./dev.sh   setenv                 
./dev.sh   setenv_other [genesis_host_name] [node_id]       
./dev.sh   build                                            
./dev.sh   install                                          
./dev.sh   update                                           
./dev.sh   up                                               
./dev.sh   buildup                                          
./dev.sh   down                                             
./dev.sh   logs [api|node|web|memcached|mongo]              
./dev.sh   test [*|api|common|saseuld|script]               
./dev.sh   fix [*|api|common|saseuld|script]                
./dev.sh   phan [*|api|saseuld]                             
./dev.sh   cleanup                                          Clean up Node Data
./dev.sh   genesis                                          Genesis Node
```
