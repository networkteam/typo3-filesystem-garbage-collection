# TYPO3 Filesystem garbage collection

This provides a command for filesystem garbage collection.

## Install

```shell
composer req networkteam/typo3-filesystem-garbage-collection
```

## Usage

Delete files in `fileadmin/_temp_/` when older than `30` days. Remove empty subfolders with `-e`.

```shell
composer exec typo3 -- cleanup:folder -e 1:/_temp_/ 30
```

## Notes

When a recycler folder (a folder named `_recycler_`) exists in any parent folder, then the file will be moved to the recycler folder and **not** really deleted.
