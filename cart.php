<?php

require_once 'common.php';
require_once 'config.php';

if (isset($_GET['delete'])) {
    foreach ($_SESSION['cart'] as $key => $value) {
        if ($value == $_GET['delete']) {
            unset($_SESSION['cart'][$key]);
        }
    }
    header('Location: cart.php');
    exit();
};

$query = 'SELECT * FROM products WHERE id IN (' . implode(',', array_fill(0, count($_SESSION['cart']), '?')) . ')';

$stmt = $connection->prepare($query);
if (!empty(array_values($_SESSION['cart']))) {
    $res = $stmt->execute(array_values($_SESSION['cart']));
} else {
    $_SESSION['cart'] = [];
};
$rows = $stmt->fetchAll();

$name = $contactDetails = $comments = '';
$nameErr = $contactDetailsErr = $cartErr = '';

if (isset($_POST['checkout'])) {

    if (empty($_POST['name'])) {
        $nameErr = __('Name is required');
    } else {
        $name = inputFilter($_POST['name']);
    }
    if (empty($_POST['contactDetails'])) {
        $contactDetailsErr = __('Contact details are required');
    } else {
        $contactDetails = inputFilter($_POST['contactDetails']);
    }

    $comments = inputFilter($_POST['comments']);

    if (empty($_SESSION['cart'])) {
        $cartErr = __('Cart is empty');
    }
}

if (isset($_POST['checkout']) && empty($nameErr) && empty($contactDetailsErr) && empty($cartErr)) {

    $to = SHOPMANAGER;
    $subject = 'Order number #';
    $headers = 'From: example@gmail.com' . "\r\n" .
        'MIME-Version: 1.0' . "r\n" .
        'Content-Type: text/html; charset=utf-8';

    $message = '
        <html>
            <head>
                <title>' . __('Order number ####') . '</title>
            </head>
            <body>
                <p>' . __('Hello. A new order from ') . ' ' . ($name) . '</p>
                <p>' . __('Please find the order details below:') . '</p>
                <table border="1" cellpadding="2">
            <tr>
                <th>' . __('Name') . ' </th>
                <th>' . __('Description') . ' </th>
                <th>' . __('Price') . ' </th>
            </tr> ';

    foreach ($rows as $row) {
        $message .= ' <tr>
                        <td>' . $row['title'] . '</td>
                        <td>' . $row['description'] . '</td>
                        <td>' . $row['price'] . '</td>
                    </tr> ';
    }
    $message .= ' </table>
                <p> ' . __('Contact details:') . ' ' . $contactDetails . '</p>
                <p> ' . __('Comments:') . ' ' . $comments . '</p>
            </body>
        </html> ';

    if (mail($to, $subject, $message, $headers)) {
        $_SESSION['cart'] = [];
        header('refresh:5;url=cart.php');
        echo '<div class="p-3 mb-2 bg-primary text-white">The email has been sent. Thank you' . ' '  . $name . '</div>';
    }
}

?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('Cart') ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

</head>

<body>
    <div class="container">
        <?php if (empty($_SESSION['cart'])) : ?>
            <h2 class="text-danger"> <?= $cartErr ?><h2>
                <?php endif; ?>
                <table class="table">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col"></th>
                            <th scope="col"><?= __('Title') ?></th>
                            <th scope="col"><?= __('Description') ?></th>
                            <th scope="col"><?= __('Price') ?></th>
                            <th scope="col"><?= __('Action') ?></th>
                        </tr>
                    </thead>
                    <?php foreach ($rows as $row) : ?>
                        <tr>
                            <td><img src="img/<?= $row['image'] ?>" style="width: 200px" alt=""></td>
                            <td><?= $row['title'] ?></td>
                            <td><?= $row['description'] ?></td>
                            <td> $ <?= $row['price'] ?></td>
                            <td><a href="?delete=<?= $row['id'] ?>"><?= __('Delete') ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <form class="form-group" method="POST" action="cart.php">
                    <label for="name"><?= __('Name') ?></label>
                    <input type="text" name="name" placeholder="<?= __('Insert your name') ?>" class="form-control" value="<?= $name ?>">
                    <p class="text-danger"> <?= $nameErr; ?></p>
                    <label for="contactDetails"><?= __('Contact details') ?></label>
                    <textarea rows="2" cols="30" name="contactDetails" placeholder="<?= __('Insert your contact details') ?>" class="form-control" value="<?= $contactDetails ?>"></textarea>
                    <p class="text-danger"> <?= $contactDetailsErr ?></p>
                    <label for="comments"><?= __('Comments') ?></label>
                    <textarea rows="4" cols="30" name="comments" placeholder="<?= __('Insert comments') ?>" class="form-control" value="<?= $comments ?>"></textarea>
                    <input type="submit" class="btn btn-primary" name="checkout" value="<?= __('Checkout') ?>"></button>
                </form>
                <a href="index.php" class="btn btn-warning"><?= __('Go to index') ?></a>
    </div>
</body>

</html>