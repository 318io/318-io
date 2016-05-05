
## 內容
本專案為318公民運動數位典藏系統之源碼，程式碼為專為318公民運動數位典藏系統撰寫，但經由適當修正後，應可應用於其他相似典藏系統。
本專案包含三個部份：
* 典藏系統
* 公眾系統
* 特展系統

其中**典藏系統**與**公眾系統**基於DRUPAL 7開發（於col目錄中）；**特展系統**則採用DRUPAL 8（於expo目錄中）。
又，典藏系統與公眾系統彼此相依，特展系統則依存於公眾系統（透過公眾系統API取的資料）。

## 準備
* 本安裝流程，均以/data/318-io為根目錄（以下以[dataroot]表示），安裝前，請先將col和expo置於[dataroot]下。
* 本專案需安裝於三個網址，以下安裝流程，均以下述三網址為例
  * 典藏系統 archive.318.test
  * 公眾系統 public.318.test
  * 特展系統 expo.318.test
  如果你安裝在不同網址，需修改 [dataroot]/col/www/sites/sites.php 與 [dataroot]/expo/www/sites/sites.php。修改方式，請自行參閱 DRUPAL 安裝手冊。

## 典藏與公眾系統
典藏與公眾系統共用檔案系統與資料庫，預設為安裝於同一伺服器。分開安裝會導致系統無法運作。

### 安裝
#### 安裝LAMP
本系統預設安裝之作業系統環境為 Linux，並於 Apache2, PHP5(>=5.2.5), MySql 5(>=5.0.15)上運行。

本系統開發環境為ubuntu 14.04, Apache 2.4.7, PHP 5.5.9, mysql 5.5.46。

請參考 DRUPAL 安裝手冊安裝必要元件。
```
# apt-get install apache2 mysql-server php5 php5-mysql php5-gd
```

#### 安裝LAMP額外模組與設定
* Apache2
  * 啟用 "rewrite" 模組。
   ```
   # a2enmod rewrite
   ```
  * 設定Apache虛擬主機設定檔，請參考conf/apache-318.conf

* Mysql，建立三個資料庫（database）
   * db_archive
   * db_public
   * db_claim

* PHP
  * 修改php.ini如下
  ```
   post_max_size = 40M
   upload_max_filesize = 40M
  ```

#### 安裝全文搜尋引擎 SphinxSearch
  * 將conf/sphinx.conf置於/etc/sphinxsearch
  * 修改sphinx.conf中資料庫帳號密碼
  * 設定indexer的suid權限 ` chmod u+s /usr/bin/indexer`

#### 安裝 drush
   ```
   # apt-get install drush
   ```

#### 安裝 Unzip
   ```
   # apt-get install unzip
   ```

#### 安裝 avconv
   ```
   # apt-get install libav-tools
   ```

#### 安裝 ImageMagick
   ```
   # apt-get install imagemagick
   ```

#### 安裝所需字型，如 fonts-arphic-ukai
   ```
   # apt-get install fonts-arphic-ukai
   # apt-get install xfonts-75dpi
   ```

#### 安裝 wkhtmltopdf
   ```
   For 64 bit Ubuntu
   # wget http://download.gna.org/wkhtmltopdf/0.12/0.12.2.1/wkhtmltox-0.12.2.1_linux-trusty-amd64.deb
   # dpkg -i wkhtmltox-0.12.2.1_linux-trusty-amd64.deb

   For 32 bit Ubuntu
   # wget http://download.gna.org/wkhtmltopdf/0.12/0.12.2.1/wkhtmltox-0.12.2.1_linux-trusty-i386.deb
   # dpkg -i wkhtmltox-0.12.2.1_linux-trusty-i386.deb
   ```

### 設定

#### 匯入預設資料庫
預設資料庫位於[dataroot]/col/db/下，匯入 db_archive.sql, db_claim.sql, db_public.sql

#### 修改DRUPAL設定檔
修改DRUPAL設定檔，填入正確的資料庫帳號密碼
```
  * [dataroot]/col/www/sites/archive/settings.php
  * [dataroot]/col/www/sites/public/settings.php
```

#### 目錄權限
確認Apache擁有讀寫files目錄的權限
  ```
  # chmod -R a+rw [dataroot]/col/www/sites/archive/files
  # chmod -R a+rw [dataroot]/col/www/sites/public/files
  ```

#### 清除快取
使用前建議先清除快取。建議使用drush:
   ```
   # cd [dataroot]/col/sites/archive; drush cc all;
   # cd [dataroot]/col/sites/public; drush cc all;
   ```

#### 系統密碼
登入系統請至http://[archive or public system url]/user/login，預設的管理者帳號密碼為 root : 12345qaz，在典藏系統中，另有一較安全的管理帳號為 318admin : 123456

### 修改路徑
如果你將系統安裝在不同網址或目錄，請至http://[archive url]/admin/config/coll/settings修改設定（只需修改典藏系統），修改前你必須要先登入系統

#### 權限控管
你可以加上適當的權限控管，如apach httpasswd以保護典藏系統不對外公開。

#### 寄件伺服器

若你的主機無法安裝寄件伺服器，你可以使用 gmail 來寄信。請到 admin/config/system/smtp 設定。
```
   在 SMTP server settings 的部份，使用下面的值

   SMTP Server: smtp.gmail.com
   SMTP port  : 587
   Use encrypted protocol: Use TLS

   在 SMTP Authentication 的部份，請填入你的 Gmail 帳號，需注意的是使用 Gmail 來寄信，需要降低安全層級，請登入 Gmail 後到下面網址設定。

   https://www.google.com/settings/security/lesssecureapps?pli=1
```

## 特展系統

### 安裝
特展系統會自公眾系統API讀取資料，請先安裝公眾系統。如安裝至獨立伺服器，請按照安裝典藏與公眾系統**安裝LAMP**、**安裝LAMP額外模組與設定**、
**安裝 ImageMagick**步驟安裝好環境
#### 安裝drush 8
由於特展系統使用drupal 8 開發，必須要安裝新版的drush。請參考 http://x-team.com/2015/02/install-drush-8-drupal-8-without-throwing-away-drush-6-7/ 安裝。

### 設定

#### 匯入預設資料庫
預設資料庫位於[dataroot]/expo/db/下，匯入 db_expo.sql

#### 修改DRUPAL設定檔
修改DRUPAL設定檔，填入正確的資料庫帳號密碼
```
  * [dataroot]/expo/www/sites/expo/settings.php
```

#### 目錄權限
確認Apache擁有讀寫files目錄的權限
  ```
  # chmod -R a+rw [dataroot]/expo/www/sites/expo/files
  ```

#### 清除快取
使用前**務必**先清除快取。建議使用drush:
   ```
   # cd [dataroot]/expo/sites/expo; drush8 cache-rebuild;
   ```

#### 系統密碼
登入系統請至http://[expo system url]/user/login，預設的管理者帳號密碼為 root : 12345qaz

### 修改路徑
如果你將公眾系統安裝在不同網址，請至http://[expo system url]/admin/config/expo/settings修改設定，修改前你必須要先登入系統
