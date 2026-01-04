<?php
// Sesuaikan dengan lokasi folder Anda
// Sesuaikan dengan lokasi folder Anda, atau gunakan env var untuk Docker
define('BASEURL', getenv('BASEURL') ?: 'http://localhost/spark');

// Atau jika pakai virtual host: 
// define('BASEURL', 'http://spark.test');