	
<?php		

try {
	$con = new \PDO(
		getenv('DB_ADAPTER').':dbname='.getenv('DB_NAME').';host='.getenv('DB_HOST'),
		getenv('DB_USER'),
		getenv('DB_PASSWD')
	);
} catch(\Exception $e) {
	echo $e->getMessage()."\n";
	return false;
}

