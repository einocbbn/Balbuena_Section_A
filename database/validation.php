<?php

function validateRegistration($fullName, $username, $email, $contactNumber, $password, $confirmPassword)
{
    $errors = [];

    if ($fullName === '') {
        $errors[] = 'Full name is required.';
    } elseif (!preg_match("/^[A-Za-zÀ-ÿ\s.'-]+$/u", $fullName)) {
        $errors[] = 'Full name must contain letters only.';
    } elseif (strlen($fullName) < 4) {
        $errors[] = 'Full name must be at least 4 characters.';
    } elseif (strlen($fullName) > 100) {
        $errors[] = 'Full name must not exceed 100 characters.';
    }

    if ($username === '') {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
        $errors[] = 'Username can contain letters, numbers, and underscores only.';
    } elseif (strlen($username) < 4) {
        $errors[] = 'Username must be at least 4 characters.';
    } elseif (strlen($username) > 50) {
        $errors[] = 'Username must not exceed 50 characters.';
    }

    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 150) {
        $errors[] = 'Email address must not exceed 150 characters.';
    }

    if ($contactNumber === '') {
        $errors[] = 'Contact number is required.';
    } elseif (!preg_match('/^[0-9]+$/', $contactNumber)) {
        $errors[] = 'Contact number must contain numbers only.';
    } elseif (strlen($contactNumber) !== 11) {
        $errors[] = 'Contact number must be exactly 11 digits.';
    } elseif (substr($contactNumber, 0, 2) !== '09') {
        $errors[] = 'Contact number must start with 09.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($confirmPassword === '') {
        $errors[] = 'Please confirm your password.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    return $errors;
}

function validateAdminRegistration($fullName, $username, $email, $password, $confirmPassword)
{
    $errors = [];

    if ($fullName === '') {
        $errors[] = 'Full name is required.';
    } elseif (!preg_match("/^[A-Za-zÀ-ÿ\s.'-]+$/u", $fullName)) {
        $errors[] = 'Full name must contain letters only.';
    } elseif (strlen($fullName) < 4) {
        $errors[] = 'Full name must be at least 4 characters.';
    } elseif (strlen($fullName) > 100) {
        $errors[] = 'Full name must not exceed 100 characters.';
    }

    if ($username === '') {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
        $errors[] = 'Username can contain letters, numbers, and underscores only.';
    } elseif (strlen($username) < 4) {
        $errors[] = 'Username must be at least 4 characters.';
    } elseif (strlen($username) > 50) {
        $errors[] = 'Username must not exceed 50 characters.';
    }

    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 150) {
        $errors[] = 'Email address must not exceed 150 characters.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($confirmPassword === '') {
        $errors[] = 'Please confirm your password.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    return $errors;
}


function validateLogin($username, $password)
{
    $errors = [];

    if ($username === '') {
        $errors[] = 'Username is required.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    return $errors;
}

function validateContactNumber($contactNumber)
{
    $errors = [];

    if ($contactNumber === '') {
        $errors[] = 'Contact number is required.';
    } elseif (!preg_match('/^[0-9]+$/', $contactNumber)) {
        $errors[] = 'Contact number must contain numbers only.';
    } elseif (strlen($contactNumber) !== 11) {
        $errors[] = 'Contact number must be exactly 11 digits.';
    } elseif (substr($contactNumber, 0, 2) !== '09') {
        $errors[] = 'Contact number must start with 09.';
    }

    return $errors;
}

function validateCheckout($customerName, $contactNumber, $email, $address, $notes)
{
    $errors = [];

    if ($customerName === '') {
        $errors[] = 'Customer name is required.';
    } elseif (!preg_match("/^[A-Za-zÀ-ÿ\s.'-]+$/u", $customerName)) {
        $errors[] = 'Customer name must contain letters only.';
    } elseif (strlen($customerName) > 100) {
        $errors[] = 'Customer name must not exceed 100 characters.';
    }

    $errors = array_merge(
        $errors,
        validateContactNumber($contactNumber)
    );

    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($address === '') {
        $errors[] = 'Address is required.';
    } elseif (strlen($address) > 500) {
        $errors[] = 'Address must not exceed 500 characters.';
    }

    if (strlen($notes) > 500) {
        $errors[] = 'Notes must not exceed 500 characters.';
    }

    return $errors;
}

function validateContact($name, $email, $phone, $subject, $message)
{
    $errors = [];

    if ($name === '') {
        $errors[] = 'Full name is required.';
    } elseif (!preg_match("/^[A-Za-zÀ-ÿ\s.'-]+$/u", $name)) {
        $errors[] = 'Full name must contain letters only.';
    }

    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($phone !== '') {
        $errors = array_merge(
            $errors,
            validateContactNumber($phone)
        );
    }

    if ($subject === '') {
        $errors[] = 'Subject is required.';
    }

    if ($message === '') {
        $errors[] = 'Message is required.';
    }

    return $errors;
}