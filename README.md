# Asset scanner for Silverstripe

Passes the stream representing an asset to a backend that can perform scanning. If the scan fails, information is logged for further review and the request to store the asset fails.

This works with assets being stored via:
+ Uploads
+ File::setFrom* operations

The module ships with a ClamAV backend where clamd can be contacted via a unix socket or TCP socket. The system allows for custom scanning backends to be created and configured.

## Requirements

To use clamd, you need to have that installed, configured and working.

## Installation

```sh
composer require nswdpc/silverstripe-asset-scan
```

To use the replacement asset store, add configuration to your project.

The name of the configuration block is up to you.

```yaml
---
Name: 'app-scanning-assetstore'
After:
  - '#assetscore'
---
SilverStripe\Core\Injector\Injector:
  SilverStripe\Assets\Storage\AssetStore:
    class: 'NSWDPC\AssetScan\ScanningFlysystemAssetStore'
```

After configuration flush, the project will be configured to use the asset store provided by this module. It will inherit the properties defined in `#assetscore` configuration.

To use the ClamAVBackend, you must set the socket address value:

```yml
---
Name: 'app-asset-scan-handling'
After:
  - '#nswdpc-asset-scan-handling'
---
NSWDPC\AssetScan\ClamAVBackend:
  # specify a unix socket location
  address: 'unix:///some/path/to/clamd.sock'
```

## Further configuration

You can configure the maximum chunk size, for use with backends that do chunked scans.

```yml
---
Name: 'app-asset-scan-handling'
After:
  - '#nswdpc-asset-scan-handling'
---
NSWDPC\AssetScan\Backend:
  # increase chunk size to 20M, value is in bytes
  max_chunk_size: 20971520
```

### Roll your own backend

You can use your own scanning backend via Injector. It must be a subclass of `\NSWDPC\AssetScan\Backend`:

```yml
---
Name: 'app-asset-scan-handling'
After:
  - '#nswdpc-asset-scan-handling'
---
SilverStripe\Core\Injector\Injector:
  NSWDPC\AssetScan\Backend:
    # set default backend
    class: 'My\App\CustomScanBackend'
```

## License

[BSD-3-Clause](./LICENSE.md)


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
