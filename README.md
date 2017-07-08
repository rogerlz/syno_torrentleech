# syno_torrentleech
Torrentleech.org BT Search for Synology Download Station

This is a little tricky because TorrentLeech.org requires authentication to download .torrent files. The dlm need a intermediary php script to get the .torrent file.

## Installation

### Make the DLM package
Clone the git, and do a tar gz archive with the INFO and search.php files:
```bash
git clone git@github.com:rogerlz/syno_torrentleech.git
cd syno_torrentleech
tar zcf syno_torrentleech.dlm INFO search.php
```

### Install the LDM package
1. Go to Download Station > Settings > BT search
2. Add the DLM package
3. Edit the new TorrentLeech line
4. Add your torrentleech.org login and password.

### Copy the tl_download.php file into the webserver folder
1. Active your NAS Web Station. Enable the PHP curl extension.
2. Copy the file tl_download.php to the root of your web folder.

