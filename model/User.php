<?php

class User{

    private $id;
    private $username;
    private $hashPass;
    private $email;

    public function __construct()
    {
        $this->id = -1;
        $this->username = "";
        $this->hashPass = "";
        $this->email = "";
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getHashPass(): string
    {
        return $this->hashPass;
    }

    /**
     * @param string $hashPass
     */
    public function setHashPass(string $hashPass)
    {
        $newHashedPassword = password_hash($hashPass, PASSWORD_BCRYPT);
        $this->hashPass = $newHashedPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
        return $this;
    }

    public function setDirectPass($pass)
    {
        $this->hashPass = $pass;
        return $this;
    }

    public function saveToDB(PDO $conn)
    {

        if ($this->id === -1) {
            $stmt = $conn->prepare('INSERT INTO `user`(`email`,`pass`) VALUES (:email,:pass)');
            $result = $stmt->execute([
                'email' => $this->getEmail(),
                'pass' => $this->getHashPass()

            ]);
            if ($result !== false) {
                $this->id = $conn->lastInsertId();
                return true;
            }

        } else {

            $stmt = $conn->prepare('UPDATE `user` SET email=:email WHERE id=:id');
            $result = $stmt->execute([
                'id' => $this->getId(),
                'email' => $this->getEmail(),

            ]);
            if ($result !== false) {
                return true;
            }
        }
        return false;
    }

    static public function loadById(PDO $conn, $id){
        $stmt = $conn->prepare('SELECT * FROM `user` WHERE id=:id');
        $result = $stmt->execute([
            'id' => $id
        ]);
        if($result === true && $stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $user = new User();
            $user->id = $row['id'];
            $user->setEmail($row['email']);
            $user->setDirectPass($row['pass']);
            return $user;
        }
        return NULL;
    }

    static public function loadAllUsers(PDO $conn){
        $result = $conn->query('SELECT * FROM `user`');
        $tab = [];
        if($result !== false && $result->rowCount() > 0){
            foreach ($result->fetchAll() as $key){
                $user = new User();
                $user->id = $key['id'];
                $user->setEmail($key['email']);
                $user->setDirectPass($key['pass']);
                $tab[] = $user;
            }
            return $tab;
        }
        return $tab;
    }

    static public function loadByEmail(PDO $conn, $email){
        $stmt = $conn->prepare('SELECT * FROM `user` WHERE email=:email');
        $result = $stmt->execute(['email' => $email]);
        if($result !== false && $stmt->rowCount() > 0){
                $row = $stmt->fetch();
                $user = new User();
                $user->id = $row['id'];
                $user->setEmail($row['email']);
                $user->setDirectPass($row['pass']);
                return $user;
            }
            return NULL;
    }
    public function toArray(){

        return array(
            'id' => $this->getId(),
            'email' => $this->getEmail()
        );
    }
    public function deleteUser(PDO $conn){

        if($this->id > -1) {
            $stmt = $conn->prepare('DELETE FROM `user` WHERE id=:id');
            $result = $stmt->execute(['id' => $this->getId()]);
            if ($result === true) {
                $this->id = -1;
                return true;
            }
            return false;
        }
        return false;
    }
}