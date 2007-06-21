<?php include "./tpl/header.php"; ?>

    <form action="./module_list.php" method="post">
        <div class="title">Step 1. 경로 입력</div>
        <div class="desc">제로보드가 설치된 경로를 입력해 주세요.</div>

        <div class="content">
            <div class="header">path</div>
            <div class="tail"><input type="text" name="path" class="input_text"value="" /></div>
            <div class="tail"><input type="submit" class="input_submit"value="next" /></div>
        </div>
    </form>

<?php include "./tpl/footer.php"; ?>
