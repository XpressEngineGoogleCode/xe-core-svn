<?php 
    include "lib.php";

    if(!$connect) {
        header("location:./");
        exit();
    }

    
    include "./tpl/header.php"; 
?>

    <form action="./" method="get" onsubmit="return doMigration(this);">
    <input type="hidden" name="path" value="<?=$path?>" />

        <div class="title">Step 2. 백업할 대상을 선택해주세요. (회원정보 또는 게시판)</div>
        <div class="desc">제로보드4는 회원정보와 그외 게시판으로 종류를 나누어 백업하실 수 있습니다.</div>

        <div class="content">
            <div class="header"><label for="member">member</label></div>
            <div class="tail"><input type="radio" name="target_module" value="member" id="member" /></div>
        </div>

        <div class="content">
            <div class="header"><label for="module_board">board</label></div>
            <div class="tail"><input type="radio" name="target_module" value="module_board" id="module_board" /></div>
        </div>

        <div class="button_area">
            <input type="submit" value="next" class="button" />
        </div>

    </form>

<?php include "./tpl/footer.php"; ?>
