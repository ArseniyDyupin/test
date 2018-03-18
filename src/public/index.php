<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['db']['host']   = "localhost";
$config['db']['user']   = "root";
$config['db']['pass']   = "";
$config['db']['dbname'] = "development_db";


$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};



$app->post('/createUser', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $user_mapper = new UserMapper($this->db);
    $user_data = [];
    $errors = [];

    if(!empty($data['name'])){
        $user_data['name'] = filter_var($data['name'], FILTER_SANITIZE_STRING);
    } else {
        $errors['name'] = 'Field "name" must be filled in';
    }

    if(!empty($data['surname'])){
        $user_data['surname'] = filter_var($data['surname'], FILTER_SANITIZE_STRING);
    } else {
        $errors['surname'] = 'Field "surname" must be filled in';
    }

    if(!empty($data['patronymic'])){
        $user_data['patronymic'] = filter_var($data['patronymic'], FILTER_SANITIZE_STRING);
    } else {
        $errors['patronymic'] = 'Field "patronymic" must be filled in';
    }

    if(!empty($data['email'])){
        $emailUnique = $user_mapper->checkEmailUnique($data['email']);
        $emailValid = $user_mapper->checkEmailValidation($data['email']);
        if(!$emailUnique) {
            $errors['email']['unique'] = 'User with this email already in system. Email must be unique.';
        }
        if(!$emailValid) {
            $errors['email']['validation'] = 'Email not valid.';
        }
        if($emailValid && $emailUnique) {
            $user_data['email'] = filter_var($data['email'], FILTER_SANITIZE_STRING);
        }
    } else {
        $errors['email'] = 'Field "email" must be filled in';
    }

    if(!empty($data['phone'])){
        $phoneUnique = $user_mapper->checkPhoneUnique($data['phone']);
        $phoneValid = $user_mapper->checkPhoneValidation($data['phone']);
        if(!$phoneUnique) {
            $errors['phone']['unique'] = 'User with this phone already in system. Phone must be unique.';
        }
        if(!$phoneValid) {
            $errors['phone']['validation'] = 'Phone not valid, mask is "+7 123 123-45-67".';
        }
        if($phoneUnique && $phoneValid) {
            $user_data['phone'] = filter_var($data['phone'], FILTER_SANITIZE_STRING);
        }
    } else {
        $errors['phone'] = 'Field "phone" must be filled in';
    }

    if(count($errors) == 0){
        $user = new UserEntity($user_data);

        $result = $user_mapper->save($user);
    }

    if(isset($result) && $result && count($errors) == 0){
        $response = [
            'result' => true,
            'text' => "User was added"
        ];
    } else {
        $response = [
            'result' => false,
            'errors' => $errors,
            'text' => "User was not added"
        ];
    }

    echo json_encode($response);
});

