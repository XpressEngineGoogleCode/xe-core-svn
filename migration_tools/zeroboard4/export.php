<?php 
    include "lib.php";

    include "./tpl/header.php"; 
?>

    <form action="./do_export.php" method="post">
    <input type="hidden" name="path" value="<?=$path?>" />
    <input type="hidden" name="target_module" value="<?=$target_module?>" />

        <div class="title">Step 3. 백업할 파일 이름을 선택해주세요.</div>
        <div class="desc">백업 파일은 파일이름.xml로 저장되며 제로보드XE에서 import 가능합니다.</div>

        <div class="content">
            <div class="header">백업 대상</div>
            <div class="tail">
<?
    if(!ereg("^module\_", $target_module)) print "회원정보"; 
    else printf('%s (%s)',  substr($target_module, 7), "게시판" );
?>
            </div>
        </div>

        <div class="content">
            <div class="header">파일 이름</div>
            <div class="tail"><input type="text" name="filename" value="<?=$target_module?>.xml" /></div>
            <div class="tail"><input type="submit" value="next" /></div>
        </div>

    </form>

<?php include "./tpl/footer.php"; ?>
