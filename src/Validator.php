<?php

namespace App;

class Validator
{
    public function validate(array $user)
    {
        $errors = [];
        if (empty($user['nickname'])) {
            $errors['nickname'] = "Nickname must be not empty";
        } elseif (strlen($user['nickname']) <= 4) {
            $errors['nickname'] = "Nickname must be greater than 4 characters";
        }

//        if (empty($user['email'])) {
//            $errors['email'] = "Email must be not empty";
//        }

        return $errors;
    }
}