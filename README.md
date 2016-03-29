# Kollus CORS Upload

## Requirement
 * jQuery
 * bootstrap : for site's theme

## How to use
Attach below code to page's footer.
```html
<!--[if lt IE 10]>
<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<![endif]-->
<!--[if (gte IE 10)|!(IE)]><!-->
<script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
<!--<![endif]-->
<script src="assets/js/cors-upload.js"></script>
```

# How to develop or test

## Requirement
 * php 5.3 above
   * composer
 * npm (of node 4.2 above)

### 1. Install composer

```bash
curl -sS https://getcomposer.org/installer | php
```

### 2. Bulid

```bash
php composer.phar install
npm install
`npm bin`/.bin/gulp
```

### 3. Create config.yml and change your information

```bash
cp config-sample.yml config.yml
vi config.yml
```
