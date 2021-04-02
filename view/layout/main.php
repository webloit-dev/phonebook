<!DOCTYPE html>
<html>
<head>

<title>Phone Book</title>

<?php require_once(doc."/view/component/css.php"); ?>

</head>

<body>

<div class="container">
    <div class="row">
        <div class='col-md-12 col-lg-12 col-sm-12 '>
            <?php
            require_once(doc."/view/$child");
            ?>
        </div>
    </div>
</div>

<?php require_once(doc."/view/component/js.php"); ?>

</body>
</html>