<?php include "./tpl/header.php"; ?>

<div id="db_info">
    <form action="./" method="get">
        <ul>
            <li>
                <div class="header">db host</div>
                <div class="tail"><input type="text" name="hostname" value="localhost" /></div>
            </li>
            <li>
                <div class="header">user id</div>
                <div class="tail"><input type="text" name="user_id" value="" /></div>
            </li>
            <li>
                <div class="header">password</div>
                <div class="tail"><input type="password" name="password" value="" /></div>
            </li>
            <li>
                <div class="header">db name</div>
                <div class="tail"><input type="text" name="db_name" value="" /></div>
                <div class="tail"><input type="submit" value="NEXT" class="button" /></div>
            </li>
        </ul>
    </form>
</div>

<?php include "./tpl/footer.php"; ?>
