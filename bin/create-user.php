<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';


$container = App\Bootstrap::boot()
	->createContainer();

if (!isset($_SERVER['argv'][3])) {
	echo '
Add new user to database.

Usage: create-user.php <role> <name> <username> <surname> <email> <address> <password>
';
	exit(1);
}

[,$role, $name, $username, $surname, $email, $address, $password] = $_SERVER['argv'];

$manager = $container->getByType(App\Model\UserManager::class);

try {
	$manager->add($role, $name, $username, $surname, $email, $address, $password, NULL);
	echo "User $name was added.\n";

} catch (App\Model\DuplicateNameException $e) {
	echo "Error: duplicate name.\n";
	exit(1);
}
