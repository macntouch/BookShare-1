<?php
session_start();

$login = $_GET["login"];

// only logged user can modify his page
if (!isset($_SESSION["user"]) && $_SESSION["user"] != $login) {
    header("Location: user_log.php");
}

?>

<!DOCTYPE html>
<html>
<head>
    <link type="text/css" rel="stylesheet" href="stylesheet.css"/>
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
    <meta charset="utf-8"/>
    <meta name="Author" content="Vsevolod Alokhin, Alina Ten"/>
    <meta name="Keywords" content="book, livre, share, partager, bookcrossing"/>
    <meta name="Description" content="Ищите и делитесь книгами бесплатно"/>
    <title>Изменение профиля | BookShare</title>
</head>
<body>

<?php

include("connect.inc.php");

try {
    // connect to the database
    $conn = new PDO("mysql:host=$mysql_host;dbname=$mysql_database", $mysql_user, $mysql_password);
    $conn->exec("set names utf8");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // define variables and set to empty values
    $password = $passwordCheck = $email = $name = $last_name = $gender = $country = $city = $telephone = $facebook = $photo = $about = "";
    $passwordCheckErr = $emailErr = $photoErr = "";
    $conditions = array();

    // function to clear input data
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES);
        return $data;
    }

    // check fields
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (!empty($_POST["email"])) {
            $email = test_input($_POST["email"]);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailErr = "Неправильный email.";
                $email = "";
            }

            $sqlCheckEmail = $conn->query("SELECT COUNT(email) FROM user WHERE email='$email' AND login!='$login'");

            if ($sqlCheckEmail->fetchColumn() > 0) {
                $emailErr = "Пользователь с таким email уже существует.";
                $email = "";
            }

            if (empty($emailErr)) {
                $conditions[] = "email='$email'";
            }
        }

        // can change password only if the old one is entered and it matches
        if (!empty($_POST["password"]) && !empty($_POST["passwordCheck"])) {
            $password = $_POST["password"];
            $passwordCheck = $_POST["passwordCheck"];

            if ($password != $passwordCheck) {
                $password = $passwordCheck = "";
                $passwordCheckErr = "Старый пароль введён неправильно";
            } else {
                $conditions[] = "password='$password'";
            }
        }

        if (!empty($_POST["name"])) {
            $name = test_input($_POST["name"]);
            $conditions[] = "name='$name'";
        }

        if (!empty($_POST["last_name"])) {
            $last_name = test_input($_POST["last_name"]);
            $conditions[] = "last_name='$last_name'";
        }

        if (!empty($_POST["gender"])) {
            $gender = $_POST["gender"];
            $conditions[] = "gender='$gender'";
        }

        if (!empty($_POST["country"])) {
            $country = test_input($_POST["country"]);
            $conditions[] = "country='$country'";
        }

        if (!empty($_POST["city"])) {
            $city = test_input($_POST["city"]);
            $conditions[] = "city='$city'";
        }

        if (!empty($_POST["telephone"])) {
            $telephone = test_input($_POST["telephone"]);
            $conditions[] = "telephone='$telephone'";
        }

        if (!empty($_POST["facebook"])) {
            $facebook = test_input($_POST["facebook"]);
            $conditions[] = "facebook='$facebook'";
        }

        if (!empty($_POST["about"])) {
            $about = test_input($_POST["about"]);
            $conditions[] = "about='$about'";
        }

        if ($_FILES["photo"]["size"] > 0) {
            $target_dir = "users_img/";
            $imageFileType = pathinfo(basename($_FILES["photo"]["name"]), PATHINFO_EXTENSION);
            $photo = $target_dir . str_replace(' ', '_', $login) . "." . $imageFileType;
            $uploadOk = 1;

            // check if image file is a actual image or fake image
            $check = getimagesize($_FILES["photo"]["tmp_name"]);
            if ($check == false) {
                $photoErr = "Файл не является картинкой.";
                $uploadOk = 0;
            }

            // check file size
            if ($_FILES["photo"]["size"] > 500000) {
                $photoErr = "К сожалению, Ваш файл слишком большого размера.";
                $uploadOk = 0;
            }

            // allow certain file formats
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                echo "К сожалению, принимаются только JPG, JPEG, PNG и GIF.";
                $uploadOk = 0;
            }

            // check if $uploadOk is not set to 0 by an error
            if ($uploadOk == 1) {
                $conditions[] = "photo='$photo'";
                if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $photo)) {
                    $photoErr = "К сожалению, произошла ошибка при загрузке Вашего файла.";
                }
            }
        }
    }

    // updating database
    if (count($conditions) > 0 && empty($photoErr)) {
        $photo = "/" . $photo;
        $sql = "UPDATE user SET " . implode(',', $conditions) . " WHERE login='$login'";
        $conn->exec($sql);
        header("Location: user.php");
    }

} catch (PDOException $e) {
    echo "<p>Erreur: " . $e->getMessage() . "</p>\n";
}

include("header.php");

?>

<div class="wrapper row2">
    <div id="container" class="clear">

        <h2 class="headline">Заполните данные, которые Вы хотите заменить</h2>
        <h3 class="headline">(оставьте поле пустым, если не хотите его менять)</h3>

        <form method="post" enctype="multipart/form-data" action="#">
            <div class="border-form">
                <div class="form">

                    <label>Email:</label> <input type="text" name="email" value="<?php echo $email ?>">
                    <span class="error"> <?php echo $emailErr ?></span>
                    <div></div>

                    <div></div>
                    <label>Новый пароль:</label> <input type="password" name="password" value="<?php echo $password ?>">
                    <span class="error"> <?php echo $passwordErr ?></span>
                    <div></div>

                    <div></div>
                    <label>Старый пароль:</label> <input type="password" name="passwordCheck" value="<?php echo $passwordCheck ?>">
                    <span class="error"> <?php echo $passwordCheckErr ?></span>
                    <div></div>

                    <label>Имя:</label> <input type="text" name="name" value="<?php echo $name ?>">
                    <div></div>

                    <label>Фамилия:</label> <input type="text" name="last_name" value="<?php echo $last_name ?>">
                    <div></div>

                    <label>Пол:</label>
                    <input type="radio"
                           name="gender" <?php if (isset($gender) && $gender == "femme") echo "checked" ?>
                           value="femme"> Женский:
                    <input type="radio" name="gender" <?php if (isset($gender) && $gender == "homme") echo "checked" ?>
                           value="homme"> Мужской:
                    <div></div>

                    <label>Страна:</label> <input type="text" name="country" value="<?php echo $country ?>">
                    <div></div>

                    <label>Город:</label> <input type="text" name="city" value="<?php echo $city ?>">
                    <div></div>

                    <label>Телефон:</label> <input type="text" name="telephone" value="<?php echo $telephone ?>">
                    <div></div>

                    <label>Facebook:</label> <input type="text" name="facebook" value="<?php echo $facebook ?>">
                    <div></div>

                    <label>Расскажите о себе: <textarea name="about" rows="5"
                                                          cols="45"><?php echo $about ?></textarea>
                    </label>

                    <div></div>

                    <label>Фото: </label> <input type="file" name="photo" id="photo"> <span class="error"> <?php echo $photoErr ?></span>
                    <div></div>

                    <input type="submit" name="submit" value="Изменить" id="button">
                </div>
            </div>
        </form>
    </div>
</div>

<?php include("footer.html"); ?>

</body>
</html>