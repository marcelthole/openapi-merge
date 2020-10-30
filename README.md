# OpenAPI Merge

[![Build Status](https://api.travis-ci.com/marcelthole/openapi-merge.svg?branch=main)](https://travis-ci.com/github/marcelthole/openapi-merge)
[![Coverage Status](https://coveralls.io/repos/github/marcelthole/openapi-merge/badge.svg?branch=main)](https://coveralls.io/github/marcelthole/openapi-merge?branch=main)
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

## Outputformat
The output format is determined by the basefile extension.