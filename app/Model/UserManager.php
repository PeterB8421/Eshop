<?php

declare(strict_types = 1);

namespace App\Model;

use Nette;
use Nette\Security\Passwords;

/**
 * Users management.
 */
final class UserManager implements Nette\Security\IAuthenticator {

    use Nette\SmartObject;


    const
            TABLE_NAME = 'users',
            COLUMN_ID = 'id',
            COLUMN_NAME = 'name',
            COLUMN_SURNAME = 'surname',
            COLUMN_USERNAME = 'username',
            COLUMN_PASSWORD_HASH = 'password',
            COLUMN_EMAIL = 'email',
            COLUMN_ROLE = 'role',
            COLUMN_PHOTO = 'photo',
            COLUMN_ADDRESS = 'address',
            COLUMN_CREATIONDATE = 'creationDate';

    /** @var Nette\Database\Context */
    private $database;

    /** @var Passwords */
    private $passwords;

    public function __construct(Nette\Database\Context $database, Passwords $passwords) {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    /**
     * Performs an authentication.
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials): Nette\Security\IIdentity {
        [$username, $password] = $credentials;

        $row = $this->database->table(self::TABLE_NAME)
                ->where(self::COLUMN_USERNAME, $username)
                ->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
        } elseif (!$this->passwords->verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
        } elseif ($this->passwords->needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
            $row->update([
                self::COLUMN_PASSWORD_HASH => $this->passwords->hash($password),
            ]);
        }

        $arr = $row->toArray();
        unset($arr[self::COLUMN_PASSWORD_HASH]);
        return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
    }

    /**
     * Adds new user.
     * @throws DuplicateNameException
     */
    public function add(string $role, string $name, string $username, string $surname, string $email, string $address, string $password, $photo): void {
        Nette\Utils\Validators::assert($email, 'email');
        try {
            $this->database->table(self::TABLE_NAME)->insert([
                self::COLUMN_ROLE => $role,
                self::COLUMN_NAME => $name,
                self::COLUMN_USERNAME => $username,
                self::COLUMN_SURNAME => $surname,
                self::COLUMN_EMAIL => $email,
                self::COLUMN_ADDRESS => $address,
                self::COLUMN_PASSWORD_HASH => $this->passwords->hash($password),
                self::COLUMN_PHOTO => $photo
            ]);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new DuplicateNameException;
        }
    }

    public function getAll($order) {
        return $this->database->table(self::TABLE_NAME)->order($order)->fetchAll();
    }

    public function getByID($id) {
        return $this->database->table(self::TABLE_NAME)->where('id', $id)->fetch();
    }

    public function edit($id, $data) {
        $this->database->table(self::TABLE_NAME)->where('id', $id)->update($data);
    }
    
    public function delete($id){
        $this->database->table(self::TABLE_NAME)->where('id',$id)->delete();
    }

}

class DuplicateNameException extends \Exception {
    
}
