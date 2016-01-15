# ESConfig

A simple tool for managing simple Elasticsearch instances. The tool looks for a file named `.esconfig.json` and uses
the definitions in that to create indexes and their mappings. It is also capable of running a warmup script to populate
these indexes if needed.

## Commands

### `help`
Renders a help view

### `nuke`
Completely erases the cluster, good for starting over. Use with caution, obviously.

### `reset`
Deletes the indexes described in `.esconfig.json` and recreates them and their mappings, but with no data

### `warm`
Calls the warmup command described in `.esconfig.json`.

## Sample `.esconfig.json` file
```json
{
    "hosts": ["localhost:9200"],
    "warm": "php ./warm_es.php",
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
