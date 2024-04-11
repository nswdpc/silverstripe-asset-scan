# Asset scanner for Silverstripe

Passes the stream representing an asset to a backend that can perform scanning. If the scan fails, information is logged for further review and the request to store the asset fails.

This works with assets being stored via:
+ Uploads
+ File::setFrom* operations

Currently, only ClamAV is supported via a unix socket or TCP socket, but the system allows for custom scanning backends to be created and configured.

## Requirements

To use clamd, you need to have that installed, configured and working.

## Installation

```sh
composer require nswdpc/silverstripe-asset-scan
```

## License

[BSD-3-Clause](./LICENSE.md)

## Documentation

> TBC

## Configuration

You can configure the socket address and chunk size (for chunked scans):

```yml
---
Name: 'app-asset-scan-handling'
After:
  - '#nswdpc-asset-scan-handling'
---
NSWDPC\AssetScan\Backend:
  # increase chunk size
  max_chunk_size: 2097152
NSWDPC\AssetScan\ClamAVBackend:
  # specify a unix socket location
  address: 'unix:///some/path/to/clamd.sock'
```


### Roll your own backend

You can create your own scanning backend. It must extend `Backend`:

```yml
---
Name: 'app-asset-scan-handling'
After:
  - '#nswdpc-asset-scan-handling'
---
SilverStripe\Core\Injector\Injector:
  NSWDPC\AssetScan\Backend:
    # set default backend
    class: 'My\App\CustomScanner'
```

## Maintainers

+ [dpcdigital@NSWDPC:~$](https://dpc.nsw.gov.au)

## Bugtracker

We welcome bug reports, pull requests and feature requests on the Github Issue tracker for this project.

Please review the [code of conduct](./code-of-conduct.md) prior to opening a new issue.

## Security

If you have found a security issue with this module, please email digital[@]dpc.nsw.gov.au in the first instance, detailing your findings.

## Development and contribution

If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module maintainers.

Please review the [code of conduct](./code-of-conduct.md) prior to completing a pull request.
