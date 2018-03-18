<?php

class UserEntity
{
    protected $id;
    protected $name;
    protected $surname;
    protected $patronymic;
    protected $email;
    protected $phone;

    public function __construct(array $data) {
        if(isset($data['id'])) {
            $this->id = $data['id'];
        }

        $this->name = $data['name'];
        $this->surname = $data['surname'];
        $this->patronymic = $data['patronymic'];
        $this->email = $data['email'];
        $this->phone = $data['phone'];
    }

    public function getFio() {
        return $this->name . " " . $this->surname . " " . $this->patronymic;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getSurname() {
        return $this->surname;
    }

    public function getPatronymic() {
        return $this->patronymic;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPhone() {
        return $this->phone;
    }
}
