<?php

require_once "../includes/connectBd.php";

$con = connectdb();

function checkSurname($input) {
    $regex = '/[A-Za-z]{3,25}/';
    return preg_match($regex, $input);
}
function checkFirstname($input) {
    $regex = '/^[A-Za-z]{3,25}$/';
    return preg_match($regex, $input);
}
function checkEmail($input) {
    $regex = '/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
    return preg_match($regex, $input);
}
function checkPassword($input) {
    $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\#\+\-\^\[\]])(?=.{8,})/';
    return preg_match($regex, $input);
}
function checkConfirmPassword($input, $original) {
    if ($input = $original) {
        return true;
    }
}

if (isset($_POST['surname'], $_POST['firstname'], $_POST['email'], $_POST['password'], $_POST['confirm-password'])) {

    $nom= $_POST['surname'];
    $prenom= $_POST['firstname'];
    $mail= $_POST['email'];
    $mdp= $_POST['password'];
    $mdpVerif= $_POST['confirm-password'];

    $isSurnameValid = checkSurname($nom);
    $isFirstnameValid = checkFirstname($prenom);
    $isEmailValid = checkEmail($mail);
    $isPasswordValid = checkPassword($mdp);
    $isConfirmPasswordValid = checkConfirmPassword($mdpVerif, $mdp);

    $isFormValid = $isSurnameValid &&
                    $isFirstnameValid &&
                    $isEmailValid &&
                    $isPasswordValid &&
                    $isConfirmPasswordValid;

    // $rec= $con->prepare('INSERT INTO utilisateur (nomUser, prenomUser, mailUser, mdpUser) VALUES (?, ?, ?, ?)');
    // $rec->execute(array($nom, $prenom, $mail, $mdp));
    exit('Email is not valid!');
    // header('location: ../views/register.html');
}

?>

// Now we check if the data was submitted, isset() function will check if the data exists.
if (!isset($_POST['surname'], $_POST['firstname'], $_POST['email'], $_POST['password'], $_POST['confirm-password'])) {
	// Could not get the data that should have been sent.
	exit('Veuillez completer le formulaire d\'inscription !');
}
// Make sure the submitted registration values are not empty.
if (empty($_POST['surname']) || empty($_POST['firstname']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['confirm-password'])) {
	// One or more values are empty.
	exit('Veuillez remplir le formulaire d\'inscription !');
}

// Check if an account with that email exists.
if ($stmt = $con->prepare('SELECT * FROM utilisateur WHERE mailUser = ?')) {
	// Bind parameters (s = string, i = int, b = blob, etc), hash the password using the PHP password_hash function.
	$stmt->bind_param('s', $_POST['email']);
	$stmt->execute();
	$stmt->store_result();
	// Store the result so we can check if the account exists in the database.
	if ($stmt->num_rows > 0) {
		// Username already exists
		echo 'Cet adresse mail est déjà utilisé par un autre compte.';
	} else {
		// Username doesn't exists, insert new account
        if ($stmt = $con->prepare('INSERT INTO utilisateur (nomUser, prenomUser, mailUser, mdpUser) VALUES (?, ?, ?, ?)')) {
            // We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
            $password = password_hash($_POST['mdpUser'], PASSWORD_DEFAULT);
            $stmt->bind_param('sss', $_POST['surname'], $_POST['firstname'], $_POST['email'], $password);
            $stmt->execute();
            echo 'Compte créé. Vous pouvez maintenant vous connecter.';
        } else {
            // Something is wrong with the SQL statement, so you must check to make sure your accounts table exists with all three fields.
            echo 'Could not prepare statement!';
        }
	}
	$stmt->close();
} else {
	// Something is wrong with the SQL statement, so you must check to make sure your accounts table exists with all 3 fields.
	echo 'Could not prepare statement!';
}
$con->close();