$app->post('/updateUser', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $user_mapper = new UserMapper($this->db);
    $user_data = [];
    $errors = [];

    $user_id = (int)filter_var($data['id'], FILTER_SANITIZE_STRING);
    if(empty($user_id)){
        $errors['id'] = 'Field "id" must be filled in. You need to chose user for update.';
    }
    $userExist = $user_mapper->getUserById($user_id);
    if(!$userExist){
        $errors['id'] = 'User with id ' . $user_id . ' does not exists.';
    }

    if(isset($data['email'])){
        if($data['email'] != ""){
            $emailUnique = $user_mapper->checkEmailUnique($data['email']);
            $emailValid = $user_mapper->checkEmailValidation($data['email']);
            if(!$emailUnique) {
                $errors['email']['unique'] = 'User with this email already in system. Email must be unique.';
            }
            if(!$emailValid) {
                $errors['email']['validation'] = 'Email not valid.';
            }
            if($emailValid && $emailUnique) {
                $user_data['email'] = filter_var($data['email'], FILTER_SANITIZE_STRING);
            }
        } else {
            $errors['email'] = 'Field "email" must be filled in';
        }
    }

    if (isset($data['name'])) {
        if ($data['name'] != "") {
            $user_data['name'] = filter_var($data['name'], FILTER_SANITIZE_STRING);
        } else {
            $errors['name'] = 'Field "name" must be filled in';
        }
    }

    if (isset($data['surname'])) {
        if ($data['surname'] != "") {
            $user_data['surname'] = filter_var($data['surname'], FILTER_SANITIZE_STRING);
        } else {
            $errors['surname'] = 'Field "surname" must be filled in';
        }
    }

    if (isset($data['patronymic'])) {
        if ($data['patronymic'] != "") {
            $user_data['patronymic'] = filter_var($data['patronymic'], FILTER_SANITIZE_STRING);
        } else {
            $errors['patronymic'] = 'Field "patronymic" must be filled in';
        }
    }

    if (isset($data['phone'])) {
        if ($data['phone'] != "") {
            $phoneUnique = $user_mapper->checkPhoneUnique($data['phone']);
            $phoneValid = $user_mapper->checkPhoneValidation($data['phone']);
            if(!$phoneUnique) {
                $errors['phone']['unique'] = 'User with this phone already in system. Phone must be unique.';
            }
            if(!$phoneValid) {
                $errors['phone']['validation'] = 'Phone not valid, mask is "+7 123 123-45-67".';
            }
            if($phoneUnique && $phoneValid) {
                $user_data['phone'] = filter_var($data['phone'], FILTER_SANITIZE_STRING);
            }
        } else {
            $errors['phone'] = 'Field "phone" must be filled in';
        }
    }

    $user = new UserEntity($user_data);

    if(count($errors) == 0){
        $update = $user_mapper->updateUserById($user, $user_id);
    }

    if (isset($update) && $update) {
        $response = [
            'result' => true,
            'text' => "User was updated"
        ];
    } else {
        $response = [
            'result' => false,
            'text' => "User was not updated",
            'errors' => $errors
        ];
    }

    echo json_encode($response);
});

$app->post('/getUser', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $user_mapper = new UserMapper($this->db);
    $errors = [];

    $user_id = (int)filter_var($data['id'], FILTER_SANITIZE_STRING);
    if(empty($user_id)){
        $errors['id'] = 'Field "id" must be filled in. You need to chose user for update.';
    }
    $userExist = $user_mapper->getUserById($user_id);
    if(!$userExist){
        $errors['id'] = 'User with id ' . $user_id . ' does not exists.';
    }

    if(count($errors) == 0){
        $user = $user_mapper->getUserById($user_id);
    }

    if(isset($user) && $user){
        $response = [
            'user_data' => [
                'name' => $user->getName(),
                'surname' => $user->getSurname(),
                'patronymic' => $user->getPatronymic(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone()
            ],
            'result' => true,
            'text' => "Success"
        ];
    } else {
        $response = [
            'user_data' => [],
            'result' => false,
            'errors' => $errors,
            'text' => "User does not exists"
        ];
    }

    echo json_encode($response);
});

$app->post('/deleteUser', function (Request $request, Response $response) {

    $data = $request->getParsedBody();
    $user_mapper = new UserMapper($this->db);
    $errors = [];

    $user_id = (int)filter_var($data['id'], FILTER_SANITIZE_STRING);
    if(empty($user_id)){
        $errors['id'] = 'Field "id" must be filled in. You need to chose user for update.';
    }
    $userExist = $user_mapper->getUserById($user_id);
    if(!$userExist){
        $errors['id'] = 'User with id ' . $user_id . ' does not exists.';
    }

    if(count($errors) == 0){
        $delete = $user_mapper->deleteUserById($user_id);
    }

    if (isset($delete) && $delete) {
        $response = [
            'result' => true,
            'text' => "User was deleted"
        ];
    } else {
        $response = [
            'result' => false,
            'errors' => $errors,
            'text' => "User was not deleted"
        ];
    }

    echo json_encode($response);
});

$app->run();