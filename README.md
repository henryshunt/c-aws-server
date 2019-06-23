# C-AWS Server
A website for viewing the data from a [C-AWS](https://github.com/henryshunt/c-aws) station. It can run on the station itself (locally), as well as on a remote server (remotely) where it also acts as an endpoint for receiving data uploaded from the station.

# Breakdown
The website pages themselves provide the following: the latest data report, statistics for the current day, camera images and astronomical info, graphs of daily data, graphs of statistic data over the past year, climate information (yearly statistics and statistics per month) for each year and graphs of that data, and station CPU and enclosure temperature over the past six hours as well as station location info. When operating locally, the station page also shows information that can only be accessed by querying the installed C-AWS software (such as drive space and power controls).

The `data` directory contains scripts to access the data that is displayed on the pages. When running locally the station's SQLite3 database is accessed, and when running remotely the web server's MySQL database is accessed. Camera images are accessed via a `camera` directory inside the `data` directory. The rest of the repository comprises supporting routines for both the front and back end, as well as the data upload script (`routines/upload.php`).

# Usage
- Setup a web server and install PHP
- Install the PHP PDO drivers for MySQL (if running remotely) or SQLite3 (if running locally)
- If running locally:
    - Give the web server permission to write to the C-AWS `DataDirectory`
    - If C-AWS is logging from a camera then create a symbolic link at `data/camera` referencing the C-AWS `CameraDirectory`
- If running remotely:
    - Install MySQL, create a database, then add the tables with the following SQL:
        - `CREATE TABLE reports (Time datetime NOT NULL, AirT decimal(3,1) DEFAULT NULL, ExpT decimal(3,1) DEFAULT NULL, RelH decimal(4,1) DEFAULT NULL, DewP decimal(3,1) DEFAULT NULL, WSpd decimal(4,1) DEFAULT NULL, WDir int(3) DEFAULT NULL, WGst decimal(4,1) DEFAULT NULL, SunD int(2) DEFAULT NULL, Rain decimal(5,3) DEFAULT NULL, StaP decimal(5,1) DEFAULT NULL, MSLP decimal(5,1) DEFAULT NULL, ST10 decimal(3,1) DEFAULT NULL, ST30 decimal(3,1) DEFAULT NULL, ST00 decimal(3,1) DEFAULT NULL, PRIMARY KEY (Time))`
        - `CREATE TABLE envReports (Time datetime NOT NULL, EncT decimal(3,1) DEFAULT NULL, CPUT decimal(3,1) DEFAULT NULL, PRIMARY KEY (Time))`
        - `CREATE TABLE dayStats (Date date NOT NULL, AirT_Min decimal(3,1) DEFAULT NULL, AirT_Max decimal(3,1) DEFAULT NULL, AirT_Avg decimal(5,3) DEFAULT NULL, RelH_Min decimal(4,1) DEFAULT NULL, RelH_Max decimal(4,1) DEFAULT NULL, RelH_Avg decimal(6,3) DEFAULT NULL, DewP_Min decimal(3,1) DEFAULT NULL, DewP_Max decimal(3,1) DEFAULT NULL, DewP_Avg decimal(5,3) DEFAULT NULL, WSpd_Min decimal(4,1) DEFAULT NULL, WSpd_Max decimal(4,1) DEFAULT NULL, WSpd_Avg decimal(6,3) DEFAULT NULL, WDir_Min int(3) DEFAULT NULL, WDir_Max int(3) DEFAULT NULL, WDir_Avg decimal(6,3) DEFAULT NULL, WGst_Min decimal(4,1) DEFAULT NULL, WGst_Max decimal(4,1) DEFAULT NULL, WGst_Avg decimal(6,3) DEFAULT NULL, SunD_Ttl int(5) DEFAULT NULL, Rain_Ttl decimal(6,3) DEFAULT NULL, MSLP_Min decimal(5,1) DEFAULT NULL, MSLP_Max decimal(5,1) DEFAULT NULL, MSLP_Avg decimal(7,3) DEFAULT NULL, ST10_Min decimal(3,1) DEFAULT NULL, ST10_Max decimal(3,1) DEFAULT NULL, ST10_Avg decimal(5,3) DEFAULT NULL, ST30_Min decimal(3,1) DEFAULT NULL, ST30_Max decimal(3,1) DEFAULT NULL, ST30_Avg decimal(5,3) DEFAULT NULL, ST00_Min decimal(3,1) DEFAULT NULL, ST00_Max decimal(3,1) DEFAULT NULL, ST00_Avg decimal(5,3) DEFAULT NULL, PRIMARY KEY (Date))`
    - If C-AWS is to upload camera images then ensure an FTP account exists with the default directory set to `data/camera`
- Block access to the `config.ini` file to prevent passwords from being accessed

# Configuration
Use config.ini to supply station information, data credentials, and parameters concerned with running locally vs remotely. Various option values can be omitted if they have nothing that depends on them (e.g. you don't need to provide a `LocalDataDir` value if `IsRemote` is turned on).

|Option|Description|
|--|--|
|`Name`|Required. The name of the station to be display in the page header and title|
|`TimeZone`|Required. Local time zone of the station. Must be a valid name in the IANA time zone database (e.g. `Europe/London`)|
|`Latitude`|Required. Latitude of the station in decimal degrees|
|`Longitude`|Required. Longitude of the station in decimal degrees|
|`Elevation`|Required. Elevation of the station above sea level in metres|
|`IsRemote`|Required. Is the server operating remotely (not on the same device as the C-AWS software)?|
|`LocalDataDir`|If operating locally, the absolute path to the directory where logged data is stored (e.g. `/home/pi/c-aws-data`)|
|`LocalSoftwareDir`|If operating locally, the absolute path to the directory where the C-AWS software is stored (e.g. `/home/pi/c-aws`|
|`RemoteUploadPass`|If operating remotely, the password to check for on requests to upload data|
|`RemoteHost`|If operating remotely, the server hosting the MySQL server to use for storing uploaded data (e.g. `localhost`)|
|`RemoteDatabase`|If operating remotely, the name of the MySQL database to store uploaded data in|
|`RemoteUsername`|If operating remotely, the username of a user with access the MySQL database to upload data|
|`RemotePassword`|If operating remotely, the password of the above MySQL user|

# Dependencies
- MySQL
- PHP MySQL PDO Driver
- PHP SQLite3 PDO Driver
- JQuery
- [Chartist](https://github.com/gionkunz/chartist-js)
- [Flatpickr](https://github.com/flatpickr/flatpickr) (modified)
- [Moment](https://github.com/moment/moment)
- [Moment Timezone](https://github.com/moment/moment-timezone) with data