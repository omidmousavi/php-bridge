# PHP-bridge 

A php code to act as a bridge between client and a route

Note that this repo and bridged route must be on same host

How to use:

```
<?php

include('./bridge.php');

$base_url = "http://localhost/laravel/public/";

echo bridge($base_url);
```
