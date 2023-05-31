<?php
date_default_timezone_set("Asia/Tokyo"); //To set the standard time to Tokyo

//initialize variables
$current_date = null;
$message = array();
$message_array = array();
$success_message = null;
$error_message = array();
$escaped = array();
$pdo = null;
$statment = null;
$res = null;

//Connect to database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=bbs-yt', 'root', '');
    
} catch (PDOException $e) {
    //get error message
    $error_message[] = $e->getMessage();
    
}

//Data will be stored into $_POST
//only if data exists, then show them
//if submit buttuon is clicked
if (!empty($_POST["submitButton"])) {

    //Validation of name
    if (empty($_POST["username"])) {
        $error_message[] = "Please enter name";
    } else {
        $escaped['username'] = htmlspecialchars($_POST["username"], ENT_QUOTES, "UTF-8");
    }

    //Validation of comments
    if (empty($_POST["comment"])) {
        $error_message[] = "Please enter comment";
    } else {
        $escaped['comment'] = htmlspecialchars($_POST["comment"], ENT_QUOTES, "UTF-8");
    }

    //As long as error message doesn't exist, then can store data
    if (empty($error_message)) {
        // var_dump($_POST);

        //store the current date & time
        $current_date = date("Y-m-d H:i:s");

        //Start a transaction
        $pdo->beginTransaction();

        try {

            //SQL
            $statment = $pdo->prepare("INSERT INTO `bs-table` (username, comment, postDate) VALUES (:username, :comment, :postDate)");

            //Setting values
            $statment->bindParam(':username', $escaped["username"], PDO::PARAM_STR);
            $statment->bindParam(':comment', $escaped["comment"], PDO::PARAM_STR);
            $statment->bindParam(':postDate', $current_date, PDO::PARAM_STR);

            //Execute the SQL
            $res = $statment->execute();

            //If no errors, then commit
            $res = $pdo->commit();
        } catch (Exception $e) {
            //in case of error, then rollback
            $pdo->rollBack();
        }

        if ($res) {
            $success_message = "Your comment is successfully stored!";
        } else {
            $error_message[] = "Failure! Your comment is not stored!";
        }

        $statment = null;
    }
}


//Get comment data from DB
$sql = "SELECT username, comment, postDate FROM `bbs-table` ORDER BY postDate ASC";

$message_array = $pdo->query($sql);


//disconnect from DB
$pdo = null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBS</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1 class="title">BBS with PHP </h1>
    <hr>
    <div class="boardWrapper">
        <!-- success -->
        <?php if (!empty($success_message)) : ?>
            <p class="success_message"><?php echo $success_message; ?></p>
        <?php endif; ?>

        <!-- validation check -->
        <?php if (!empty($error_message)) : ?>
            <?php foreach ($error_message as $value) : ?>
                <div class="error_message">*<?php echo $value; ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        <section>
            <?php if (!empty($message_array)) : ?>
                <?php foreach ($message_array as $value) : ?>
                    <article>
                        <div class="wrapper">
                            <div class="nameArea">
                                <span>Name:</span>
                                <p class="username"><?php echo $value['username'] ?></p>
                                <time>:<?php echo date('Y/m/d H:i', strtotime($value['postDate'])); ?></time>
                            </div>
                            <p class="comment"><?php echo $value['comment']; ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
        <form method="POST" action="" class="formWrapper">
            <div>
                <input type="submit" value="SUBMIT" name="submitButton">
                <label for="usernameLabel">Name:</label>
                <input type="text" name="username">
            </div>
            <div>
                <textarea name="comment" class="commentTextArea"></textarea>
            </div>
        </form>
    </div>

</body>

</html>