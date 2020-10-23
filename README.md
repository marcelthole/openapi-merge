# OpenAPI Merge
Read multiple OpenAPI 3.0.x YAML and JSON files and merge them into one large file.  
This application is build on [cebe/php-openapi](https://github.com/cebe/php-openapi) 

# Installation
```
composer require marcelthole/openapi-merge
```

# Usage
## CLI
```
$ vendor/bin/openapi-merge --help

Usage:
    openapi-merge basefile.yml additionalFileA.yml additionalFileB.yml [...]  > combined.yml

```

## Outputformat
The output format is determined by the basefile extension.