# ESConfig

A simple tool for managing simple Elasticsearch instances. The tool looks for a file named `.esconfig.json` and uses
the definitions in that to create indexes and their mappings. It is also capable of running a warmup script to populate
these indexes if needed.


## Installation

Install manually by cloning and ensuring the `dist/esconfig.phar` binary is in your `$PATH`; or use [Homebrew](http://brew.sh):

    brew tap hellopablo/utilities
    brew install hellopablo/utilities/esconfig


## Commands

### `help`
Renders a help view

### `nuke`
Completely erases the cluster, good for starting over. Use with caution, obviously.

### `reset`
Deletes the indexes described in `.esconfig.json` and recreates them and their mappings, but with no data

### `warm`
Calls the warmup command described in `.esconfig.json`.


## Environment

You can define which environment is being used using any of the following methods (in order of specifity):

- As the second parameter, e.g. `esconfig warm production`
- Using a file located at the project root called `esconfig.environment` which contains the environment to use
- Using the `default_environment` property of `.esconfig.json`
- The default, which is `DEVELOPMENT`


## Sample `.esconfig.json` file

```json
{
    "host": "localhost:9200",
    "warm": "php ./warm_es.php",
    "host": {
        "DEVELOPMENT": "192.168.99.100:32769",
        "PRODUCTION": "localhost:9200",
        "STAGING": "localhost:9200"
    },
    "warm": {
        "DEVELOPMENT": "php ./warm_es.php {{__HOST__}}",
        "PRODUCTION": "php ./warm_es.php {{__HOST__}}",
        "STAGING": "php ./warm_es.php {{__HOST__}}"
    }
    "default_environment": "DEVELOPMENT",
    "indexes": [
        {
            "name": "my-index",
            "mappings": {
                "my-item": {
                    "properties": {
                        "location": {
                            "type": "geo_point"
                        }
                    }
                }
            }
        },
        {
            "name": "another-index",
            "mappings": {
                "another-item": {
                    "properties": {
                        "location": {
                            "type": "geo_point"
                        }
                    }
                }
            }
        }
    ]
}

```


## RoadMap
- [ ] Use symfony console component
- [ ] Use Guzzle or similar
- [ ] Better handling of responses from ES
