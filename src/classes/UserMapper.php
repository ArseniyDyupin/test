<?php

class UserMapper extends Mapper
{
    public function getUsers() {
        $sql = "SELECT * from users";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new UserEntity($row);
        }
        return $results;
    }

    public function getUserById($user_id) {
        $sql = "SELECT * from users where id = :user_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["user_id" => $user_id]);

        $data = $stmt->fetch();

        if($result && is_array($data)) {
            return new UserEntity($data);
        } else {
            return false;
        }
    }

    public function checkEmailValidation($email){
        $res = preg_match('/^((([0-9A-Za-z]{1}[-0-9A-z\.]{1,}[0-9A-Za-z]{1})|([0-9А-Яа-я]{1}[-0-9А-я\.]{1,}[0-9А-Яа-я]{1}))@([-A-Za-z]{1,}\.){1,2}[-A-Za-z]{2,})$/u', $email);
        return $res;
    }

    public function checkPhoneValidation($phone){
        $res = preg_match("/[+][7] [0-9]{3} [0-9]{3}[-][0-9]{2}[-][0-9]{2}$/i", $phone);
        return $res;
    }

    public function checkEmailUnique($email){
        $sql = "SELECT * from users where email = :email";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["email" => $email]);

        $data = $stmt->fetch();

        if($result && is_array($data)) {
            return false;
        } else {
            return true;
        }
    }

    public function checkPhoneUnique($phone){
        $sql = "SELECT * from users where phone = :phone";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["phone" => $phone]);

        $data = $stmt->fetch();

        if($result && is_array($data)) {
            return false;
        } else {
            return true;
        }
    }

    public function deleteUserById($user_id) {
        $sql = "DELETE from users where id = :user_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["user_id" => $user_id]);

        echo '<pre>';
        var_dump($result);

        return $result ? true : false;
    }

    public function save(UserEntity $user) {
        $sql = "insert into users
            (name, surname, patronymic, email, phone) values
            (:name, :surname, :patronymic, :email, :phone)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "name" => $user->getName(),
            "surname" => $user->getSurname(),
            "patronymic" => $user->getPatronymic(),
            "email" => $user->getEmail(),
            "phone" => $user->getPhone(),
        ]);

        return $result ? true : false;
    }

    public function updateUserById(UserEntity $user, $user_id) {
        $sql = "update users set name=:name, surname=:surname, patronymic=:patronymic, email=:email, phone=:phone WHERE id=:user_id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "name" => $user->getName(),
            "surname" => $user->getSurname(),
            "patronymic" => $user->getPatronymic(),
            "email" => $user->getEmail(),
            "phone" => $user->getPhone(),
            "user_id" => $user_id
        ]);

        return $result ? true : false;
    }
}
