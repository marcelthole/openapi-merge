# OpenAPI Merge


[![Test Status](https://github.com/marcelthole/openapi-merge/workflows/Tests/badge.svg)](https://github.com/marcelthole/openapi-merge/actions)
[![Docker Build Status](https://github.com/marcelthole/openapi-merge/workflows/Docker-Build/badge.svg)](https://github.com/marcelthole/openapi-merge/actions)
[![codecov](https://codecov.io/gh/marcelthole/openapi-merge/branch/main/graph/badge.svg?token=dffVbhqxvg)](https://codecov.io/gh/marcelthole/openapi-merge)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fmarcelthole%2Fopenapi-merge%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/marcelthole/openapi-merge/main)
[![Latest Stable Version](https://poser.pugx.org/marcelthole/openapi-merge/v)](//packagist.org/packages/marcelthole/openapi-merge)
[![License](https://poser.pugx.org/marcelthole/openapi-merge/license)](//packagist.org/packages/marcelthole/openapi-merge)


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

### Arguments
| Argument | Meaning |
| --- | ---  |
| --match[=MATCH] | Use a RegEx pattern to determine the additionalFiles. If this option is set the additionalFiles could be omitted (multiple values allowed) |
| --resolve-references[=RESOLVE-REFERENCES] | Resolve the "$refs" in the given files [default: true] |
| -o, --outputfile[=OUTPUTFILE] | Defines the output file for the result. Defaults the result will printed to stdout |


## Docker
Run the `openapi-merge` command within a docker container
```
docker pull ghcr.io/marcelthole/openapi-merge
docker run -v $PWD:/app --rm ghcr.io/marcelthole/openapi-merge [arguments]
```

Build the image locally from the sourcecode:
```
docker build --build-arg COMPOSER_REQUIRE_VERSION=<version> --no-cache -t marcelthole/openapi-merge:dev docker
docker run -v $PWD:/app --rm marcelthole/openapi-merge:dev [arguments]
```

## Outputformat
The output format is determined by the basefile extension.
